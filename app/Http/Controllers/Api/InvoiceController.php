<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Milestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function issue(Request $request, string $milestoneId): JsonResponse
    {
        $user = $request->user();
        $milestone = Milestone::whereHas('project', function ($q) use ($user) {
            $q->where('workspace_id', $user->workspace_id);
        })->with('project')->findOrFail($milestoneId);

        // AC-9: Must be bast_signed before invoice can be issued
        if ($milestone->status !== 'bast_signed') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MILESTONE_NOT_SIGNED',
                    'message' => 'Invoice hanya dapat diterbitkan untuk milestone yang telah ditandatangani BAST.',
                ],
            ], 422);
        }

        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'due_date' => ['required', 'date'],
        ]);

        $invoice = Invoice::create([
            'milestone_id' => $milestone->id,
            'invoice_number' => $validated['invoice_number'],
            'amount' => $milestone->amount,
            'issued_at' => now(),
            'due_date' => $validated['due_date'],
            'status' => 'issued',
        ]);

        $milestone->update(['status' => 'invoiced']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ], 201);
    }

    public function pay(Request $request, string $invoiceId): JsonResponse
    {
        $user = $request->user();
        $invoice = Invoice::whereHas('milestone.project', function ($q) use ($user) {
            $q->where('workspace_id', $user->workspace_id);
        })->with(['milestone.project.milestones'])->findOrFail($invoiceId);

        $validated = $request->validate([
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $paidAt = $validated['paid_at'] ?? now();

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $paidAt,
            'payment_notes' => $validated['notes'] ?? null,
        ]);

        $milestone = $invoice->milestone;
        $milestone->update(['status' => 'paid']);

        // AC-10: If all non-retention milestones are paid, activate RETENTION_HOLD
        $project = $milestone->project;
        $nonRetentionCount = $project->milestones()->where('is_retention', false)->count();
        $paidNonRetentionCount = $project->milestones()->where('is_retention', false)->where('status', 'paid')->count();

        if ($nonRetentionCount > 0 && $nonRetentionCount === $paidNonRetentionCount) {
            $retentionMilestone = $project->milestones()
                ->where('is_retention', true)
                ->where('status', 'draft')
                ->first();

            if ($retentionMilestone) {
                $days = $project->retention_days ?: 180;
                $retentionMilestone->update([
                    'status' => 'retention_hold',
                    'due_date' => now()->addDays($days),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $invoice->fresh(),
        ], 200);
    }
}
