<?php

namespace Database\Seeders;

use App\Models\AuditStandard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateAuditStandardsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua data lama
        AuditStandard::truncate();

        // Internal Audits
        $internalAudits = [
            'ISO 9001 & ISO 14001' => 'ISO 9001 & ISO 14001',
            'IMS' => 'IMS',
        ];

        foreach ($internalAudits as $kode => $nama) {
            AuditStandard::updateOrCreate(
                ['kode' => $kode, 'jenis_audit' => 'internal'],
                [
                    'nama' => $nama,
                    'jenis_audit' => 'internal'
                ]
            );
        }

        // External Audits
        $externalAudits = [
            'ISO 9001 & ISO 14001' => 'ISO 9001 & ISO 14001',
            'IMS' => 'Integrated Management System',
            'SMAP' => 'Sistem Manajemen Keamanan Pangan',
            'SNI' => 'Standar Nasional Indonesia',
            'SMK3' => 'Sistem Manajemen Keselamatan dan Kesehatan Kerja',
            'Halal' => 'Sertifikasi Halal',
        ];

        foreach ($externalAudits as $kode => $nama) {
            AuditStandard::updateOrCreate(
                ['kode' => $kode, 'jenis_audit' => 'eksternal'],
                [
                    'nama' => $nama,
                    'jenis_audit' => 'eksternal'
                ]
            );
        }

        $this->command->info('✅ Audit standards updated with correct jenis_audit types!');
    }
}
