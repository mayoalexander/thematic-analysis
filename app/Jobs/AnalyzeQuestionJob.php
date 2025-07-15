<?php

namespace App\Jobs;

use App\Models\ProjectContext;
use App\Services\AnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Jobs\BusinessSummaryJob;

class AnalyzeQuestionJob implements ShouldQueue
{
    use Queueable;

    // Limit retry attempts to prevent infinite loops
    public $tries = 3;
    public $maxExceptions = 2;

    public function __construct(
        public ProjectContext $project,
        public string $questionKey,
        public array $responses
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnalysisService $analyzer): void
    {
        try {
            Log::info("Starting analysis for question: {$this->questionKey}");

            // Analyze the question using the AnalysisService
            $result = $analyzer->analyzeQuestion(
                $this->responses,
                $this->project->only(['reasoning_context', 'working_context']),
                $this->questionKey
            );

            // Log the result for debugging
            Log::info("Analysis result for {$this->questionKey}:", ['result' => $result]);

            // Validate the output
            if (!$analyzer->validateOutput($result['structured'])) {
                Log::error("Invalid output structure for question: {$this->questionKey}", [
                    'structured_output' => $result['structured'],
                    'raw_output' => $result['raw']
                ]);
                throw new \Exception("Invalid output generated for question: {$this->questionKey}");
            }

            // Update the project context with results
            $this->updateProjectContext($result);

            // Update progress
            $this->updateProgress();

            Log::info("Completed analysis for question: {$this->questionKey}");

        } catch (\Exception $e) {
            Log::error("Analysis failed for question {$this->questionKey}: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    protected function updateProjectContext(array $result): void
    {
        $draftContext = $this->project->draft_context ?? [];
        $analysisResults = $this->project->analysis_results ?? [];
        $metadata = $this->project->metadata ?? [];

        $draftContext[$this->questionKey] = $result['raw'];
        $analysisResults[$this->questionKey] = $result['structured'];
        $metadata[$this->questionKey] = $result['metadata'] ?? [];

        $this->project->update([
            'draft_context' => $draftContext,
            'analysis_results' => $analysisResults,
            'metadata' => $metadata,
        ]);
    }

    protected function updateProgress(): void
    {
        // Refresh the project to get the latest data
        $this->project->refresh();
        
        // Get current analysis results to count actually completed questions
        $analysisResults = $this->project->analysis_results ?? [];
        $completedCount = count($analysisResults);
        
        $progress = $this->project->progress ?? ['total' => 6, 'completed' => 0];
        $progress['completed'] = $completedCount;
        $progress['percent'] = round(($progress['completed'] / $progress['total']) * 100);

        $this->project->update(['progress' => $progress]);

        Log::info("Updated progress for {$this->questionKey}: {$completedCount}/{$progress['total']} completed");

        // If all questions are completed, dispatch business summary job
        if ($completedCount >= $progress['total']) {
            Log::info("All questions completed, dispatching business summary job");
            \App\Jobs\BusinessSummaryJob::dispatch($this->project);
        }
    }

    protected function handleError(\Exception $e): void
    {
        $workingContext = $this->project->working_context ?? [];
        $workingContext['errors'] = $workingContext['errors'] ?? [];
        $workingContext['errors'][$this->questionKey] = $e->getMessage();

        $this->project->update(['working_context' => $workingContext]);

        // Re-throw the exception to mark the job as failed
        throw $e;
    }
}
