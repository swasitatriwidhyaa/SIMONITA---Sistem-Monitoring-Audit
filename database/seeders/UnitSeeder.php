<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UnitSeeder extends Seeder
{
    public function run()
    {

        $units = [
            'Auditor',
            'Kedaton',
            'Way Berulu',
            'Pematang Kiwah',
            'Way Lima',
            'Bergen',
            'Tulung Buyut',
            'Musilandas',
            'Tebenan',
            'Baturaja',
            'Sungai Niru',
            'Padang Pelawi',
            'Senabing',
            'Beringin',
            'Ketahun',
            'Talopino',
            'Pagaralam',
            'Kantor Regional'
        ];

        foreach ($units as $unitName) {

            $email = strtolower(str_replace(' ', '', $unitName)) . '@ptpn7.com';

            // Cek apakah user sudah ada (berdasarkan email) agar tidak error duplikat
            if (!User::where('email', $email)->exists()) {
                User::create([
                    'name' => 'Unit ' . $unitName,  // Nama Akun: "Unit Kedaton"
                    'email' => $email,              // Email Login
                    'password' => Hash::make('12345678'), // Password Default
                    'role' => 'auditee',            // Role Unit
                ]);
            }
        }
    }
}