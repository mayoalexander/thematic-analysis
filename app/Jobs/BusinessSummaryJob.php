<?php

namespace App\Jobs;

use App\Models\ProjectContext;
use App\Services\AnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BusinessSummaryJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $maxExceptions = 2;

    public function __construct(
        public ProjectContext $project
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnalysisService $analyzer): void
    {
        try {
            Log::info("Starting business summary generation for project: {$this->project->id}");

            // Get all analysis results
            $analysisResults = $this->project->analysis_results ?? [];
            $metadata = $this->project->metadata ?? [];

            if (empty($analysisResults)) {
                throw new \Exception("No analysis results found to summarize");
            }

            // Generate business summary
            $result = $analyzer->generateBusinessSummary(
                $analysisResults,
                $this->project->only(['reasoning_context', 'working_context']),
                $metadata
            );

            // Log the result for debugging
            Log::info("Business summary generated:", ['result' => $result]);

            // Validate the output
            if (!$analyzer->validateBusinessSummary($result['structured'])) {
                Log::error("Invalid business summary structure", [
                    'structured_output' => $result['structured'],
                    'raw_output' => $result['raw']
                ]);
                throw new \Exception("Invalid business summary generated");
            }

            // Update the project context with business summary
            $this->updateProjectContext($result);

            // Mark analysis as complete
            $this->markAnalysisComplete();

            Log::info("Completed business summary for project: {$this->project->id}");

        } catch (\Exception $e) {
            Log::error("Business summary failed for project {$this->project->id}: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    protected function updateProjectContext(array $result): void
    {
        $draftContext = $this->project->draft_context ?? [];
        $analysisResults = $this->project->analysis_results ?? [];
        $metadata = $this->project->metadata ?? [];

        $draftContext['business_summary'] = $result['raw'];
        $analysisResults['business_summary'] = $result['structured'];
        $metadata['business_summary'] = $result['metadata'] ?? [];

        $this->project->update([
            'draft_context' => $draftContext,
            'analysis_results' => $analysisResults,
            'metadata' => $metadata,
        ]);
    }

    protected function markAnalysisComplete(): void
    {
        $progress = $this->project->progress ?? ['total' => 6, 'completed' => 6];
        $progress['business_summary_complete'] = true;
        $progress['analysis_complete'] = true;
        $progress['completed_at'] = now()->toISOString();

        $this->project->update([
            'progress' => $progress,
            'status' => 'completed'
        ]);

        Log::info("Analysis workflow completed for project: {$this->project->id}");
    }

    protected function handleError(\Exception $e): void
    {
        $workingContext = $this->project->working_context ?? [];
        $workingContext['errors'] = $workingContext['errors'] ?? [];
        $workingContext['errors']['business_summary'] = $e->getMessage();

        $this->project->update([
            'working_context' => $workingContext,
            'status' => 'failed'
        ]);

        // Re-throw the exception to mark the job as failed
        throw $e;
    }
}
