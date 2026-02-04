<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFinding extends Model
{
    use HasFactory;

    // PASTIKAN SEMUA KOLOM INI ADA
    protected $fillable = [
        'audit_id',
        'kategori',
        'klausul',
        'uraian_temuan',
        'std_referensi',
        'auditor_nama',
        'lokasi',
        'deadline',
        'akar_masalah',
        'tindakan_koreksi',
        'tindakan_korektif',
        'bukti_perbaikan',
        'status_temuan',
        'completion_reason',
        'catatan_auditor',
        'submitted_at',
        'inisial_input'
    ];

    protected $casts = [
        'bukti_perbaikan' => 'array',
        'deadline' => 'date',
        'submitted_at' => 'datetime',
    ];

    /**
     * Mutator untuk memastikan submitted_at disimpan dengan timezone yang benar
     */
    public function setSubmittedAtAttribute($value)
    {
        if ($value) {
            // Jika value adalah string, parse sebagai WIB
            if (is_string($value)) {
                $this->attributes['submitted_at'] = \Carbon\Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $value,
                    'Asia/Jakarta'
                );
            } else {
                // Jika sudah Carbon instance, convert ke UTC untuk storage
                $this->attributes['submitted_at'] = $value;
            }
        }
    }

    /**
     * Accessor untuk menampilkan submitted_at dalam timezone WIB
     */
    public function getSubmittedAtForDisplayAttribute()
    {
        if ($this->submitted_at) {
            return $this->submitted_at->timezone('Asia/Jakarta');
        }
        return null;
    }

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * Accessor untuk mendapatkan label status yang user-friendly
     */
    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'open' => 'Terbuka',
            'responded' => 'Merespons',
            'closed' => 'Selesai'
        ];

        $label = $statusLabels[$this->status_temuan] ?? $this->status_temuan;

        // Jika status closed, tampilkan alasan penyelesaiannya
        if ($this->status_temuan === 'closed' && $this->completion_reason) {
            $reasonLabels = [
                'accepted_by_auditor' => 'Selesai - Diterima Auditor',
                'deadline_exceeded' => 'Selesai - Melewati Batas Waktu'
            ];
            $label = $reasonLabels[$this->completion_reason] ?? $label;
        }

        return $label;
    }

    /**
     * Cek apakah deadline sudah terlewat
     */
    public function isDeadlineExceeded()
    {
        if (!$this->deadline) {
            return false;
        }

        return \Carbon\Carbon::parse($this->deadline)->isPast() && $this->status_temuan !== 'closed';
    }

    /**
     * Cek status deadline (approaching, exceeded, atau ok)
     */
    public function getDeadlineStatus()
    {
        if (!$this->deadline || $this->status_temuan === 'closed') {
            return 'ok';
        }

        $now = \Carbon\Carbon::now();
        $deadline = \Carbon\Carbon::parse($this->deadline);
        $daysRemaining = $now->diffInDays($deadline, false);

        if ($daysRemaining < 0) {
            return 'exceeded';
        } elseif ($daysRemaining <= 3) {
            return 'approaching';
        }

        return 'ok';
    }

    /**
     * Dapatkan hari tersisa untuk deadline
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->deadline) {
            return null;
        }

        $now = \Carbon\Carbon::now();
        $deadline = \Carbon\Carbon::parse($this->deadline);
        return $now->diffInDays($deadline, false);
    }
}
