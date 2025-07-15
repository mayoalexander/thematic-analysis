<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class AnalysisService
{
    protected string $model;

    public function __construct()
    {
        $this->model = config('openai.model', 'gpt-4o-mini');
    }

    public function analyzeQuestion(array $responses, array $context, string $questionKey)
    {
        $startTime = microtime(true);
        $prompt = $this->buildPrompt($responses, $context, $questionKey);

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a skilled research assistant performing thematic analysis on qualitative interview data. Your task is to identify meaningful themes and provide supporting evidence through direct quotes.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
            ]);

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000); // milliseconds

            $rawOutput = $response->choices[0]->message->content;
            $structuredOutput = $this->parseThemeOutput($rawOutput);

            // Calculate token usage and costs
            $usage = $response->usage;
            $metadata = $this->calculateMetadata($usage, $duration, count($responses));

            return [
                'raw' => $rawOutput,
                'structured' => $structuredOutput,
                'metadata' => $metadata,
            ];
        } catch (\Exception $e) {
            Log::error("OpenAI API error for question {$questionKey}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function buildPrompt(array $responses, array $context, string $questionKey): string
    {
        $projectBackground = $context['reasoning_context']['project_background'] ?? '';
        $questionContext = $context['reasoning_context']['questions'][$questionKey] ?? [];
        
        $formattedResponses = $this->formatResponses($responses);
        $participantCount = count($responses);

        return <<<PROMPT
PROJECT BACKGROUND:
{$projectBackground}

QUESTION BEING ANALYZED:
{$questionContext['title']}

PARTICIPANT RESPONSES ({$participantCount} total):
{$formattedResponses}

ANALYSIS INSTRUCTIONS:
1. Identify 3-5 key themes that capture the main patterns in the responses
2. Each theme should be "necessary and sufficient" - meaningful but not too broad or too narrow
3. Themes should be mutually exclusive (no significant overlap)
4. For each theme, provide:
   - A clear, descriptive title that directly answers the question
   - A brief description explaining the theme
   - Count of participants who fit this theme
   - Exactly 3 supporting quotes (verbatim, with participant IDs)

CRITICAL REQUIREMENTS:
- Use quotes EXACTLY as written - no rewording or paraphrasing
- Each quote must include the participant ID (format: "Quote text" - Participant 1234)
- Don't use the same quote for multiple themes
- Don't use multiple quotes from the same participant for one theme
- Ensure participant counts are accurate

OUTPUT FORMAT:
Please structure your response as follows:

## Question: [Question Title]
**Participants:** {$participantCount}
**Headline:** [Engaging headline that captures the key insight]

### Summary
[1-2 sentences providing high-level overview of findings]

### Themes

**Theme 1: [Title]**
Description: [Brief description]
Participants: [Count]
Supporting Quotes:
- "Quote 1" - Participant [ID]
- "Quote 2" - Participant [ID]
- "Quote 3" - Participant [ID]

**Theme 2: [Title]**
[Continue same format...]

PROMPT;
    }

    protected function formatResponses(array $responses): string
    {
        return collect($responses)
            ->map(function ($response) {
                $participantId = $response['participant_id'];
                $content = $response['response'];
                return "Participant {$participantId}: {$content}";
            })
            ->join("\n\n");
    }

    protected function parseThemeOutput(string $output): array
    {
        Log::info("Starting to parse theme output", ['raw_output' => $output]);
        
        $lines = explode("\n", $output);
        $result = [
            'question' => '',
            'participants' => 0,
            'headline' => '',
            'summary' => '',
            'themes' => []
        ];

        $currentTheme = null;
        $inQuotes = false;
        $currentSection = null;

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            
            if (empty($line)) continue;

            Log::debug("Processing line {$lineNumber}: {$line}");

            // Parse question
            if (preg_match('/^## Question: (.+)/', $line, $matches)) {
                $result['question'] = $matches[1];
                Log::debug("Found question: {$result['question']}");
                continue;
            }

            // Parse participants count
            if (preg_match('/\*\*Participants:\*\* (\d+)/', $line, $matches)) {
                $result['participants'] = (int) $matches[1];
                continue;
            }

            // Parse headline
            if (preg_match('/\*\*Headline:\*\* (.+)/', $line, $matches)) {
                $result['headline'] = $matches[1];
                continue;
            }

            // Parse summary section
            if (str_contains($line, '### Summary')) {
                $currentSection = 'summary';
                continue;
            }

            // Parse themes section
            if (str_contains($line, '### Themes')) {
                $currentSection = 'themes';
                continue;
            }

            // Parse theme title
            if (preg_match('/\*\*Theme \d+: (.+)\*\*/', $line, $matches)) {
                if ($currentTheme) {
                    $result['themes'][] = $currentTheme;
                }
                $currentTheme = [
                    'title' => $matches[1],
                    'description' => '',
                    'participants' => 0,
                    'quotes' => []
                ];
                continue;
            }

            // Parse theme description
            if (str_starts_with($line, 'Description:')) {
                $currentTheme['description'] = trim(str_replace('Description:', '', $line));
                continue;
            }

            // Parse participants count for theme
            if (preg_match('/Participants: (\d+)/', $line, $matches)) {
                $currentTheme['participants'] = (int) $matches[1];
                continue;
            }

            // Parse quotes
            if (str_contains($line, 'Supporting Quotes:')) {
                $inQuotes = true;
                continue;
            }

            if ($inQuotes && str_starts_with($line, '- "')) {
                // Extract quote and participant ID
                if (preg_match('/- "(.+)" - Participant (\d+)/', $line, $matches)) {
                    $currentTheme['quotes'][] = [
                        'text' => $matches[1],
                        'participant_id' => $matches[2]
                    ];
                }
                continue;
            }

            // Handle summary content
            if ($currentSection === 'summary' && !empty($line) && !str_starts_with($line, '#')) {
                $result['summary'] .= ($result['summary'] ? ' ' : '') . $line;
            }
        }

        // Add the last theme if it exists
        if ($currentTheme) {
            $result['themes'][] = $currentTheme;
        }

        Log::info("Finished parsing theme output", [
            'themes_count' => count($result['themes']),
            'has_question' => !empty($result['question']),
            'has_summary' => !empty($result['summary']),
            'result' => $result
        ]);

        return $result;
    }

    public function extractUserResponses(string $conversationText): string
    {
        // Extract user responses from conversational format
        // Pattern: user: [response] or similar
        preg_match_all('/user:\s*(.+?)(?=\nassistant:|$)/s', $conversationText, $matches);
        
        if (!empty($matches[1])) {
            return trim(implode(' ', $matches[1]));
        }
        
        return trim($conversationText);
    }

    public function validateOutput(array $structuredOutput): bool
    {
        // Log the structured output for debugging
        Log::info('Validating output:', $structuredOutput);
        
        // Basic validation
        if (empty($structuredOutput['themes'])) {
            Log::warning('Validation failed: No themes found in output');
            return false;
        }

        foreach ($structuredOutput['themes'] as $index => $theme) {
            if (empty($theme['title']) || empty($theme['description'])) {
                Log::warning("Validation failed: Theme {$index} missing title or description", $theme);
                return false;
            }
            
            if (empty($theme['quotes'])) {
                Log::warning("Validation failed: Theme {$index} missing quotes", $theme);
                return false;
            }
            
            if (count($theme['quotes']) !== 3) {
                Log::warning("Validation failed: Theme {$index} has " . count($theme['quotes']) . " quotes instead of 3", $theme);
                return false;
            }
        }

        Log::info('Output validation passed');
        return true;
    }

    protected function calculateMetadata($usage, int $durationMs, int $participantCount): array
    {
        // GPT-4o-mini pricing (as of 2024)
        $inputCostPer1k = 0.000150;  // $0.15 per 1K input tokens
        $outputCostPer1k = 0.000600; // $0.60 per 1K output tokens

        $inputTokens = $usage->promptTokens ?? 0;
        $outputTokens = $usage->completionTokens ?? 0;
        $totalTokens = $usage->totalTokens ?? 0;

        $inputCost = ($inputTokens / 1000) * $inputCostPer1k;
        $outputCost = ($outputTokens / 1000) * $outputCostPer1k;
        $totalCost = $inputCost + $outputCost;

        return [
            'model' => $this->model,
            'duration_ms' => $durationMs,
            'duration_seconds' => round($durationMs / 1000, 2),
            'participant_count' => $participantCount,
            'tokens' => [
                'input' => $inputTokens,
                'output' => $outputTokens,
                'total' => $totalTokens,
            ],
            'costs' => [
                'input' => round($inputCost, 6),
                'output' => round($outputCost, 6),
                'total' => round($totalCost, 6),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    public function generateBusinessSummary(array $analysisResults, array $context, array $metadata)
    {
        $startTime = microtime(true);
        $prompt = $this->buildBusinessSummaryPrompt($analysisResults, $context, $metadata);

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a senior business analyst creating executive summaries from thematic analysis results. Your task is to synthesize findings into actionable business insights and recommendations.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 3000,
            ]);

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000); // milliseconds

            $rawOutput = $response->choices[0]->message->content;
            $structuredOutput = $this->parseBusinessSummaryOutput($rawOutput);

            // Calculate token usage and costs
            $usage = $response->usage;
            $summaryMetadata = $this->calculateMetadata($usage, $duration, 1);

            return [
                'raw' => $rawOutput,
                'structured' => $structuredOutput,
                'metadata' => $summaryMetadata,
            ];
        } catch (\Exception $e) {
            Log::error("OpenAI API error for business summary: " . $e->getMessage());
            throw $e;
        }
    }

    protected function buildBusinessSummaryPrompt(array $analysisResults, array $context, array $metadata): string
    {
        $totalParticipants = $metadata['total_participants'] ?? 'Unknown';
        $questionsCompleted = count($analysisResults);
        
        $prompt = "# Executive Business Summary Request\n\n";
        $prompt .= "Please create a comprehensive executive summary based on the thematic analysis results below.\n\n";
        $prompt .= "## Study Overview\n";
        $prompt .= "- Participants: {$totalParticipants}\n";
        $prompt .= "- Questions Analyzed: {$questionsCompleted}\n\n";
        
        $prompt .= "## Analysis Results by Question\n\n";
        
        foreach ($analysisResults as $questionKey => $themes) {
            if ($questionKey === 'business_summary') continue; // Skip if already exists
            
            $prompt .= "### Question: " . ucwords(str_replace('_', ' ', $questionKey)) . "\n\n";
            
            if (isset($themes['themes']) && is_array($themes['themes'])) {
                foreach ($themes['themes'] as $theme) {
                    $prompt .= "**Theme:** {$theme['title']}\n";
                    $prompt .= "**Description:** {$theme['description']}\n";
                    $prompt .= "**Participants:** {$theme['participants']}\n";
                    if (isset($theme['quotes']) && !empty($theme['quotes'])) {
                        $prompt .= "**Key Quote:** \"{$theme['quotes'][0]['text']}\"\n";
                    }
                    $prompt .= "\n";
                }
            }
            $prompt .= "---\n\n";
        }
        
        $prompt .= "## Required Output Format\n\n";
        $prompt .= "Please provide your response in the following JSON structure:\n\n";
        $prompt .= "```json\n";
        $prompt .= "{\n";
        $prompt .= "  \"executive_summary\": \"Brief high-level overview of key findings\",\n";
        $prompt .= "  \"key_insights\": [\n";
        $prompt .= "    \"Insight 1\",\n";
        $prompt .= "    \"Insight 2\",\n";
        $prompt .= "    \"Insight 3\"\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"priority\": \"High|Medium|Low\",\n";
        $prompt .= "      \"action\": \"Specific recommendation\",\n";
        $prompt .= "      \"rationale\": \"Why this is important\",\n";
        $prompt .= "      \"impact\": \"Expected business impact\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"risks_and_opportunities\": {\n";
        $prompt .= "    \"risks\": [\"Risk 1\", \"Risk 2\"],\n";
        $prompt .= "    \"opportunities\": [\"Opportunity 1\", \"Opportunity 2\"]\n";
        $prompt .= "  },\n";
        $prompt .= "  \"next_steps\": [\"Step 1\", \"Step 2\", \"Step 3\"]\n";
        $prompt .= "}\n";
        $prompt .= "```\n\n";
        $prompt .= "Focus on actionable insights that can drive business decisions. Synthesize themes across questions to identify overarching patterns and strategic implications.";
        
        return $prompt;
    }

    protected function parseBusinessSummaryOutput(string $output): array
    {
        Log::info("Parsing business summary output", ['raw_output' => $output]);
        
        // Extract JSON from the output
        $jsonMatch = [];
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $output, $jsonMatch)) {
            $jsonContent = $jsonMatch[1];
        } else {
            // Try to find JSON without code blocks
            if (preg_match('/(\{.*\})/s', $output, $jsonMatch)) {
                $jsonContent = $jsonMatch[1];
            } else {
                Log::error("No JSON found in business summary output");
                return [
                    'executive_summary' => 'Failed to parse business summary',
                    'key_insights' => [],
                    'recommendations' => [],
                    'risks_and_opportunities' => ['risks' => [], 'opportunities' => []],
                    'next_steps' => []
                ];
            }
        }
        
        try {
            $parsed = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON decode error in business summary: " . json_last_error_msg());
                throw new \Exception("Invalid JSON in business summary output");
            }
            
            // Validate required fields
            $required = ['executive_summary', 'key_insights', 'recommendations', 'risks_and_opportunities', 'next_steps'];
            foreach ($required as $field) {
                if (!isset($parsed[$field])) {
                    Log::warning("Missing required field in business summary: {$field}");
                    $parsed[$field] = $field === 'risks_and_opportunities' ? ['risks' => [], 'opportunities' => []] : [];
                }
            }
            
            Log::info("Successfully parsed business summary", ['summary' => $parsed]);
            return $parsed;
            
        } catch (\Exception $e) {
            Log::error("Error parsing business summary JSON: " . $e->getMessage());
            return [
                'executive_summary' => 'Failed to parse business summary',
                'key_insights' => [],
                'recommendations' => [],
                'risks_and_opportunities' => ['risks' => [], 'opportunities' => []],
                'next_steps' => []
            ];
        }
    }

    public function validateBusinessSummary(array $summary): bool
    {
        $required = ['executive_summary', 'key_insights', 'recommendations', 'risks_and_opportunities', 'next_steps'];
        
        foreach ($required as $field) {
            if (!isset($summary[$field])) {
                Log::error("Business summary validation failed: missing {$field}");
                return false;
            }
        }
        
        // Validate executive_summary is a string
        if (!is_string($summary['executive_summary']) || empty(trim($summary['executive_summary']))) {
            Log::error("Business summary validation failed: invalid executive_summary");
            return false;
        }
        
        // Validate arrays
        $arrayFields = ['key_insights', 'next_steps'];
        foreach ($arrayFields as $field) {
            if (!is_array($summary[$field])) {
                Log::error("Business summary validation failed: {$field} must be array");
                return false;
            }
        }
        
        // Validate recommendations structure
        if (!is_array($summary['recommendations'])) {
            Log::error("Business summary validation failed: recommendations must be array");
            return false;
        }
        
        // Validate risks_and_opportunities structure
        if (!is_array($summary['risks_and_opportunities']) ||
            !isset($summary['risks_and_opportunities']['risks']) ||
            !isset($summary['risks_and_opportunities']['opportunities'])) {
            Log::error("Business summary validation failed: invalid risks_and_opportunities structure");
            return false;
        }
        
        Log::info("Business summary validation passed");
        return true;
    }
}