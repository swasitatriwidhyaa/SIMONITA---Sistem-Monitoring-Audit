<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Standar (Satu audit punya satu standar, misal Audit ini khusus ISO 9001)
// Tambahkan di dalam class Audit extends Model { ... }

    public function standard()
    {
        // Relasi ke model AuditStandard
        return $this->belongsTo(AuditStandard::class, 'standard_id');
    }

    // Relasi ke User sebagai AUDITOR
    // Kita harus sebutkan 'auditor_id' karena nama fungsinya bukan 'user'
    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    // Relasi ke User sebagai AUDITEE (Objek Terperiksa)
    public function auditee()
    {
        return $this->belongsTo(User::class, 'auditee_id');
    }

    // Relasi: Satu Jadwal Audit bisa punya BANYAK Temuan masalah
    public function findings()
    {
        return $this->hasMany(AuditFinding::class);
    }


}