<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_id')->constrained('audit_standards')->onDelete('cascade');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('auditee_id')->constrained('users')->onDelete('cascade');
            $table->string('auditor_name')->nullable(); // Nama auditor pelaksana
            $table->string('id_audit_plan')->nullable();
            $table->date('tanggal_audit');
            $table->date('deadline');
            $table->enum('status', ['requested', 'planned', 'ongoing', 'finished', 'rejected'])->default('planned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};