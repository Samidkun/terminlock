<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliverableApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_attaches_deliverable_to_milestone(): void
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
            'contract_number' => 'CTR-100',
            'total_amount' => 50000000,
        ]);
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'name' => 'Figma Tokens',
            'amount' => 50000000,
            'percentage' => 100,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('figma-spec.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)->postJson("/api/milestones/{$milestone->id}/deliverables", [
            'title' => 'Figma Design Tokens Export',
            'description' => 'Final token export and component library documentation.',
            'staging_url' => 'https://figma.com/file/123/Tokens',
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Figma Design Tokens Export',
                    'staging_url' => 'https://figma.com/file/123/Tokens',
                ],
            ]);

        $this->assertDatabaseHas('deliverables', [
            'milestone_id' => $milestone->id,
            'title' => 'Figma Design Tokens Export',
        ]);

        $deliverable = Deliverable::first();
        Storage::disk('public')->assertExists($deliverable->file_path);
    }
}
