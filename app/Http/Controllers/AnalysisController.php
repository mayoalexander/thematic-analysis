<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeQuestionJob;
use App\Models\ProjectContext;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnalysisController extends Controller
{
    public function triggerAnalysis(Request $request)
    {
        try {
            // Get or create project context
            $project = ProjectContext::firstOrCreate([
                'name' => 'vpn-analysis',
            ], [
                'reasoning_context' => $this->getReasoningContext(),
                'working_context' => $this->getWorkingContext(),
                'progress' => [
                    'total' => 6,
                    'completed' => 0,
                    'percent' => 0
                ]
            ]);

            // Reset progress if restarting
            if ($request->get('restart')) {
                // Clear failed jobs
                DB::table('failed_jobs')->delete();
                
                // Clear any pending jobs in the queue
                DB::table('jobs')->delete();
                
                $project->update([
                    'draft_context' => null,
                    'analysis_results' => null,
                    'final_draft' => null,
                    'metadata' => null,
                    'working_context' => [
                        'model' => 'gpt-4o-mini',
                        'temperature' => 0.7,
                        'max_tokens' => 4000
                    ],
                    'progress' => [
                        'total' => 6,
                        'completed' => 0,
                        'percent' => 0
                    ]
                ]);
            }

            // Load Excel data
            $excelPath = storage_path('app/data/usercue_interview_case_study_6Qs.xlsx');
            
            if (!file_exists($excelPath)) {
                // Copy the file from Downloads to storage
                $downloadPath = '/Users/mayoalexander/Downloads/usercue_interview_case_study_6Qs (3).xlsx';
                if (file_exists($downloadPath)) {
                    if (!is_dir(dirname($excelPath))) {
                        mkdir(dirname($excelPath), 0755, true);
                    }
                    copy($downloadPath, $excelPath);
                } else {
                    return response()->json(['error' => 'Excel file not found'], 404);
                }
            }

            // Read Excel data with headers
            $data = Excel::toArray(new class implements WithHeadingRow {
                public function headingRow(): int
                {
                    return 1;
                }
            }, $excelPath)[0];
            
            // Debug: Log available column headers
            if (!empty($data)) {
                $availableColumns = array_keys($data[0]);
                Log::info('Available Excel columns:', ['columns' => $availableColumns]);
            }
            
            // Question columns (now using column names directly)
            $questionColumns = [
                'vpn_selection',
                'unmet_needs_private_location',
                'unmet_needs_always_avail',
                'current_vpn_feedback',
                'remove_data_steps_probe_yes',
                'remove_data_steps_probe_no',
            ];
            
            Log::info('Looking for question columns:', ['expected_columns' => $questionColumns]);

            // Process each question column
            foreach ($questionColumns as $questionKey) {
                $responses = $this->extractResponses($data, $questionKey);
                
                Log::info("Processing question: {$questionKey}", [
                    'response_count' => count($responses),
                    'sample_data' => array_slice($responses, 0, 2) // Log first 2 responses for debugging
                ]);
                
                if (!empty($responses)) {
                    AnalyzeQuestionJob::dispatch($project, $questionKey, $responses);
                } else {
                    Log::warning("No responses found for question: {$questionKey}");
                }
            }

            return response()->json([
                'message' => 'Analysis jobs queued successfully',
                'project_id' => $project->id,
                'total_questions' => count($questionColumns)
            ]);

        } catch (\Exception $e) {
            Log::error('Error triggering analysis: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to trigger analysis'], 500);
        }
    }

    public function getStatus(Request $request)
    {
        $project = ProjectContext::where('name', 'vpn-analysis')->first();
        
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Get failed jobs from database
        $failedJobs = DB::table('failed_jobs')->get();
        $failedJobErrors = [];
        
        foreach ($failedJobs as $failedJob) {
            $payload = json_decode($failedJob->payload, true);
            if (isset($payload['data']['command'])) {
                // Decode the serialized job to get question key
                $command = unserialize($payload['data']['command']);
                if (isset($command->questionKey)) {
                    $failedJobErrors[$command->questionKey] = $failedJob->exception;
                }
            }
        }

        // Merge with working context errors
        $allErrors = array_merge(
            $project->working_context['errors'] ?? [],
            $failedJobErrors
        );

        // Calculate total metadata summary
        $metadataSummary = $this->calculateMetadataSummary($project->metadata ?? []);

        // Check if business summary is complete
        $analysisResults = $project->analysis_results ?? [];
        $hasBusinessSummary = isset($analysisResults['business_summary']);
        $isComplete = $project->status === 'completed' || $hasBusinessSummary;

        return response()->json([
            'progress' => $project->progress,
            'has_results' => !empty($project->analysis_results),
            'has_business_summary' => $hasBusinessSummary,
            'is_complete' => $isComplete,
            'status' => $project->status ?? 'processing',
            'errors' => $allErrors,
            'metadata_summary' => $metadataSummary,
        ]);
    }

    public function getResults(Request $request)
    {
        $project = ProjectContext::where('name', 'vpn-analysis')->first();
        
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json([
            'reasoning_context' => $project->reasoning_context,
            'working_context' => $project->working_context,
            'draft_context' => $project->draft_context,
            'analysis_results' => $project->analysis_results,
            'final_draft' => $project->final_draft,
            'progress' => $project->progress,
            'metadata' => $project->metadata,
        ]);
    }

    public function reprocessQuestion(Request $request, $questionKey)
    {
        $project = ProjectContext::where('name', 'vpn-analysis')->first();
        
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        try {
            // Load Excel data and extract responses for the specific question
            $excelPath = storage_path('app/data/usercue_interview_case_study_6Qs.xlsx');
            // Read Excel data with headers
            $data = Excel::toArray(new class implements WithHeadingRow {
                public function headingRow(): int
                {
                    return 1;
                }
            }, $excelPath)[0];
            $responses = $this->extractResponses($data, $questionKey);

            if (!empty($responses)) {
                AnalyzeQuestionJob::dispatch($project, $questionKey, $responses);
                return response()->json(['message' => "Reprocessing {$questionKey}"]);
            }

            return response()->json(['error' => 'No responses found for question'], 400);

        } catch (\Exception $e) {
            Log::error("Error reprocessing question {$questionKey}: " . $e->getMessage());
            return response()->json(['error' => 'Failed to reprocess question'], 500);
        }
    }

    public function clearAnalysis(Request $request)
    {
        try {
            // Clear failed jobs
            DB::table('failed_jobs')->delete();
            
            // Clear any pending jobs in the queue
            DB::table('jobs')->delete();

            $project = ProjectContext::where('name', 'vpn-analysis')->first();
            
            if ($project) {
                $project->update([
                    'draft_context' => null,
                    'analysis_results' => null,
                    'final_draft' => null,
                    'metadata' => null,
                    'working_context' => [
                        'model' => 'gpt-4o-mini',
                        'temperature' => 0.7,
                        'max_tokens' => 4000
                    ],
                    'progress' => [
                        'total' => 6,
                        'completed' => 0,
                        'percent' => 0
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Analysis cleared successfully',
                'project_reset' => !is_null($project)
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing analysis: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear analysis'], 500);
        }
    }

    protected function extractResponses(array $data, string $questionKey): array
    {
        $responses = [];
        $analysisService = new AnalysisService();

        foreach ($data as $row) {
            $participantId = $row['id'] ?? $row['ID'] ?? null;
            $responseText = $row[$questionKey] ?? null;

            if ($participantId && $responseText) {
                // Extract user responses from conversational format
                $cleanedResponse = $analysisService->extractUserResponses($responseText);
                
                if (!empty(trim($cleanedResponse))) {
                    $responses[] = [
                        'participant_id' => $participantId,
                        'response' => $cleanedResponse,
                    ];
                }
            }
        }

        return $responses;
    }

    protected function getReasoningContext(): array
    {
        return [
            'project_background' => 'The primary goal of this research study is to understand the consumer privacy market, specifically in the areas of network privacy (VPNs) and data deletion services. We aim to size the market, identify key customer needs and use cases, and validate product-market fit for CLIENT\'s offerings in these spaces.',
            'questions' => [
                'vpn_selection' => [
                    'title' => 'What factors influence VPN selection decisions?',
                    'description' => 'Understanding the criteria consumers use when choosing VPN services'
                ],
                'unmet_needs_private_location' => [
                    'title' => 'What are the unmet needs for private location features?',
                    'description' => 'Identifying gaps in current VPN location privacy offerings'
                ],
                'unmet_needs_always_avail' => [
                    'title' => 'What are the unmet needs for VPN accessibility and reliability?',
                    'description' => 'Understanding issues with VPN availability and consistent service'
                ],
                'current_vpn_feedback' => [
                    'title' => 'What feedback do users have about their current VPN services?',
                    'description' => 'Collecting user experiences and satisfaction levels'
                ],
                'remove_data_steps_probe_yes' => [
                    'title' => 'How do users who want data deletion expect the process to work?',
                    'description' => 'Understanding expectations for data deletion services'
                ],
                'remove_data_steps_probe_no' => [
                    'title' => 'Why do some users not want data deletion services?',
                    'description' => 'Understanding resistance to data deletion offerings'
                ]
            ]
        ];
    }

    protected function getWorkingContext(): array
    {
        return [
            'excel_path' => storage_path('app/data/usercue_interview_case_study_6Qs.xlsx'),
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'requirements' => [
                'accuracy' => 'No quote hallucination or rewording',
                'themes' => '3-5 themes per question',
                'quotes' => 'Exactly 3 quotes per theme',
                'uniqueness' => 'No duplicate quotes across themes'
            ]
        ];
    }

    protected function calculateMetadataSummary(array $metadata): array
    {
        if (empty($metadata)) {
            return [
                'total_tokens' => 0,
                'total_cost' => 0.0,
                'total_duration_ms' => 0,
                'total_participants' => 0,
                'questions_completed' => 0,
                'model' => null,
            ];
        }

        $totalTokens = 0;
        $totalCost = 0.0;
        $totalDuration = 0;
        $totalParticipants = 0;
        $questionsCompleted = count($metadata);
        $model = null;

        foreach ($metadata as $questionMeta) {
            if (isset($questionMeta['tokens']['total'])) {
                $totalTokens += $questionMeta['tokens']['total'];
            }
            if (isset($questionMeta['costs']['total'])) {
                $totalCost += $questionMeta['costs']['total'];
            }
            if (isset($questionMeta['duration_ms'])) {
                $totalDuration += $questionMeta['duration_ms'];
            }
            if (isset($questionMeta['participant_count'])) {
                $totalParticipants = max($totalParticipants, $questionMeta['participant_count']);
            }
            if (!$model && isset($questionMeta['model'])) {
                $model = $questionMeta['model'];
            }
        }

        return [
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 4),
            'total_duration_ms' => $totalDuration,
            'total_duration_seconds' => round($totalDuration / 1000, 2),
            'total_participants' => $totalParticipants,
            'questions_completed' => $questionsCompleted,
            'model' => $model,
            'avg_tokens_per_question' => $questionsCompleted > 0 ? round($totalTokens / $questionsCompleted) : 0,
            'avg_cost_per_question' => $questionsCompleted > 0 ? round($totalCost / $questionsCompleted, 4) : 0,
        ];
    }
}
