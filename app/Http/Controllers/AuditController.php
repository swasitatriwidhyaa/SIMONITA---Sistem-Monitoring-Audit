<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\AuditStandard;
use App\Models\AuditFinding;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditExport;

class AuditController extends Controller
{
    /**
     * 1. Menampilkan detail audit dan daftar temuannya
     */
    public function show($id)
    {
        // Mengambil data audit beserta relasi findings, auditee, dll
        $audit = Audit::with(['findings', 'standard', 'auditee', 'auditor'])->findOrFail($id);

        return view('audit.show', compact('audit'));
    }

    /**
     * Update audit fields - auditor only
     */
    public function update(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'auditor') {
            return back()->with('error', 'Aksi ditolak: hanya auditor yang dapat mengubah audit.');
        }

        $audit = Audit::findOrFail($id);

        $request->validate([
            'id_audit_plan' => 'nullable|string|max:255',
            'tanggal_audit' => 'required|date',
            'deadline' => 'required|date',
            'auditor_name' => 'required|string|max:255',

        ]);

        try {
            $auditDate = Carbon::parse($request->tanggal_audit)->startOfDay();
            $newDeadline = Carbon::parse($request->deadline)->startOfDay();

            if ($newDeadline->lt($auditDate)) {
                return back()->with('error', 'Deadline harus sama atau setelah tanggal audit.');
            }

            // Update audit data - auditor name is stored per audit
            $audit->update([
                'id_audit_plan' => $request->id_audit_plan,
                'tanggal_audit' => $auditDate->toDateString(),
                'deadline' => $newDeadline->toDateString(),
                'auditor_name' => $request->auditor_name,
                'inisial_input' => $request->inisial_input,
            ]);

            return back()->with('success', 'Data audit berhasil diperbarui.');
        } catch (\Throwable $e) {
            \Log::error('Failed to update audit: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal memperbarui data audit.');
        }
    }

    /**
     * 2. Fitur untuk menambah temuan baru (Bisa berkali-kali dalam 1 ID Audit)
     */
    public function storeFinding(Request $request, $id)
    {
        try {
            $audit = Audit::findOrFail($id);

            // Validasi input
            $validated = $request->validate([
                'klausul' => 'required|string|max:255',
                'kategori' => 'required|string|in:major,minor,observasi',
                'uraian_temuan' => 'required|string|min:10',
                'std_referensi' => 'nullable|string|max:255',
                'auditor_nama' => 'nullable|string|max:255',
                'inisial_input' => 'required|string|max:10',
                'lokasi' => 'required|string|max:255',
                'akar_masalah' => 'nullable|string',
                'tindakan_koreksi' => 'nullable|string',
                'tindakan_korektif' => 'nullable|string',
                'deadline' => 'required|date|after_or_equal:today',
            ], [
                'klausul.required' => 'Klausul harus diisi',
                'kategori.required' => 'Kategori temuan harus dipilih',
                'uraian_temuan.required' => 'Uraian ketidaksesuaian harus diisi minimal 10 karakter',
                'lokasi.required' => 'Lokasi harus diisi',
                'deadline.required' => 'Waktu penyelesaian harus diisi',
                'deadline.after_or_equal' => 'Deadline harus hari ini atau di masa depan',
                'deadline.date' => 'Format deadline tidak valid',
            ]);

            // Tentukan deadline
            $deadline = $validated['deadline'];

            // Jika audit punya deadline, pastikan deadline temuan tidak melampaui deadline audit
            if ($audit->deadline && $deadline) {
                $auditDeadline = \Carbon\Carbon::parse($audit->deadline);
                $findingDeadline = \Carbon\Carbon::parse($deadline);

                if ($findingDeadline->isAfter($auditDeadline)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Deadline temuan tidak boleh melampaui deadline audit (' . $auditDeadline->format('d M Y') . ')');
                }
            }

            // Buat temuan baru
            $finding = AuditFinding::create([
                'audit_id' => $id,
                'klausul' => $validated['klausul'],
                'kategori' => $validated['kategori'],
                'uraian_temuan' => $validated['uraian_temuan'],
                'std_referensi' => $validated['std_referensi'] ?? null,
                'auditor_nama' => $validated['auditor_nama'] ?? auth()->user()->name,
                'inisial_input' => $validated['inisial_input'],
                'auditor_id' => auth()->id(),
                'lokasi' => $validated['lokasi'],
                'akar_masalah' => $validated['akar_masalah'] ?? null,
                'tindakan_koreksi' => $validated['tindakan_koreksi'] ?? null,
                'tindakan_korektif' => $validated['tindakan_korektif'] ?? null,
                'deadline' => $deadline,
                'status_temuan' => 'open',
                'submitted_at' => now(),
            ]);

            // Update Status Audit jadi "Ongoing" jika sebelumnya planned
            if ($audit->status == 'planned') {
                $audit->update(['status' => 'ongoing']);
            }

            \Log::info('Finding created', [
                'audit_id' => $id,
                'finding_id' => $finding->id,
                'klausul' => $finding->klausul,
                'deadline' => $finding->deadline,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Temuan audit baru berhasil ditambahkan! Deadline: ' . ($deadline ? \Carbon\Carbon::parse($deadline)->format('d M Y') : 'Mengikuti audit'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error storing finding: ' . $e->getMessage(), [
                'exception' => $e,
                'audit_id' => $id,
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan temuan: ' . $e->getMessage());
        }
    }

    /**
     * 3. Menampilkan form pembuatan jadwal audit baru (oleh Auditor)
     */
    public function create()
    {
        $standards = AuditStandard::all();

        // Ambil semua auditee (role auditee)
        // Lalu kelompokkan secara otomatis berdasarkan kolom 'wilayah' di database
        $auditeesGrouped = User::where('role', 'auditee')
            ->get()
            ->groupBy('wilayah');

        // Mengurutkan grup agar 'Kantor Regional' selalu di atas jika ada
        $auditeesGrouped = $auditeesGrouped->sortBy(function ($units, $wilayah) {
            return $wilayah === 'Kantor Regional' ? 0 : 1;
        });

        return view('audit.create', compact('standards', 'auditeesGrouped'));
    }

    /**
     * 4. Menyimpan jadwal audit utama ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'standard_id' => 'required',
            'auditee_id' => 'required',
            'tanggal_audit' => 'required|date',
            'deadline' => 'required|date|after_or_equal:tanggal_audit',
        ]);

        Audit::create([
            'standard_id' => $request->standard_id,
            'auditor_id' => Auth::id(),
            'auditee_id' => $request->auditee_id,
            'tanggal_audit' => $request->tanggal_audit,
            'deadline' => $request->deadline,
            'status' => 'planned'
        ]);

        return redirect()->route('home')->with('success', 'Jadwal Audit Berhasil Dibuat!');
    }

    /**
     * 5. Fitur Request Audit oleh Auditee
     */
    public function requestForm()
    {
        $standards = AuditStandard::all();
        return view('audit.request', compact('standards'));
    }

    public function submitRequest(Request $request)
    {
        $request->validate([
            'standard_id' => 'required',
            'tanggal_audit' => 'required|date',
        ]);

        $auditor = User::where('role', 'auditor')->first();

        Audit::create([
            'standard_id' => $request->standard_id,
            'auditor_id' => $auditor->id,
            'auditee_id' => Auth::id(),
            'tanggal_audit' => $request->tanggal_audit,
            'deadline' => $request->tanggal_audit,
            'status' => 'requested'
        ]);

        return redirect()->route('home')->with('success', 'Pengajuan Audit dikirim!');
    }

    /**
     * 6. Approval Audit oleh Auditor
     */
    public function approveAudit($id)
    {
        $audit = Audit::findOrFail($id);
        $audit->update(['status' => 'planned']);
        return back()->with('success', 'Ajuan Audit Disetujui!');
    }

    /**
     * Close audit manually (only auditor)
     */
    public function closeAudit(Request $request, $id)
    {
        $audit = Audit::with('findings')->findOrFail($id);

        if (!auth()->check() || auth()->user()->role !== 'auditor') {
            return back()->with('error', 'Aksi ditolak: hanya auditor yang dapat menutup audit.');
        }

        try {
            // Close all findings and the audit
            $openCount = $audit->findings()->where('status_temuan', '!=', 'closed')->count();
            if ($openCount > 0) {
                \Log::info('Manual audit close requested; closing all findings', ['audit_id' => $audit->id, 'open_findings' => $openCount, 'by' => auth()->id()]);
            }

            // Set every finding to closed
            $audit->findings()->update(['status_temuan' => 'closed']);

            // mark audit as finished (localized label)
            $audit->update(['status' => 'finished']);
            return back()->with('success', 'Audit telah ditutup.');
        } catch (\Throwable $e) {
            \Log::error('Failed to close audit: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal menutup audit.');
        }
    }

    /**
     * Reopen an audit (auditor only) and set findings back to open.
     * NOTE: Only reopen findings that were auto-closed (deadline expired),
     * keep findings that were accepted by auditor in 'closed' state.
     */
    public function reopenAudit(Request $request, $id)
    {
        $audit = Audit::with('findings')->findOrFail($id);

        if (!auth()->check() || auth()->user()->role !== 'auditor') {
            return back()->with('error', 'Aksi ditolak: hanya auditor yang dapat membuka audit.');
        }

        try {
            // Only reopen findings that were auto-closed due to deadline expiration
            // NOT the ones that were accepted by auditor (completion_reason = 'accepted_by_auditor')
            $audit->findings()
                ->where('status_temuan', 'closed')
                ->where('completion_reason', '!=', 'accepted_by_auditor')
                ->update(['status_temuan' => 'open']);

            $audit->update(['status' => 'ongoing']);

            return back()->with('success', 'Audit dibuka kembali. Temuan yang sudah diterima tetap dalam status CLOSED.');
        } catch (\Throwable $e) {
            \Log::error('Failed to reopen audit: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal membuka audit.');
        }
    }

    /**
     * Destroy an audit and its findings (auditor only).
     */
    public function destroy(Request $request, $id)
    {
        $audit = Audit::with('findings')->findOrFail($id);

        if (!auth()->check() || auth()->user()->role !== 'auditor') {
            return back()->with('error', 'Aksi ditolak: hanya auditor yang dapat menghapus audit.');
        }

        try {
            // delete findings first to be explicit
            $audit->findings()->delete();

            // delete audit
            $audit->delete();

            return redirect()->route('home')->with('success', 'Audit berhasil dihapus.');
        } catch (\Throwable $e) {
            \Log::error('Failed to delete audit: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal menghapus audit.');
        }
    }
    public function exportExcel($id)
    {
        $audit = Audit::findOrFail($id);
        $fileName = 'RTKK_' . str_replace(' ', '_', $audit->auditee->unit_kerja) . '_' . date('Ymd') . '.xlsx';

        // Memanggil class Export (Pastikan Anda sudah menjalankan: php artisan make:export AuditExport)
        return Excel::download(new AuditExport($id), $fileName);
    }

    public function exportPdf($id)
    {
        $audit = Audit::with(['findings', 'auditee', 'standard'])->findOrFail($id);

        // Hitung ringkasan untuk tabel catatan di bawah
        $summary = [
            'major' => $audit->findings->where('kategori', 'major')->count(),
            'minor' => $audit->findings->where('kategori', 'minor')->count(),
            'observasi' => $audit->findings->where('kategori', 'observasi')->count(),
        ];

        $pdf = Pdf::loadView('audit.export_pdf', compact('audit', 'summary'))
            ->setPaper('a4', 'landscape'); // Format Landscape sangat penting

        return $pdf->download('RTKK_' . $audit->auditee->unit_kerja . '.pdf');
    }
}
