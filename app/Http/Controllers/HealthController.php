<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check()
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Check if we can write to storage
            $canWrite = is_writable(storage_path());
            
            // Check OpenAI API key is configured
            $hasOpenAI = !empty(config('openai.api_key'));
            
            $status = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'checks' => [
                    'database' => 'connected',
                    'storage' => $canWrite ? 'writable' : 'not_writable',
                    'openai' => $hasOpenAI ? 'configured' : 'missing_api_key',
                ],
                'app' => [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'version' => '1.0.0',
                ]
            ];
            
            return response()->json($status, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
