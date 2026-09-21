<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\SignoffRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::create(['name' => 'Acme Tech Solutions']);

        $user = User::create([
            'workspace_id' => $workspace->id,
            'name' => 'Agency Admin',
            'email' => 'admin@acme.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Mega Corporindo',
            'company_name' => 'PT Mega Corporindo',
            'email' => 'finance@megacorp.com',
            'phone' => '08123456789',
        ]);

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'name' => 'Core Banking Transformation',
            'contract_number' => 'CTR/BANK/2026/09',
            'total_amount' => 250000000,
            'retention_percentage' => 5,
            'retention_days' => 180,
            'status' => 'active',
        ]);

        Milestone::create([
            'project_id' => $project->id,
            'order' => 1,
            'name' => 'Termin 1 (DP 30%)',
            'amount' => 75000000,
            'percentage' => 30,
            'status' => 'paid',
            'due_date' => now()->subDays(15),
        ]);

        $m2 = Milestone::create([
            'project_id' => $project->id,
            'order' => 2,
            'name' => 'Termin 2 (Backend Core & Schema 40%)',
            'amount' => 100000000,
            'percentage' => 40,
            'status' => 'awaiting_signoff',
            'due_date' => now()->addDays(7),
        ]);

        Deliverable::create([
            'milestone_id' => $m2->id,
            'title' => 'Core Banking Schema v1 & OpenAPI Specs',
            'description' => 'Complete specification and seed database with 45 tables.',
            'staging_url' => 'https://staging.megacorp.internal',
        ]);

        $rawToken = 'e2e-token-valid-12345';
        $tokenHash = hash('sha256', $rawToken);

        SignoffRequest::create([
            'milestone_id' => $m2->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        Milestone::create([
            'project_id' => $project->id,
            'order' => 3,
            'name' => 'Termin 3 (Frontend & Integration 25%)',
            'amount' => 62500000,
            'percentage' => 25,
            'status' => 'active',
            'due_date' => now()->addDays(30),
        ]);

        Milestone::create([
            'project_id' => $project->id,
            'order' => 4,
            'name' => 'Retensi Pemeliharaan 180 Hari (5%)',
            'amount' => 12500000,
            'percentage' => 5,
            'status' => 'draft',
            'is_retention' => true,
            'due_date' => now()->addDays(210),
        ]);
    }
}
