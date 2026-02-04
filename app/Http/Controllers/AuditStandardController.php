<?php

namespace App\Http\Controllers;

use App\Models\AuditStandard;
use Illuminate\Http\Request;

class AuditStandardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $standards = AuditStandard::orderBy('jenis_audit')->orderBy('kode')->get();

        return view('audit.standards.index', compact('standards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('audit.standards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:audit_standards,kode|max:50',
            'nama' => 'required|string|max:255',
            'jenis_audit' => 'required|in:internal,eksternal',
            'deskripsi' => 'nullable|string',
        ]);

        AuditStandard::create($validated);

        return redirect()->route('audit.standards.index')
            ->with('success', 'Standar audit berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditStandard $standard)
    {
        return view('audit.standards.edit', compact('standard'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditStandard $standard)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:audit_standards,kode,' . $standard->id . '|max:50',
            'nama' => 'required|string|max:255',
            'jenis_audit' => 'required|in:internal,eksternal',
            'deskripsi' => 'nullable|string',
        ]);

        $standard->update($validated);

        return redirect()->route('audit.standards.index')
            ->with('success', 'Standar audit berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditStandard $standard)
    {
        // Check if standard is used in any audits
        if ($standard->audits()->exists()) {
            return redirect()->route('audit.standards.index')
                ->with('error', 'Standar audit tidak dapat dihapus karena masih digunakan dalam jadwal audit.');
        }

        $standard->delete();

        return redirect()->route('audit.standards.index')
            ->with('success', 'Standar audit berhasil dihapus.');
    }
}
