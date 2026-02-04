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

        // 1. DATA STANDAR AUDIT
        $standards = [
            ['kode' => 'ISO 9001 & ISO 14001', 'nama' => 'Sistem Manajemen Mutu & Lingkungan', 'jenis_audit' => 'internal'],
            ['kode' => 'ISO 45001', 'nama' => 'Sistem Manajemen K3', 'jenis_audit' => 'internal'],
            ['kode' => 'IMS-INT', 'nama' => 'Integrated Management System (Internal)', 'jenis_audit' => 'internal'],
            ['kode' => 'Audit Internal', 'nama' => 'Audit Internal Rutin', 'jenis_audit' => 'internal'],
            ['kode' => 'RSPO', 'nama' => 'Roundtable on Sustainable Palm Oil', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SMAP', 'nama' => 'Sistem Manajemen Anti Penyuapan', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SNI', 'nama' => 'Standar Nasional Indonesia', 'jenis_audit' => 'eksternal'],
            ['kode' => 'IMS-EKS', 'nama' => 'Integrated Management System (External)', 'jenis_audit' => 'eksternal'],
            ['kode' => 'SMK3', 'nama' => 'Sistem Manajemen K3 (External)', 'jenis_audit' => 'eksternal'],
            ['kode' => 'Halal', 'nama' => 'Sertifikasi Halal', 'jenis_audit' => 'eksternal'],
        ];

        // MENGGUNAKAN LOOP MANUAL (Lebih Aman untuk PostgreSQL tanpa Unique Constraint tambahan)
        foreach ($standards as $std) {
            \App\Models\AuditStandard::updateOrCreate(
                ['kode' => $std['kode'], 'jenis_audit' => $std['jenis_audit']],
                ['nama' => $std['nama']]
            );
        }
    }
}
