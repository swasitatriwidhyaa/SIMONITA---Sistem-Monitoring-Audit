<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Audit;
use App\Models\AuditStandard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RiwayatAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'auditee');

        // Filter Berdasarkan Wilayah
        if ($request->filled('wilayah')) {
            $query->where('wilayah', $request->wilayah);
        }

        // Paginasi 50 data per halaman
        // appends(request()->query()) agar saat pindah halaman filter tidak hilang
        $units = $query->orderBy('wilayah', 'asc')
            ->orderBy('unit_kerja', 'asc')
            ->paginate(50)
            ->withQueryString();

        return view('riwayat.index', compact('units'));
    }

    public function show(Request $request, $id)
    {
        // 1. Cari Unit
        $unit = User::findOrFail($id);

        // If current user is an auditee, ensure they can only view their own unit history
        if (auth()->check() && auth()->user()->role == 'auditee' && auth()->id() != $id) {
            return redirect()->route('home')->with('error', 'Akses ditolak: Anda hanya dapat melihat riwayat unit Anda sendiri.');
        }

        // 2. Ambil Daftar Standar dari Database
        $standardsList = AuditStandard::all();

        // Gunakan data `AuditStandard` dari database langsung
        // (Seeder sekarang menggabungkan ISO 9001 & ISO 14001 sebagai satu entri)

        // 3. Siapkan Query Dasar dan eager-load relasi standard & auditor
        $query = Audit::with(['standard', 'auditor'])->where('auditee_id', $id);

        // --- LOGIKA FILTER ---
        if ($request->filled('jenis')) {
            // Filter by jenis_audit dari tabel audit_standards
            $standardsByJenis = AuditStandard::where('jenis_audit', $request->jenis)->pluck('id');
            $query->whereIn('standard_id', $standardsByJenis);
        }
        if ($request->filled('standard')) {
            $query->where('standard_id', $request->standard);
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal_audit', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('tanggal_audit', $request->month);
        }

        // 4. Eksekusi Query
        $riwayatAudits = $query->orderBy('tanggal_audit', 'desc')->get();

        return view('riwayat.show', compact('unit', 'riwayatAudits', 'standardsList'));
    }

    public function storeAuditee(Request $request)
    {
        $request->validate([
            'unit_kerja' => 'required|string|unique:users,unit_kerja',
            'wilayah' => 'required', // Tambahkan ini
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'unit_kerja' => $request->unit_kerja,
            'wilayah' => $request->wilayah, // Simpan wilayahnya
            'name' => 'Manager ' . $request->unit_kerja,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'auditee',
        ]);

        return redirect()->back()->with('success', 'Unit berhasil terdaftar!');
    }
    public function destroyAuditee(User $user)
    {
        if ($user->role !== 'auditee') {
            return redirect()->route('riwayat.index')->with('error', 'Hanya auditee yang dapat dihapus dari halaman ini.');
        }

        // Prevent self-deletion if an auditee user could somehow access this
        if (auth()->check() && auth()->id() == $user->id) {
            return redirect()->route('riwayat.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('riwayat.index')->with('success', 'Kebun (Auditee) berhasil dihapus.');
    }

    public function updateAuditee(Request $request, User $user)
    {
        $request->validate([
            'unit_kerja' => ['required', 'string', 'max:255', Rule::unique('users', 'unit_kerja')->ignore($user->id)],
            'wilayah'    => 'required|string', // Tambahkan validasi wilayah
            'name'       => 'nullable|string|max:255',
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password'   => 'nullable|string|min:8',
        ]);

        $user->unit_kerja = $request->unit_kerja;
        $user->wilayah = $request->wilayah; // TAMBAHKAN INI agar wilayah tersimpan saat diedit
        $user->name = $request->name ?: 'Manager ' . $request->unit_kerja;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('riwayat.index')->with('success', 'Data Kebun berhasil diperbarui.');
    }
}
