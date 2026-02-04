<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\DeadlineHelper;

class CloseExpiredFindings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'findings:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup secara otomatis findings yang sudah melewati deadline tanpa diterima auditor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Menjalankan pengecekan deadline findings...');

        $result = DeadlineHelper::closeExpiredFindings();

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        } else {
            $this->error($result['message']);
            return Command::FAILURE;
        }
    }
}
