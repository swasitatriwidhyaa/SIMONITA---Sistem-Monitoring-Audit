<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT AUDITOR UTAMA
        User::updateOrCreate(
            ['email' => 'auditor@ptpn7.com'],
            [
                'name' => 'Auditor Utama',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'unit_kerja' => 'Kantor Direksi',
                'wilayah' => 'Kantor Regional'
            ]
        );

        // 2. DAFTAR UNIT KERJA BERDASARKAN WILAYAH
        $dataUnits = [
            'Kantor Regional' => ['Kantor Regional'],
            'Wilayah Lampung' => ['Unit Bergen', 'Unit Kedaton', 'Unit Tulung Buyut', 'Unit Pematang Kiwah', 'Unit Way Berulu', 'Unit Way Lima'],
            'Wilayah Sumatera Selatan' => ['Unit Baturaja', 'Unit Beringin', 'Unit Musilandas', 'Unit Pagaralam', 'Unit Senabing', 'Unit Sungai Niru', 'Unit Tebenan'],
            'Wilayah Bengkulu' => ['Unit Ketahun', 'Unit Padang Pelawi', 'Unit Talopino']
        ];

        foreach ($dataUnits as $wilayah => $units) {
            foreach ($units as $unitName) {
                $email = strtolower(str_replace(' ', '', $unitName)) . '@ptpn7.com';

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Admin ' . $unitName,
                        'password' => Hash::make('password'),
                        'role' => 'auditee',
                        'unit_kerja' => $unitName,
                        'wilayah' => $wilayah
                    ]
                );
            }
        }
    }
}
