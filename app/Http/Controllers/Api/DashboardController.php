<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        $user = $request->user();
        $workspaceId = $user->workspace_id;

        $totalContractValue = (int) Project::where('workspace_id', $workspaceId)->sum('total_amount');

        $workspaceMilestones = Milestone::whereHas('project', function ($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        });

        $totalCashCollected = (int) (clone $workspaceMilestones)
            ->where('status', 'paid')
            ->sum('amount');

        $cashAtRisk = (int) (clone $workspaceMilestones)
            ->whereIn('status', ['awaiting_signoff', 'bast_signed', 'invoiced'])
            ->sum('amount');

        $retentionHeld = (int) (clone $workspaceMilestones)
            ->where('status', 'retention_hold')
            ->sum('amount');

        $retentionMatured = (int) (clone $workspaceMilestones)
            ->where('status', 'retention_matured')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_contract_value' => $totalContractValue,
                'total_cash_collected' => $totalCashCollected,
                'cash_at_risk' => $cashAtRisk,
                'retention_held' => $retentionHeld,
                'retention_matured' => $retentionMatured,
            ],
        ]);
    }
}
