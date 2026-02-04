<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditStandard;

class StandarUnitSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan kode lama yang duplikat atau terpisah
        AuditStandard::whereIn('kode', ['ISO 9001', 'ISO 14001'])->delete();

        $standards = [
            [
                'kode' => 'ISO 9001 & ISO 14001',
                'nama' => 'Sistem Manajemen Mutu & Lingkungan',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'SMAP',
                'nama' => 'Sistem Manajemen Anti Penyuapan',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'SNI',
                'nama' => 'Standar Nasional Indonesia',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'IMS',
                'nama' => 'Integrated Management System',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'SMK3',
                'nama' => 'Sistem Manajemen K3',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'Halal',
                'nama' => 'Sertifikasi Halal',
                'jenis_audit' => 'eksternal'
            ],
            [
                'kode' => 'Audit Internal',
                'nama' => 'Audit Internal Rutin',
                'jenis_audit' => 'internal'
            ]
        ];

        foreach ($standards as $standard) {
            // Gunakan KODE saja sebagai kunci unik agar PostgreSQL tidak protes
            AuditStandard::updateOrCreate(
                ['kode' => $standard['kode']],
                $standard
            );
        }
    }
}
