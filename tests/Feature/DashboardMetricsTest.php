<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_cash_at_risk_metrics_correctly(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Corporate Client',
            'company_name' => 'Corporate Client',
        ]);

        // Project 1: Contract 100M
        // - M1 (Paid): 30M
        // - M2 (Awaiting signoff - risk): 40M
        // - M3 (Invoiced - risk): 25M
        // - Retention (Hold): 5M
        $p1 = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Project 1',
            'contract_number' => 'CTR-P1',
            'total_amount' => 100000000,
        ]);
        Milestone::create(['project_id' => $p1->id, 'order' => 1, 'name' => 'M1', 'amount' => 30000000, 'status' => 'paid']);
        Milestone::create(['project_id' => $p1->id, 'order' => 2, 'name' => 'M2', 'amount' => 40000000, 'status' => 'awaiting_signoff']);
        Milestone::create(['project_id' => $p1->id, 'order' => 3, 'name' => 'M3', 'amount' => 25000000, 'status' => 'invoiced']);
        Milestone::create(['project_id' => $p1->id, 'order' => 4, 'name' => 'Retention', 'amount' => 5000000, 'status' => 'retention_hold', 'is_retention' => true]);

        // Project 2: Contract 50M
        // - M1 (Draft - not yet at risk, not yet delivered): 40M
        // - Retention (Matured - claimable): 10M
        $p2 = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Project 2',
            'contract_number' => 'CTR-P2',
            'total_amount' => 50000000,
        ]);
        Milestone::create(['project_id' => $p2->id, 'order' => 1, 'name' => 'M1', 'amount' => 40000000, 'status' => 'draft']);
        Milestone::create(['project_id' => $p2->id, 'order' => 2, 'name' => 'Retensi Matured', 'amount' => 10000000, 'status' => 'retention_matured', 'is_retention' => true]);

        $response = $this->actingAs($user)->getJson('/api/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_contract_value' => 150000000, // 100M + 50M
                    'total_cash_collected' => 30000000,  // M1 of P1
                    'cash_at_risk' => 65000000,          // M2 (40M) + M3 (25M)
                    'retention_held' => 5000000,         // P1 retention hold
                    'retention_matured' => 10000000,     // P2 retention matured
                ],
            ]);
    }
}
