<?php

namespace App\Console\Commands;

use App\Models\Milestone;
use Illuminate\Console\Command;

class CheckRetentionsCommand extends Command
{
    protected $signature = 'terminlock:check-retentions';
    protected $description = 'Scan active retention holds and transition matured retentions to RETENTION_MATURED';

    public function handle(): int
    {
        $matured = Milestone::where('status', 'retention_hold')
            ->where('due_date', '<=', now())
            ->get();

        $count = 0;
        foreach ($matured as $milestone) {
            $milestone->update(['status' => 'retention_matured']);
            $count++;
            $this->info("Milestone {$milestone->id} ({$milestone->name}) matured. Ready for final invoice.");
        }

        $this->info("Processed {$count} matured retention milestone(s).");
        return 0;
    }
}
