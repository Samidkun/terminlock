<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_project_with_integer_contract_amount(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'PT Maju Bersama',
            'company_name' => 'PT Maju Bersama',
            'email' => 'finance@majubersama.co.id',
        ]);

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'client_id' => $client->id,
            'name' => 'E-Commerce Replatforming',
            'contract_number' => 'CTR/2026/09/001',
            'total_amount' => 150000000,
            'retention_percentage' => 5,
            'retention_days' => 180,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'E-Commerce Replatforming',
                    'contract_number' => 'CTR/2026/09/001',
                    'total_amount' => 150000000,
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'contract_number' => 'CTR/2026/09/001',
            'total_amount' => 150000000,
        ]);
    }

    public function test_rejects_duplicate_contract_number_in_same_workspace(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client A',
            'company_name' => 'Client A',
            'email' => 'client@a.com',
        ]);

        Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Project 1',
            'contract_number' => 'CTR/2026/001',
            'total_amount' => 50000000,
            'retention_percentage' => 0,
            'retention_days' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'client_id' => $client->id,
            'name' => 'Project 2',
            'contract_number' => 'CTR/2026/001',
            'total_amount' => 60000000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_number']);
    }
}
