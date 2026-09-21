<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\SignoffRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MilestoneController extends Controller
{
    public function uploadDeliverable(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $milestone = Milestone::whereHas('project', function ($q) use ($user) {
            $q->where('workspace_id', $user->workspace_id);
        })->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'staging_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:20480'], // max 20MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('deliverables', 'public');
        }

        $deliverable = Deliverable::create([
            'milestone_id' => $milestone->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'staging_url' => $validated['staging_url'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $deliverable,
        ], 201);
    }

    public function requestSignoff(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $milestone = Milestone::whereHas('project', function ($q) use ($user) {
            $q->where('workspace_id', $user->workspace_id);
        })->with('deliverables')->findOrFail($id);

        // AC-3 / AC-4 guard: cannot request signoff without deliverables
        if ($milestone->deliverables->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_DELIVERABLES_ATTACHED',
                    'message' => 'Milestone must have at least one deliverable before requesting sign-off.',
                ],
            ], 422);
        }

        $expiresInDays = (int) $request->input('expires_in_days', 7);
        if ($expiresInDays <= 0) {
            $expiresInDays = 7;
        }

        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = now()->addDays($expiresInDays);

        $signoff = SignoffRequest::create([
            'milestone_id' => $milestone->id,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'status' => 'pending',
        ]);

        $milestone->update(['status' => 'awaiting_signoff']);

        $signoffUrl = url("/sign/{$rawToken}");

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $signoff->id,
                'signoff_url' => $signoffUrl,
                'token' => $rawToken,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ], 200);
    }
}
