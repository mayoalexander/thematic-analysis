<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Analysis routes
Route::prefix('analysis')->group(function () {
    Route::post('/trigger', [AnalysisController::class, 'triggerAnalysis']);
    Route::post('/clear', [AnalysisController::class, 'clearAnalysis']);
    Route::get('/status', [AnalysisController::class, 'getStatus']);
    Route::get('/results', [AnalysisController::class, 'getResults']);
    Route::post('/reprocess/{questionKey}', [AnalysisController::class, 'reprocessQuestion']);
});