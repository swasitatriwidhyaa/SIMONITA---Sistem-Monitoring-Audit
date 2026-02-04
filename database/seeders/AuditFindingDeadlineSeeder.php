<?php

namespace Database\Seeders;

use App\Models\AuditFinding;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuditFindingDeadlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk membuat data testing temuan dengan berbagai status deadline
     */
    public function run(): void
    {
        // Data untuk testing:
        $testFindings = [
            // Finding 1: Status open (deadline belum terlewat)
            [
                'audit_id' => 1,
                'klausul' => '5.1',
                'uraian_temuan' => 'Dokumentasi Leadership tidak lengkap',
                'kategori' => 'major',
                'std_referensi' => 'ISO 9001:2015',
                'auditor_nama' => 'Budi Santoso',
                'deadline' => Carbon::now()->addDays(7)->toDateString(), // 7 hari ke depan
                'akar_masalah' => null,
                'tindakan_koreksi' => null,
                'bukti_perbaikan' => null,
                'status_temuan' => 'open',
                'completion_reason' => null,
            ],

            // Finding 2: Status responded (sudah ada respon, deadline belum terlewat)
            [
                'audit_id' => 1,
                'klausul' => '6.2',
                'uraian_temuan' => 'Manajemen risiko tidak terdokumentasi dengan baik',
                'kategori' => 'minor',
                'std_referensi' => 'ISO 9001:2015',
                'auditor_nama' => 'Budi Santoso',
                'deadline' => Carbon::now()->addDays(5)->toDateString(),
                'akar_masalah' => 'Tim belum memahami pentingnya dokumentasi risiko',
                'tindakan_koreksi' => 'Melakukan training dan membuat SOP baru',
                'bukti_perbaikan' => 'bukti/training_list.pdf',
                'status_temuan' => 'responded',
                'completion_reason' => null,
            ],

            // Finding 3: Status closed - diterima auditor (normal completion)
            [
                'audit_id' => 1,
                'klausul' => '7.1',
                'uraian_temuan' => 'Kompetensi karyawan audit tidak tercukupi',
                'kategori' => 'observasi',
                'std_referensi' => 'ISO 9001:2015',
                'auditor_nama' => 'Budi Santoso',
                'deadline' => Carbon::now()->subDays(2)->toDateString(),
                'akar_masalah' => 'Kurangnya program pengembangan SDM',
                'tindakan_koreksi' => 'Mendaftarkan auditor internal untuk training',
                'bukti_perbaikan' => 'bukti/sertifikat_training.pdf',
                'status_temuan' => 'closed',
                'completion_reason' => 'accepted_by_auditor', // Ditutup karena diterima auditor
            ],

            // Finding 4: Status closed - deadline exceeded
            // Scenario: Auditee sudah merespons tapi auditor belum approve, dan deadline sudah lewat
            [
                'audit_id' => 1,
                'klausul' => '8.2',
                'uraian_temuan' => 'Pengendalian produksi tidak sesuai standar',
                'kategori' => 'major',
                'std_referensi' => 'ISO 9001:2015',
                'auditor_nama' => 'Budi Santoso',
                'deadline' => Carbon::now()->subDays(5)->toDateString(), // Deadline 5 hari lalu
                'akar_masalah' => 'Mesin produksi tidak dikalibrasi rutin',
                'tindakan_koreksi' => 'Membuat jadwal kalibrasi bulanan',
                'bukti_perbaikan' => 'bukti/kalibrasi_report.pdf',
                'status_temuan' => 'closed',
                'completion_reason' => 'deadline_exceeded', // Ditutup karena deadline terlewat
            ],
        ];

        foreach ($testFindings as $findingData) {
            // Cek apakah audit_id 1 ada, jika tidak skip
            if (
                AuditFinding::where('audit_id', $findingData['audit_id'])
                    ->where('klausul', $findingData['klausul'])
                    ->exists()
            ) {
                continue; // Skip jika sudah ada
            }

            AuditFinding::create($findingData);
        }

        $this->command->info('AuditFindingDeadlineSeeder: Data testing berhasil dibuat!');
    }
}
