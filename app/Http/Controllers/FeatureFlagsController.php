<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeatureFlagsController extends Controller
{
    public function getFlags(Request $request)
    {
        return response()->json([
            'models' => [
                'gpt4' => [
                    'enabled' => env('FEATURE_GPT4_ENABLED', false),
                    'beta' => env('FEATURE_GPT4_BETA', true),
                    'reason' => env('FEATURE_GPT4_REASON', 'Coming Soon')
                ],
                'gpt4oMini' => [
                    'enabled' => env('FEATURE_GPT4O_MINI_ENABLED', true),
                    'beta' => env('FEATURE_GPT4O_MINI_BETA', false)
                ],
                'customModel' => [
                    'enabled' => env('FEATURE_CUSTOM_MODEL_ENABLED', false),
                    'beta' => env('FEATURE_CUSTOM_MODEL_BETA', true),
                    'reason' => env('FEATURE_CUSTOM_MODEL_REASON', 'Coming Soon')
                ]
            ],
            'fileUpload' => [
                'uploadFile' => [
                    'enabled' => env('FEATURE_FILE_UPLOAD_ENABLED', false),
                    'beta' => env('FEATURE_FILE_UPLOAD_BETA', true),
                    'reason' => env('FEATURE_FILE_UPLOAD_REASON', 'Coming Soon')
                ],
                'demoFile' => [
                    'enabled' => env('FEATURE_DEMO_FILE_ENABLED', true),
                    'beta' => env('FEATURE_DEMO_FILE_BETA', false)
                ]
            ],
            'features' => [
                'businessSummary' => [
                    'enabled' => env('FEATURE_BUSINESS_SUMMARY_ENABLED', true),
                    'beta' => env('FEATURE_BUSINESS_SUMMARY_BETA', false)
                ],
                'debugMode' => [
                    'enabled' => env('FEATURE_DEBUG_MODE_ENABLED', true),
                    'beta' => env('FEATURE_DEBUG_MODE_BETA', false)
                ]
            ]
        ]);
    }
}