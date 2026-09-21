<?php

namespace App\Http\Controllers;

use App\Models\BastCertificate;
use App\Models\SignoffRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicSignoffController extends Controller
{
    private function resolveToken(string $rawToken): array
    {
        $tokenHash = hash('sha256', $rawToken);
        $signoff = SignoffRequest::with(['milestone.project.client', 'milestone.deliverables'])
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$signoff) {
            return [
                'error' => response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_TOKEN',
                        'message' => 'Tautan persetujuan tidak valid.',
                    ],
                ], 404),
            ];
        }

        if ($signoff->expires_at->isPast() || $signoff->status === 'expired') {
            return [
                'error' => response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TOKEN_EXPIRED',
                        'message' => 'Tautan persetujuan ini sudah kedaluwarsa.',
                    ],
                ], 410),
            ];
        }

        return ['signoff' => $signoff];
    }

    public function show(Request $request, string $token): JsonResponse|InertiaResponse
    {
        $res = $this->resolveToken($token);
        if (isset($res['error'])) {
            return $res['error'];
        }

        $signoff = $res['signoff'];
        $milestone = $signoff->milestone;
        $project = $milestone->project;

        $deliverablesData = $milestone->deliverables->map(function ($d) {
            return [
                'id' => $d->id,
                'title' => $d->title,
                'description' => $d->description,
                'file_url' => $d->file_path ? url("storage/{$d->file_path}") : null,
                'staging_url' => $d->staging_url,
            ];
        });

        $payload = [
            'project_name' => $project->name,
            'contract_number' => $project->contract_number,
            'milestone_name' => $milestone->name,
            'amount' => $milestone->amount,
            'deliverables' => $deliverablesData,
            'expires_at' => $signoff->expires_at->toIso8601String(),
            'status' => $signoff->status,
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        }

        return Inertia::render('SignoffPortal', [
            'token' => $token,
            'data' => $payload,
        ]);
    }

    public function approve(Request $request, string $token): JsonResponse
    {
        $res = $this->resolveToken($token);
        if (isset($res['error'])) {
            return $res['error'];
        }

        $signoff = $res['signoff'];
        if ($signoff->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ALREADY_PROCESSED',
                    'message' => 'Tautan persetujuan ini telah diproses sebelumnya.',
                ],
            ], 400);
        }

        $validated = $request->validate([
            'signatory_name' => ['required', 'string', 'max:255'],
            'signatory_title' => ['required', 'string', 'max:255'],
        ]);

        $milestone = $signoff->milestone;
        $project = $milestone->project;

        // Construct Immutable Snapshot
        $snapshot = [
            'project_name' => $project->name,
            'contract_number' => $project->contract_number,
            'milestone_name' => $milestone->name,
            'amount' => $milestone->amount,
            'deliverables' => $milestone->deliverables->toArray(),
            'signatory_name' => $validated['signatory_name'],
            'signatory_title' => $validated['signatory_title'],
            'client_ip' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'signed_at' => now()->toIso8601String(),
        ];

        $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $checksum = hash('sha256', $snapshotJson);

        $now = now();
        $cleanContract = preg_replace('/[^a-zA-Z0-9]/', '', $project->contract_number);
        $bastNumber = sprintf(
            'BAST/%s/%s/%s-M%d',
            $now->format('Y'),
            $now->format('m'),
            strtoupper(substr($cleanContract, -6)),
            $milestone->order
        );

        $certificate = BastCertificate::create([
            'signoff_request_id' => $signoff->id,
            'milestone_id' => $milestone->id,
            'bast_number' => $bastNumber,
            'snapshot_data' => $snapshot,
            'sha256_checksum' => $checksum,
        ]);

        $signoff->update([
            'status' => 'signed',
            'signatory_name' => $validated['signatory_name'],
            'signatory_title' => $validated['signatory_title'],
            'client_ip' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'signed_at' => $now,
        ]);

        $milestone->update(['status' => 'bast_signed']);

        return response()->json([
            'success' => true,
            'data' => [
                'bast_number' => $certificate->bast_number,
                'signed_at' => $now->toIso8601String(),
                'sha256_checksum' => $checksum,
            ],
        ]);
    }

    public function revise(Request $request, string $token): JsonResponse
    {
        $res = $this->resolveToken($token);
        if (isset($res['error'])) {
            return $res['error'];
        }

        $signoff = $res['signoff'];
        $validated = $request->validate([
            'rejection_notes' => ['required', 'string', 'max:2000'],
        ]);

        $signoff->update([
            'status' => 'rejected',
            'rejection_notes' => $validated['rejection_notes'],
        ]);

        $milestone = $signoff->milestone;
        $milestone->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Catatan revisi telah dikirim ke tim vendor.',
        ]);
    }
}
