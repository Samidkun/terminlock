<?php

use App\Http\Controllers\Api\MilestoneController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/projects/{id}/milestones', [ProjectController::class, 'storeMilestones']);

    Route::post('/milestones/{id}/deliverables', [MilestoneController::class, 'uploadDeliverable']);
    Route::post('/milestones/{id}/request-signoff', [MilestoneController::class, 'requestSignoff']);
});
