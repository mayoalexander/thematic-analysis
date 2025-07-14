<?php

namespace App\Console\Commands;

use App\Models\ProjectContext;
use App\Services\AnalysisService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestAnalysisPipeline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analysis:test {--skip-api : Skip OpenAI API calls and test parsing only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the thematic analysis pipeline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Thematic Analysis Pipeline...');
        
        // Test 1: Check if Excel file exists
        $this->info('1. Testing Excel file access...');
        $excelPath = '/Users/mayoalexander/Downloads/usercue_interview_case_study_6Qs (3).xlsx';
        
        if (!file_exists($excelPath)) {
            $this->error('❌ Excel file not found at: ' . $excelPath);
            return Command::FAILURE;
        }
        
        $this->info('✅ Excel file found');
        
        // Test 2: Load and parse Excel data
        $this->info('2. Testing Excel parsing...');
        
        try {
            // Read Excel data with headers
            $data = Excel::toArray(new class implements WithHeadingRow {
                public function headingRow(): int
                {
                    return 1;
                }
            }, $excelPath)[0];
            
            $this->info('✅ Excel file loaded successfully');
            $this->info('   - Total rows: ' . count($data));
            
            // Show first few column names
            if (!empty($data)) {
                $columns = array_keys($data[0]);
                $this->info('   - Columns: ' . implode(', ', array_slice($columns, 0, 5)) . '...');
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to load Excel file: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Test 3: Test response extraction
        $this->info('3. Testing response extraction...');
        
        $analysisService = new AnalysisService();
        $testQuestions = ['vpn_selection', 'current_vpn_feedback'];
        
        foreach ($testQuestions as $questionKey) {
            $responses = $this->extractResponses($data, $questionKey, $analysisService);
            $this->info("   - {$questionKey}: " . count($responses) . ' responses extracted');
            
            if (count($responses) > 0) {
                $this->info('   - Sample response: ' . substr($responses[0]['response'], 0, 100) . '...');
            }
        }
        
        // Test 4: Test project context creation
        $this->info('4. Testing project context creation...');
        
        try {
            $project = ProjectContext::firstOrCreate([
                'name' => 'test-analysis',
            ], [
                'reasoning_context' => $this->getTestReasoningContext(),
                'working_context' => $this->getTestWorkingContext(),
                'progress' => ['total' => 2, 'completed' => 0, 'percent' => 0]
            ]);
            
            $this->info('✅ Project context created/found (ID: ' . $project->id . ')');
        } catch (\Exception $e) {
            $this->error('❌ Failed to create project context: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Test 5: Test API configuration (if not skipped)
        if (!$this->option('skip-api')) {
            $this->info('5. Testing OpenAI API configuration...');
            
            if (empty(config('openai.api_key'))) {
                $this->warn('⚠️  OpenAI API key not configured. Set OPENAI_API_KEY in .env');
                $this->info('   Run with --skip-api to test without API calls');
            } else {
                $this->info('✅ OpenAI API key configured');
                $this->info('   - Model: ' . config('openai.model', 'gpt-4o-mini'));
                
                // Test a simple API call
                try {
                    $testResponses = array_slice($this->extractResponses($data, 'vpn_selection', $analysisService), 0, 3);
                    
                    if (count($testResponses) > 0) {
                        $this->info('6. Testing OpenAI API call...');
                        
                        $result = $analysisService->analyzeQuestion(
                            $testResponses,
                            $project->only(['reasoning_context', 'working_context']),
                            'vpn_selection'
                        );
                        
                        if ($result && isset($result['structured'])) {
                            $this->info('✅ OpenAI API call successful');
                            $this->info('   - Themes found: ' . count($result['structured']['themes'] ?? []));
                        } else {
                            $this->warn('⚠️  API call returned unexpected format');
                        }
                    }
                } catch (\Exception $e) {
                    $this->error('❌ OpenAI API call failed: ' . $e->getMessage());
                    $this->info('   Make sure your API key is valid and you have credits');
                }
            }
        }
        
        $this->info('');
        $this->info('🎉 Pipeline test completed!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Set your OpenAI API key in .env: OPENAI_API_KEY=your-key-here');
        $this->info('2. Start the queue worker: php artisan queue:work');
        $this->info('3. Visit /analysis to trigger the full analysis');
        
        return Command::SUCCESS;
    }
    
    private function extractResponses(array $data, string $questionKey, AnalysisService $analysisService): array
    {
        $responses = [];
        
        foreach ($data as $row) {
            $participantId = $row['id'] ?? $row['ID'] ?? null;
            $responseText = $row[$questionKey] ?? null;
            
            if ($participantId && $responseText) {
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
    
    private function getTestReasoningContext(): array
    {
        return [
            'project_background' => 'Test analysis for VPN consumer research',
            'questions' => [
                'vpn_selection' => [
                    'title' => 'What factors influence VPN selection decisions?',
                    'description' => 'Understanding VPN selection criteria'
                ],
                'current_vpn_feedback' => [
                    'title' => 'What feedback do users have about their current VPN services?',
                    'description' => 'Collecting user experiences'
                ]
            ]
        ];
    }
    
    private function getTestWorkingContext(): array
    {
        return [
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ];
    }
}
