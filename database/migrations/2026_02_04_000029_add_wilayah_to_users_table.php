<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambah kolom.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada agar tidak error di PostgreSQL
            if (!Schema::hasColumn('users', 'wilayah')) {
                $table->string('wilayah')->nullable()->after('unit_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wilayah')) {
                $table->dropColumn('wilayah');
            }
        });
    }
};
