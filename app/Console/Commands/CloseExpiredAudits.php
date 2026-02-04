<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CloseExpiredAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audits:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close audits whose deadline has passed and mark findings closed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $this->info("Checking for audits with deadline before {$today}...");

        $audits = \App\Models\Audit::whereDate('deadline', '<', $today)
            ->where('status', '!=', 'finished')
            ->get();

        foreach ($audits as $audit) {
            DB::beginTransaction();
            try {
                // Close audit findings that are not already closed
                $audit->findings()->where('status_temuan', '!=', 'closed')->update(['status_temuan' => 'closed']);

                // Update audit status to 'finished' (enum-valid)
                $audit->status = 'finished';
                $audit->save();

                Log::info("Auto-closed audit id={$audit->id} due to passed deadline {$audit->deadline}");
                $this->info("Closed audit id={$audit->id}");

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to auto-close audit id=' . $audit->id . ': ' . $e->getMessage());
                $this->error('Failed to close audit id=' . $audit->id);
            }
        }

        $this->info('Done.');

        return 0;
    }
}
