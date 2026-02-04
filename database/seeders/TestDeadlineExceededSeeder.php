<?php

namespace Database\Seeders;

use App\Models\AuditFinding;
use App\Models\Audit;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDeadlineExceededSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk membuat test data findings dengan deadline terlewat
     * untuk testing fitur status pembeda
     */
    public function run(): void
    {
        // Ambil audit pertama yang ada
        $audit = Audit::first();

        if (!$audit) {
            $this->command->error('Tidak ada audit di database. Silakan buat audit terlebih dahulu.');
            return;
        }

        // Finding 1: Status responded dengan deadline sudah terlewat 5 hari
        AuditFinding::create([
            'audit_id' => $audit->id,
            'klausul' => '5.1',
            'uraian_temuan' => 'TEST: Dokumentasi Leadership belum lengkap',
            'kategori' => 'major',
            'std_referensi' => 'ISO 9001:2015',
            'auditor_nama' => $audit->auditor->name ?? 'Auditor',
            'deadline' => Carbon::now()->subDays(5)->toDateString(),  // 5 hari lalu
            'akar_masalah' => 'Tim belum memahami requirement',
            'tindakan_koreksi' => 'Melakukan training',
            'bukti_perbaikan' => null,
            'status_temuan' => 'responded',  // Sudah ada response
            'completion_reason' => null,  // Belum ditutup - akan auto-close
        ]);

        // Finding 2: Status responded dengan deadline sudah terlewat 10 hari
        AuditFinding::create([
            'audit_id' => $audit->id,
            'klausul' => '6.2',
            'uraian_temuan' => 'TEST: Manajemen risiko tidak terdokumentasi',
            'kategori' => 'minor',
            'std_referensi' => 'ISO 9001:2015',
            'auditor_nama' => $audit->auditor->name ?? 'Auditor',
            'deadline' => Carbon::now()->subDays(10)->toDateString(),  // 10 hari lalu
            'akar_masalah' => 'Kurangnya awareness',
            'tindakan_koreksi' => 'Membuat SOP baru',
            'bukti_perbaikan' => null,
            'status_temuan' => 'responded',  // Sudah ada response
            'completion_reason' => null,  // Belum ditutup - akan auto-close
        ]);

        // Finding 3: Status open (untuk kontrol - tidak akan auto-close)
        AuditFinding::create([
            'audit_id' => $audit->id,
            'klausul' => '7.1',
            'uraian_temuan' => 'TEST: Kompetensi karyawan kurang',
            'kategori' => 'observasi',
            'std_referensi' => 'ISO 9001:2015',
            'auditor_nama' => $audit->auditor->name ?? 'Auditor',
            'deadline' => Carbon::now()->subDays(3)->toDateString(),
            'akar_masalah' => null,
            'tindakan_koreksi' => null,
            'bukti_perbaikan' => null,
            'status_temuan' => 'open',  // Masih terbuka - tidak auto-close
            'completion_reason' => null,
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('Audit ID: ' . $audit->id);
        $this->command->info('');
        $this->command->info('Sekarang jalankan: php artisan findings:close-expired');
        $this->command->info('untuk melihat findings yang deadline exceeded otomatis tertutup.');
    }
}
