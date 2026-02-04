<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('audits')->onDelete('cascade');

            // Kolom Temuan (Auditor)
            $table->text('klausul');
            $table->string('std_referensi')->nullable();
            $table->string('auditor_nama')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('uraian_temuan');
            $table->enum('kategori', ['major', 'minor', 'observasi']);
            $table->date('deadline')->nullable();

            // Kolom Respon (Auditee)
            $table->text('akar_masalah')->nullable();
            $table->text('tindakan_koreksi')->nullable();
            $table->text('tindakan_korektif')->nullable();

            // Kolom Status & Verifikasi
            $table->enum('status_temuan', ['open', 'responded', 'closed'])->default('open');
            $table->enum('completion_reason', ['accepted_by_auditor', 'deadline_exceeded'])->nullable();
            $table->text('catatan_auditor')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Bukti Perbaikan (Format JSON untuk banyak file di PostgreSQL)
            $table->json('bukti_perbaikan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
    }
};
