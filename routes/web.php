<?php

use App\Http\Controllers\PublicSignoffController;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $projects = Project::with(['client', 'milestones'])->orderByDesc('created_at')->get();

    $totalContractValue = (int) Project::sum('total_amount');
    $totalCashCollected = (int) Milestone::where('status', 'paid')->sum('amount');
    $cashAtRisk = (int) Milestone::whereIn('status', ['awaiting_signoff', 'bast_signed', 'invoiced'])->sum('amount');
    $retentionHeld = (int) Milestone::where('status', 'retention_hold')->sum('amount');
    $retentionMatured = (int) Milestone::where('status', 'retention_matured')->sum('amount');

    return Inertia::render('Dashboard', [
        'metrics' => [
            'total_contract_value' => $totalContractValue,
            'total_cash_collected' => $totalCashCollected,
            'cash_at_risk' => $cashAtRisk,
            'retention_held' => $retentionHeld,
            'retention_matured' => $retentionMatured,
        ],
        'projects' => $projects,
    ]);
});

Route::prefix('sign')->group(function () {
    Route::get('/{token}', [PublicSignoffController::class, 'show']);
    Route::post('/{token}/approve', [PublicSignoffController::class, 'approve']);
    Route::post('/{token}/revise', [PublicSignoffController::class, 'revise']);
});
