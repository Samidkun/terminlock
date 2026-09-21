<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $workspaceId = $user->workspace_id;

        $validated = $request->validate([
            'client_id' => ['required', 'uuid', Rule::exists('clients', 'id')->where('workspace_id', $workspaceId)],
            'name' => ['required', 'string', 'max:255'],
            'contract_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('projects', 'contract_number')->where('workspace_id', $workspaceId),
            ],
            'total_amount' => ['required', 'integer', 'min:1'],
            'retention_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'retention_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $project = Project::create([
            'workspace_id' => $workspaceId,
            'client_id' => $validated['client_id'],
            'name' => $validated['name'],
            'contract_number' => $validated['contract_number'],
            'total_amount' => $validated['total_amount'],
            'retention_percentage' => $validated['retention_percentage'] ?? 0,
            'retention_days' => $validated['retention_days'] ?? 180,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ], 201);
    }

    public function storeMilestones(Request $request, string $projectId): JsonResponse
    {
        $user = $request->user();
        $project = Project::where('workspace_id', $user->workspace_id)->findOrFail($projectId);

        $validated = $request->validate([
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.name' => ['required', 'string', 'max:255'],
            'milestones.*.amount' => ['required', 'integer', 'min:1'],
            'milestones.*.percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'milestones.*.due_date' => ['nullable', 'date'],
            'milestones.*.is_retention' => ['nullable', 'boolean'],
        ]);

        // AC-2: Validate milestone sum strictly equals project total_amount
        $sum = 0;
        foreach ($validated['milestones'] as $m) {
            $sum += (int) $m['amount'];
        }

        if ($sum !== $project->total_amount) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MILESTONE_SUM_MISMATCH',
                    'message' => 'Total jumlah nominal termin harus sama persis dengan nilai kontrak proyek.',
                    'expected' => $project->total_amount,
                    'actual' => $sum,
                ],
            ], 422);
        }

        // Delete any existing draft milestones
        $project->milestones()->where('status', 'draft')->delete();

        $order = 1;
        $created = [];
        foreach ($validated['milestones'] as $m) {
            $created[] = Milestone::create([
                'project_id' => $project->id,
                'order' => $order++,
                'name' => $m['name'],
                'amount' => $m['amount'],
                'percentage' => $m['percentage'] ?? 0,
                'status' => 'draft',
                'due_date' => $m['due_date'] ?? null,
                'is_retention' => $m['is_retention'] ?? false,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $created,
        ], 201);
    }
}
