<?php

namespace App\Helpers;

use App\Models\AuditFinding;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeadlineHelper
{
    /**
     * Periksa dan tutup semua findings yang sudah melewati deadline
     * tanpa diterima oleh auditor
     *
     * Dijalankan secara berkala (schedule/cron atau manual)
     */
    public static function closeExpiredFindings()
    {
        try {
            $now = Carbon::now();

            // Cari semua findings yang:
            // 1. Status masih 'responded' (sudah ada response dari auditee, menunggu verifikasi auditor)
            // 2. Deadline sudah lewat
            // 3. Belum ada completion_reason (belum ditutup dengan alasan apapun)
            $expiredFindings = AuditFinding::where('status_temuan', 'responded')
                ->where('deadline', '<', $now->toDateString())
                ->whereNull('completion_reason')
                ->get();

            $count = 0;
            foreach ($expiredFindings as $finding) {
                $finding->update([
                    'status_temuan' => 'closed',
                    'completion_reason' => 'deadline_exceeded'
                ]);
                $count++;
                Log::info('Auto-closed finding due to deadline exceeded', [
                    'finding_id' => $finding->id,
                    'deadline' => $finding->deadline,
                    'current_date' => $now->toDateString()
                ]);
            }

            return [
                'success' => true,
                'count' => $count,
                'message' => "Berhasil menutup {$count} temuan yang melewati deadline."
            ];

        } catch (\Exception $e) {
            Log::error('Error in closeExpiredFindings: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return [
                'success' => false,
                'count' => 0,
                'message' => 'Gagal menutup findings yang expired: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Periksa status deadline untuk satu finding
     * Mengembalikan status deadline dan rekomendasi
     */
    public static function checkDeadlineStatus(AuditFinding $finding)
    {
        if (!$finding->deadline) {
            return [
                'has_deadline' => false,
                'status' => 'no_deadline',
                'message' => 'Tidak ada deadline'
            ];
        }

        $now = Carbon::now();
        $deadline = Carbon::parse($finding->deadline);
        $daysRemaining = $deadline->diffInDays($now, false); // Negatif jika sudah lewat

        if ($daysRemaining < 0) {
            return [
                'has_deadline' => true,
                'status' => 'expired',
                'days_overdue' => abs($daysRemaining),
                'message' => 'Deadline sudah terlewat ' . abs($daysRemaining) . ' hari'
            ];
        } elseif ($daysRemaining == 0) {
            return [
                'has_deadline' => true,
                'status' => 'today',
                'days_remaining' => 0,
                'message' => 'Deadline hari ini!'
            ];
        } else {
            return [
                'has_deadline' => true,
                'status' => 'pending',
                'days_remaining' => $daysRemaining,
                'message' => 'Sisa waktu: ' . $daysRemaining . ' hari'
            ];
        }
    }
}
