<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private function setupSignedProject(bool $withRetention = true): array
    {
        $workspace = Workspace::create(['name' => 'Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client Corp',
            'company_name' => 'Client Corp',
        ]);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Core Platform',
            'contract_number' => 'CTR/INV/01',
            'total_amount' => 100000000,
            'retention_percentage' => $withRetention ? 5 : 0,
            'retention_days' => 180,
        ]);

        $milestone1 = Milestone::create([
            'project_id' => $project->id,
            'order' => 1,
            'name' => 'Termin 1 (Delivery)',
            'amount' => 95000000,
            'percentage' => 95,
            'status' => 'bast_signed',
        ]);

        $retentionMilestone = null;
        if ($withRetention) {
            $retentionMilestone = Milestone::create([
                'project_id' => $project->id,
                'order' => 2,
                'name' => 'Uang Retensi Garansi 180 Hari',
                'amount' => 5000000,
                'percentage' => 5,
                'status' => 'draft',
                'is_retention' => true,
            ]);
        }

        return [$user, $milestone1, $retentionMilestone, $project];
    }

    public function test_issues_invoice_for_signed_bast(): void
    {
        [$user, $milestone1] = $this->setupSignedProject();

        $response = $this->actingAs($user)->postJson("/api/milestones/{$milestone1->id}/invoice", [
            'invoice_number' => 'INV/2026/09/001',
            'due_date' => '2026-10-15',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'invoice_number' => 'INV/2026/09/001',
                    'amount' => 95000000,
                    'status' => 'issued',
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'milestone_id' => $milestone1->id,
            'invoice_number' => 'INV/2026/09/001',
            'status' => 'issued',
        ]);

        // Milestone advances to invoiced
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone1->id,
            'status' => 'invoiced',
        ]);
    }

    public function test_cannot_issue_invoice_for_unsigned_milestone(): void
    {
        $workspace = Workspace::create(['name' => 'Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client Corp',
            'company_name' => 'Client Corp',
        ]);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Unsigned Project',
            'contract_number' => 'CTR/INV/02',
            'total_amount' => 50000000,
        ]);
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'order' => 1,
            'name' => 'Termin 1',
            'amount' => 50000000,
            'percentage' => 100,
            'status' => 'active', // NOT bast_signed
        ]);

        $response = $this->actingAs($user)->postJson("/api/milestones/{$milestone->id}/invoice", [
            'invoice_number' => 'INV/2026/09/002',
            'due_date' => '2026-10-15',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => ['code' => 'MILESTONE_NOT_SIGNED'],
            ]);
    }

    public function test_confirm_payment_triggers_retention_hold(): void
    {
        [$user, $milestone1, $retentionMilestone, $project] = $this->setupSignedProject();

        // 1. Issue invoice
        $this->actingAs($user)->postJson("/api/milestones/{$milestone1->id}/invoice", [
            'invoice_number' => 'INV/2026/09/001',
            'due_date' => '2026-10-15',
        ]);

        $invoice = Invoice::where('milestone_id', $milestone1->id)->first();

        // 2. Confirm payment
        $response = $this->actingAs($user)->postJson("/api/invoices/{$invoice->id}/pay", [
            'paid_at' => '2026-10-10',
            'notes' => 'Bukti Transfer Bank BCA #TRX998811',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'paid',
                ],
            ]);

        // Invoice status paid
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);

        // Milestone 1 status paid
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone1->id,
            'status' => 'paid',
        ]);

        // Since milestone 1 was the final delivery milestone, retention milestone must enter RETENTION_HOLD
        $this->assertDatabaseHas('milestones', [
            'id' => $retentionMilestone->id,
            'status' => 'retention_hold',
        ]);

        $retention = $retentionMilestone->fresh();
        $this->assertNotNull($retention->due_date);
    }
}
