<?php

namespace Database\Seeders;

use App\Models\AuditStandard;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. JALANKAN USER SEEDER
        $this->call(UserSeeder::class);

        // 2. DATA STANDAR AUDIT
        $standards = [
            // INTERNAL
            ['kode' => 'ISO 9001 & ISO 14001', 'nama' => 'Sistem Manajemen Mutu & Lingkungan', 'jenis_audit' => 'internal'],
            ['kode' => 'IMS', 'nama' => 'Integrated Management System', 'jenis_audit' => 'internal'],
            ['kode' => 'SMAP', 'nama' => 'Sistem Manajemen Anti Penyuapan', 'jenis_audit' => 'internal'],
            ['kode' => 'SMK3', 'nama' => 'Sistem Manajemen K3', 'jenis_audit' => 'internal'],
            ['kode' => 'HALAL', 'nama' => 'Sertifikasi Halal', 'jenis_audit' => 'internal'],

            // EKSTERNAL
            ['kode' => 'ISO 9001 & ISO 14001', 'nama' => 'Sistem Manajemen Mutu & Lingkungan', 'jenis_audit' => 'eksternal'],
            ['kode' => 'IMS', 'nama' => 'Integrated Management System', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SMAP', 'nama' => 'Sistem Manajemen Anti Penyuapan', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SMK3', 'nama' => 'Sistem Manajemen K3', 'jenis_audit' => 'eksternal'],
            ['kode' => 'HALAL', 'nama' => 'Sertifikasi Halal', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SNI', 'nama' => 'Standar Nasional Indonesia', 'jenis_audit' => 'eksternal'],
        ];

        // MENGGUNAKAN LOOP MANUAL (Lebih Aman untuk sinkronisasi data)
        foreach ($standards as $std) {
            AuditStandard::updateOrCreate(
                ['kode' => $std['kode'], 'jenis_audit' => $std['jenis_audit']],
                ['nama' => $std['nama']]
            );
        }
    }
}
