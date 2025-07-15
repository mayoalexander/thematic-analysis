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
            // DEBUG: Log business summary job start
            Log::info("🚀 BUSINESS SUMMARY JOB STARTED for project: {$this->project->id}");

            // Get all analysis results
            $analysisResults = $this->project->analysis_results ?? [];
            $metadata = $this->project->metadata ?? [];

            // DEBUG: Log what we have
            Log::info("📋 BUSINESS SUMMARY INPUT:", [
                'analysis_results_count' => count($analysisResults),
                'analysis_results_keys' => array_keys($analysisResults),
                'metadata_keys' => array_keys($metadata)
            ]);

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
        
        // DEBUG: Log successful business summary save
        Log::info("💾 BUSINESS SUMMARY SAVED:", [
            'project_id' => $this->project->id,
            'has_business_summary' => isset($analysisResults['business_summary']),
            'business_summary_keys' => array_keys($analysisResults['business_summary'] ?? [])
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
        
        // Refresh the project to make sure the status is updated
        $this->project->refresh();

        // DEBUG: Log completion
        Log::info("✅ ANALYSIS WORKFLOW COMPLETED for project: {$this->project->id}:", [
            'progress' => $progress,
            'status' => $this->project->status
        ]);
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
