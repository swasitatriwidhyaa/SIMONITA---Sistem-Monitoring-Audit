<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditStandard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DeadlineHelper;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // 1. SET WAKTU WIB
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        // ------------------------------------------------------------------
        // UPDATE OTOMATIS (AUTO-CLOSE AUDIT) - PERBAIKAN DI SINI
        // ------------------------------------------------------------------
        // Mengubah status menjadi 'finished' (bukan 'closed') agar sesuai database
        Audit::whereDate('deadline', '<', $today)
            ->whereNotIn('status', ['finished', 'selesai (closed)', 'closed'])
            ->update(['status' => 'finished']);

        // Opsional: Tutup juga finding
        DeadlineHelper::closeExpiredFindings();
        // ------------------------------------------------------------------

        $user = Auth::user();
        $isAuditor = $user && strtolower($user->role ?? '') === 'auditor';

        // 2. Query Scope
        $getQuery = function () use ($user, $isAuditor) {
            $query = Audit::query();
            if (!$isAuditor) {
                if ($user && $user->unit_kerja) {
                    $query->whereHas('auditee', function ($q) use ($user) {
                        $q->where('unit_kerja', $user->unit_kerja);
                    });
                } else {
                    $query->where('auditee_id', $user->id);
                }
            }
            return $query;
        };

        $visibleAuditIds = $getQuery()->pluck('id');

        // 3. Hitung Statistik
        $totalAudit = $getQuery()->count();

        // Kita hitung 'finished' sebagai status selesai di database
        $totalSelesai = $getQuery()->whereIn('status', ['finished', 'selesai (closed)', 'Selesai (Closed)', 'closed'])->count();
        $totalProses = $getQuery()->whereIn('status', ['ongoing', 'proses', 'Proses', 'Process'])->count();
        $totalOpen = $totalAudit - ($totalSelesai + $totalProses);

        // 4. Widget Deadline (Hanya yang BELUM LEWAT / Masa Depan)
        $upcomingDeadlines = $getQuery()
            ->whereNotIn('status', ['finished', 'selesai (closed)', 'closed'])
            ->whereDate('deadline', '>=', $today)
            ->orderBy('deadline', 'asc')
            ->take(3)
            ->get();

        $upcomingDeadlines->each(function ($item) use ($today) {
            $deadline = Carbon::parse($item->deadline)->timezone('Asia/Jakarta')->startOfDay();
            $item->selisih_hari = $today->diffInDays($deadline, false);
        });

        // 5. Tabel Audit Terbaru
        $latestAudits = $getQuery()
            ->with(['auditor', 'auditee', 'findings'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 6. LOGIKA STATUS TAMPILAN (VIEW)
        $latestAudits->transform(function ($audit) use ($today) {
            $deadline = Carbon::parse($audit->deadline)->timezone('Asia/Jakarta')->startOfDay();
            $statusLower = strtolower($audit->status);

            // Cek apakah status DB sudah 'finished'/'closed'
            $isClosed = in_array($statusLower, ['finished', 'selesai (closed)', 'closed']);

            // CEK FINDINGS: Apakah ada findings yang deadline-nya sudah lewat tapi belum diselesaikan
            $hasOverdueFindings = false;
            $daysExceeded = 0;

            foreach ($audit->findings as $finding) {
                $findingDeadline = Carbon::parse($finding->deadline)->timezone('Asia/Jakarta')->startOfDay();

                // Jika finding deadline sudah lewat dan finding belum closed
                if ($findingDeadline->lessThan($today) && $finding->status_temuan !== 'closed') {
                    $hasOverdueFindings = true;
                    $daysExceeded = max($daysExceeded, $today->diffInDays($findingDeadline));
                    break;
                }
            }

            // KASUS 1: DEADLINE SUDAH LEWAT (AUDIT ATAU FINDINGS)
            // Jika main audit deadline sudah lewat ATAU ada findings yang lewat deadline
            if ($deadline->lessThan($today) || $hasOverdueFindings) {
                // Jika belum ada daysExceeded dari findings, hitung dari main deadline
                if ($daysExceeded == 0) {
                    $daysExceeded = $today->diffInDays($deadline);
                }
                $audit->days_exceeded = $daysExceeded;
                $audit->status_label = "Selesai - Lewat Deadline ({$daysExceeded} hari)";
                $audit->status_type = 'deadline_exceeded'; // Merah
            }
            // KASUS 2: SEMUA FINDINGS SUDAH SELESAI & DISETUJUI AUDITOR
            // Jika semua findings closed dan bukan karena deadline_exceeded
            else {
                $totalFindings = $audit->findings->count();
                if ($totalFindings > 0) {
                    $completedFindings = $audit->findings
                        ->where('status_temuan', 'closed')
                        ->where('completion_reason', '!=', 'deadline_exceeded')
                        ->count();

                    // Semua findings selesai dan disetujui -> Hijau
                    if ($completedFindings == $totalFindings) {
                        $audit->status_label = 'Selesai (Diterima Auditor)';
                        $audit->status_type = 'accepted'; // Hijau
                    }
                    // Ada findings yang belum selesai -> Status lain
                    else {
                        // Cek status DB
                        if ($isClosed) {
                            $audit->status_label = 'Selesai (Diterima Auditor)';
                            $audit->status_type = 'accepted';
                        } elseif (in_array($statusLower, ['ongoing', 'proses', 'process'])) {
                            $audit->status_label = 'Proses';
                            $audit->status_type = 'ongoing';
                        } else {
                            $audit->status_label = 'Open';
                            $audit->status_type = 'open';
                        }
                    }
                } else {
                    // Tidak ada findings, cek status DB
                    if ($isClosed) {
                        $audit->status_label = 'Selesai (Diterima Auditor)';
                        $audit->status_type = 'accepted';
                    } elseif (in_array($statusLower, ['ongoing', 'proses', 'process'])) {
                        $audit->status_label = 'Proses';
                        $audit->status_type = 'ongoing';
                    } else {
                        $audit->status_label = 'Open';
                        $audit->status_type = 'open';
                    }
                }
            }
            return $audit;
        });

        // Widget Temuan
        $findingsDeadlineExceeded = AuditFinding::where('completion_reason', 'deadline_exceeded')
            ->where('status_temuan', 'closed')
            ->whereIn('audit_id', $visibleAuditIds)
            ->with('audit.auditee')
            ->orderBy('deadline', 'desc')
            ->take(5)
            ->get();

        $findingsResponded = AuditFinding::where('status_temuan', 'responded')
            ->whereDate('deadline', '<', $today)
            ->whereIn('audit_id', $visibleAuditIds)
            ->with('audit.auditee')
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // ===== GRAFIK DATA PER STANDAR =====
        $auditsByStandard = $getQuery()
            ->with('standard')
            ->get()
            ->groupBy(function ($audit) {
                return $audit->standard?->nama ?? 'Unknown';
            })
            ->map(function ($group) {
                return $group->count();
            });

        $standardLabels = array_keys($auditsByStandard->toArray());
        $standardCounts = array_values($auditsByStandard->toArray());

        // ===== GRAFIK DATA PER UNIT =====
        $auditsByUnit = $getQuery()
            ->with('auditee')
            ->get()
            ->groupBy(function ($audit) {
                return $audit->auditee?->unit_kerja ?? 'Unknown';
            })
            ->map(function ($group) {
                return $group->count();
            });

        $unitLabels = array_keys($auditsByUnit->toArray());
        $unitCounts = array_values($auditsByUnit->toArray());

        // ===== DETAIL PER STANDAR (Status breakdown) =====
        // Group by kode untuk menghindari duplikat
        $standardsByKode = AuditStandard::all()->groupBy('kode');

        $standardDetailsTemp = $standardsByKode->map(function ($standardsGroup, $kode) use ($getQuery, $today) {
            // Get the first standard (bisa internal atau external, keduanya sama)
            $standard = $standardsGroup->first();

            // Query audits untuk semua standards dengan kode yang sama
            $standardIds = $standardsGroup->pluck('id')->toArray();

            $auditsForStandard = $getQuery()
                ->whereIn('standard_id', $standardIds)
                ->with(['auditee', 'standard'])
                ->get();

            $total = $auditsForStandard->count();
            $finished = $auditsForStandard->filter(function ($audit) use ($today) {
                return in_array(strtolower($audit->status), ['finished', 'selesai (closed)', 'closed'])
                    || Carbon::parse($audit->deadline)->startOfDay()->lt($today);
            })->count();
            $ongoing = $auditsForStandard->filter(function ($audit) {
                return in_array(strtolower($audit->status), ['ongoing', 'proses', 'process']);
            })->count();
            $open = $total - $finished - $ongoing;

            // List of audits with unit kerja
            $auditsList = $auditsForStandard->map(function ($audit) {
                return [
                    'id' => $audit->id,
                    'unit_kerja' => $audit->auditee?->unit_kerja ?? $audit->auditee?->name ?? '-',
                    'jenis_audit' => optional($audit->standard)->jenis_audit ?? '-',
                    'status' => $audit->status,
                    'tanggal_audit' => \Carbon\Carbon::parse($audit->tanggal_audit)->format('d M Y'),
                    'deadline' => \Carbon\Carbon::parse($audit->deadline)->format('d M Y'),
                ];
            })->toArray();

            return [
                'nama' => $standard->nama,
                'kode' => $standard->kode ?? '',
                'total' => $total,
                'finished' => $finished,
                'ongoing' => $ongoing,
                'open' => $open,
                'audits' => $auditsList,
            ];
        })->filter(function ($item) {
            return $item['total'] > 0;
        });

        // Standardize values - no longer needed since we already group by kode
        $standardDetails = array_values($standardDetailsTemp->toArray());

        // ===== DETAIL PER UNIT (Status breakdown + Internal/External) =====
        $unitDetails = User::where('role', 'auditee')
            ->distinct('unit_kerja')
            ->pluck('unit_kerja')
            ->map(function ($unitKerja) use ($getQuery, $today) {
                $auditsForUnit = $getQuery()
                    ->whereHas('auditee', function ($q) use ($unitKerja) {
                        $q->where('unit_kerja', $unitKerja);
                    })
                    ->with('standard')
                    ->get();

                $total = $auditsForUnit->count();
                $finished = $auditsForUnit->filter(function ($audit) use ($today) {
                    return in_array(strtolower($audit->status), ['finished', 'selesai (closed)', 'closed'])
                        || Carbon::parse($audit->deadline)->startOfDay()->lt($today);
                })->count();
                $ongoing = $auditsForUnit->filter(function ($audit) {
                    return in_array(strtolower($audit->status), ['ongoing', 'proses', 'process']);
                })->count();
                $open = $total - $finished - $ongoing;

                // Breakdown by audit type (internal/eksternal)
                $internal = $auditsForUnit->filter(function ($audit) {
                    return optional($audit->standard)->jenis_audit === 'internal';
                })->count();
                $eksternal = $auditsForUnit->filter(function ($audit) {
                    return optional($audit->standard)->jenis_audit === 'eksternal';
                })->count();

                // List of audits for detail modal
                $auditsList = $auditsForUnit->map(function ($audit) {
                    return [
                        'id' => $audit->id,
                        'standard' => $audit->standard->kode ?? '-',
                        'standar_nama' => $audit->standard->nama ?? '-',
                        'jenis' => optional($audit->standard)->jenis_audit === 'internal' ? 'Internal' : 'Eksternal',
                        'status' => $audit->status,
                        'tanggal_audit' => \Carbon\Carbon::parse($audit->tanggal_audit)->format('d M Y'),
                        'deadline' => \Carbon\Carbon::parse($audit->deadline)->format('d M Y'),
                    ];
                })->toArray();

                return [
                    'nama' => $unitKerja,
                    'total' => $total,
                    'finished' => $finished,
                    'ongoing' => $ongoing,
                    'open' => $open,
                    'internal' => $internal,
                    'eksternal' => $eksternal,
                    'audits' => $auditsList,
                ];
            })
            ->filter(function ($item) {
                return $item['total'] > 0;
            });

        return view('home', compact(
            'totalAudit',
            'totalOpen',
            'totalProses',
            'totalSelesai',
            'upcomingDeadlines',
            'latestAudits',
            'findingsDeadlineExceeded',
            'findingsResponded',
            'standardLabels',
            'standardCounts',
            'standardDetails',
            'unitLabels',
            'unitCounts',
            'unitDetails'
        ));
    }
}