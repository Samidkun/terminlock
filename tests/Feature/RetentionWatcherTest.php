<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RetentionWatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_watcher_matures_expired_holds(): void
    {
        $workspace = Workspace::create(['name' => 'Agency']);
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client Corp',
            'company_name' => 'Client Corp',
        ]);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Core Platform',
            'contract_number' => 'CTR/RET/01',
            'total_amount' => 100000000,
        ]);

        // Case A: Expired hold (due_date in the past) -> should mature
        $expiredRetention = Milestone::create([
            'project_id' => $project->id,
            'order' => 2,
            'name' => 'Retensi Garansi 6 Bulan',
            'amount' => 10000000,
            'percentage' => 10,
            'status' => 'retention_hold',
            'is_retention' => true,
            'due_date' => now()->subDay(), // Expired yesterday
        ]);

        // Case B: Future hold (due_date in 30 days) -> should stay in hold
        $futureRetention = Milestone::create([
            'project_id' => $project->id,
            'order' => 3,
            'name' => 'Retensi Garansi Masa Depan',
            'amount' => 5000000,
            'percentage' => 5,
            'status' => 'retention_hold',
            'is_retention' => true,
            'due_date' => now()->addDays(30),
        ]);

        $exitCode = Artisan::call('terminlock:check-retentions');
        $this->assertEquals(0, $exitCode);

        // Case A matured
        $this->assertDatabaseHas('milestones', [
            'id' => $expiredRetention->id,
            'status' => 'retention_matured',
        ]);

        // Case B still in retention_hold
        $this->assertDatabaseHas('milestones', [
            'id' => $futureRetention->id,
            'status' => 'retention_hold',
        ]);
    }
}
