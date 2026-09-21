<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\SignoffRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignoffRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_secure_signoff_token(): void
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
            'name' => 'Design System',
            'contract_number' => 'CTR-101',
            'total_amount' => 50000000,
        ]);
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'name' => 'Figma Tokens',
            'amount' => 50000000,
            'percentage' => 100,
            'status' => 'active',
        ]);

        Deliverable::create([
            'milestone_id' => $milestone->id,
            'title' => 'Design System v1',
            'file_path' => 'deliverables/test.pdf',
        ]);

        $response = $this->actingAs($user)->postJson("/api/milestones/{$milestone->id}/request-signoff", [
            'expires_in_days' => 7,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'signoff_url',
                    'token',
                    'expires_at',
                ],
            ]);

        $rawToken = $response->json('data.token');
        $expectedHash = hash('sha256', $rawToken);

        $this->assertDatabaseHas('signoff_requests', [
            'milestone_id' => $milestone->id,
            'token_hash' => $expectedHash,
            'status' => 'pending',
        ]);

        // Milestone status must advance to awaiting_signoff
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => 'awaiting_signoff',
        ]);
    }

    public function test_cannot_request_signoff_without_deliverables(): void
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
            'name' => 'Design System',
            'contract_number' => 'CTR-102',
            'total_amount' => 50000000,
        ]);
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'name' => 'Empty Milestone',
            'amount' => 50000000,
            'percentage' => 100,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->postJson("/api/milestones/{$milestone->id}/request-signoff");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'NO_DELIVERABLES_ATTACHED',
                ],
            ]);
    }
}
