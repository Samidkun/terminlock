<?php

namespace Tests\Feature;

use App\Models\BastCertificate;
use App\Models\Client;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\SignoffRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicSignoffTest extends TestCase
{
    use RefreshDatabase;

    private function createMilestoneWithSignoff(int $expiresInDays = 7): array
    {
        $workspace = Workspace::create(['name' => 'Agency']);
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'PT Maju Terus',
            'company_name' => 'PT Maju Terus',
            'email' => 'client@majuterus.com',
        ]);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Enterprise ERP',
            'contract_number' => 'CTR/ERP/2026/01',
            'total_amount' => 100000000,
        ]);
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'name' => 'Termin 1: Database & Core Engine',
            'amount' => 40000000,
            'percentage' => 40,
            'status' => 'awaiting_signoff',
        ]);
        $deliverable = Deliverable::create([
            'milestone_id' => $milestone->id,
            'title' => 'Core Database Schema & ERD',
            'description' => 'Delivered 18 relational tables and seeders.',
            'staging_url' => 'https://staging.erp.example.com',
        ]);

        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        $signoff = SignoffRequest::create([
            'milestone_id' => $milestone->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays($expiresInDays),
            'status' => $expiresInDays < 0 ? 'expired' : 'pending',
        ]);

        return [$rawToken, $signoff, $milestone, $project, $client];
    }

    public function test_client_accesses_signoff_portal_without_auth(): void
    {
        [$rawToken, $signoff, $milestone, $project] = $this->createMilestoneWithSignoff();

        $response = $this->getJson("/sign/{$rawToken}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'project_name' => 'Enterprise ERP',
                    'contract_number' => 'CTR/ERP/2026/01',
                    'milestone_name' => 'Termin 1: Database & Core Engine',
                    'amount' => 40000000,
                    'deliverables' => [
                        [
                            'title' => 'Core Database Schema & ERD',
                            'staging_url' => 'https://staging.erp.example.com',
                        ],
                    ],
                ],
            ]);
    }

    public function test_client_approves_bast_generates_certificate(): void
    {
        [$rawToken, $signoff, $milestone] = $this->createMilestoneWithSignoff();

        $response = $this->postJson("/sign/{$rawToken}/approve", [
            'signatory_name' => 'Bambang Sudibyo',
            'signatory_title' => 'Direktur Operasional',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'bast_number',
                    'signed_at',
                    'sha256_checksum',
                ],
            ]);

        // Milestone status must advance to bast_signed
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => 'bast_signed',
        ]);

        // Signoff request must be marked signed
        $this->assertDatabaseHas('signoff_requests', [
            'id' => $signoff->id,
            'status' => 'signed',
            'signatory_name' => 'Bambang Sudibyo',
            'signatory_title' => 'Direktur Operasional',
        ]);

        // BAST Certificate must be created
        $this->assertDatabaseHas('bast_certificates', [
            'milestone_id' => $milestone->id,
            'signoff_request_id' => $signoff->id,
        ]);

        $cert = BastCertificate::where('milestone_id', $milestone->id)->first();
        $this->assertNotNull($cert->sha256_checksum);
        $this->assertNotEmpty($cert->snapshot_data);
    }

    public function test_client_requests_revision_reverts_milestone(): void
    {
        [$rawToken, $signoff, $milestone] = $this->createMilestoneWithSignoff();

        $response = $this->postJson("/sign/{$rawToken}/revise", [
            'rejection_notes' => 'Ada tabel yang kurang, mohon tambahkan tabel audits.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Catatan revisi telah dikirim ke tim vendor.',
            ]);

        // Milestone reverts to active
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('signoff_requests', [
            'id' => $signoff->id,
            'status' => 'rejected',
            'rejection_notes' => 'Ada tabel yang kurang, mohon tambahkan tabel audits.',
        ]);
    }

    public function test_expired_or_invalid_token_is_rejected(): void
    {
        // Case 1: Non-existent token
        $response1 = $this->getJson('/sign/invalid-token-12345');
        $response1->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => ['code' => 'INVALID_TOKEN'],
            ]);

        // Case 2: Expired token
        [$expiredToken] = $this->createMilestoneWithSignoff(-1); // Expired yesterday
        $response2 = $this->getJson("/sign/{$expiredToken}");
        $response2->assertStatus(410)
            ->assertJson([
                'success' => false,
                'error' => ['code' => 'TOKEN_EXPIRED'],
            ]);

        // Case 3: Try to approve expired token
        $response3 = $this->postJson("/sign/{$expiredToken}/approve", [
            'signatory_name' => 'Alice',
            'signatory_title' => 'Manager',
        ]);
        $response3->assertStatus(410);
    }
}
