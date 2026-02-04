<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditFinding;
use Carbon\Carbon;

class AutoCloseExpiredFindings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'findings:auto-close';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Auto-close audit findings that have exceeded their deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        
        // Find all open findings with deadline that has passed
        $expiredFindings = AuditFinding::where('status_temuan', 'open')
            ->where('deadline', '<', $today)
            ->whereNotNull('deadline')
            ->get();

        $count = $expiredFindings->count();

        if ($count === 0) {
            $this->info('✓ Tidak ada temuan yang melampaui batas waktu.');
            return 0;
        }

        foreach ($expiredFindings as $finding) {
            try {
                $finding->update([
                    'status_temuan' => 'closed',
                    'completion_reason' => 'deadline_exceeded',
                ]);
                
                \Log::info("Auto-closed finding {$finding->id} due to deadline expiration", [
                    'deadline' => $finding->deadline,
                    'today' => $today,
                ]);

                $this->line("✓ Temuan #{$finding->id} ditutup otomatis (deadline terlampaui: {$finding->deadline})");
            } catch (\Exception $e) {
                \Log::error("Failed to auto-close finding {$finding->id}: " . $e->getMessage());
                $this->error("✗ Gagal menutup temuan #{$finding->id}: " . $e->getMessage());
            }
        }

        $this->info("✓ {$count} temuan berhasil ditutup otomatis.");
        return 0;
    }
}
