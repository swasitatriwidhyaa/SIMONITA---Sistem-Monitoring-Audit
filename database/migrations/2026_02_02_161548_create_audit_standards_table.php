<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_standards', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // Hapus ->unique() jika ada di sini
            $table->string('nama');
            $table->string('jenis_audit');
            $table->timestamps();

            // TAMBAHKAN INI: Membuat kombinasi kode dan jenis_audit menjadi unik
            $table->unique(['kode', 'jenis_audit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_standards');
    }
};
