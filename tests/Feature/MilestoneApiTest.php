<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilestoneApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_milestone_sum_mismatch(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client A',
            'company_name' => 'Client A',
            'email' => 'client@a.com',
        ]);

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Mobile App Development',
            'contract_number' => 'CTR/2026/002',
            'total_amount' => 100000000,
            'retention_percentage' => 5,
            'retention_days' => 90,
            'status' => 'active',
        ]);

        // Total amount is 100,000,000 but milestones sum to 90,000,000 (mismatch!)
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/milestones", [
            'milestones' => [
                ['name' => 'Termin 1 (DP)', 'amount' => 30000000, 'percentage' => 30, 'due_date' => '2026-10-01'],
                ['name' => 'Termin 2 (Delivery)', 'amount' => 40000000, 'percentage' => 40, 'due_date' => '2026-11-01'],
                ['name' => 'Termin 3 (Final)', 'amount' => 20000000, 'percentage' => 20, 'due_date' => '2026-12-01'],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'MILESTONE_SUM_MISMATCH',
                ],
            ]);
    }

    public function test_accepts_valid_milestones_equaling_total_amount(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client A',
            'company_name' => 'Client A',
            'email' => 'client@a.com',
        ]);

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Web Redesign',
            'contract_number' => 'CTR/2026/003',
            'total_amount' => 100000000,
            'retention_percentage' => 5,
            'retention_days' => 90,
            'status' => 'active',
        ]);

        // Total is exactly 100,000,000 (50m + 45m + 5m retention)
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/milestones", [
            'milestones' => [
                ['name' => 'DP Kickoff', 'amount' => 50000000, 'percentage' => 50, 'due_date' => '2026-10-01'],
                ['name' => 'Pelunasan Launch', 'amount' => 45000000, 'percentage' => 45, 'due_date' => '2026-11-01'],
                ['name' => 'Retensi Garansi', 'amount' => 5000000, 'percentage' => 5, 'is_retention' => true, 'due_date' => '2027-02-01'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('milestones', 3);
        $this->assertDatabaseHas('milestones', [
            'project_id' => $project->id,
            'name' => 'Retensi Garansi',
            'is_retention' => true,
            'status' => 'draft',
        ]);
    }
}
