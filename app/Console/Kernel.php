<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        // Run daily early morning to auto-close expired audits
        $schedule->command('audits:close-expired')->dailyAt('00:05');

        // Run daily early morning to auto-close expired findings (that already responded but deadline passed)
        $schedule->command('findings:close-expired')->dailyAt('00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
