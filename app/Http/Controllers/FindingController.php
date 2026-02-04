<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditFinding;
use App\Models\Audit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FindingController extends Controller
{
    /**
     * Handle auditee response to a finding.
     */
    public function response(Request $request, AuditFinding $finding)
    {
        // CRITICAL: Check if files were actually sent BEFORE validation
        $hasFiles = $request->hasFile('bukti_perbaikan') && is_array($request->file('bukti_perbaikan'));

        if (!$hasFiles) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bukti_perbaikan' => 'Bukti perbaikan harus disertakan - silakan pilih minimal 1 file']);
        }

        // Validate input - include 3 textarea fields + bukti_perbaikan
        $validated = $request->validate([
            'akar_masalah' => 'required|string',
            'tindakan_koreksi' => 'required|string',
            'tindakan_korektif' => 'required|string',
            'bukti_perbaikan' => 'required|array|min:1|max:5',
            'bukti_perbaikan.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
        ], [
            'akar_masalah.required' => 'Penyebab Ketidaksesuaian harus diisi',
            'tindakan_koreksi.required' => 'Tindakan Koreksi harus diisi',
            'tindakan_korektif.required' => 'Tindakan Korektif harus diisi',
            'bukti_perbaikan.required' => 'Bukti perbaikan harus disertakan',
            'bukti_perbaikan.min' => 'Minimal 1 file bukti perbaikan harus disertakan',
            'bukti_perbaikan.max' => 'Maksimal 5 file bukti perbaikan',
            'bukti_perbaikan.*.required' => 'Semua file bukti perbaikan harus valid',
            'bukti_perbaikan.*.file' => 'Bukti perbaikan harus berupa file',
        ]);

        $updateData = [
            'akar_masalah' => $validated['akar_masalah'],
            'tindakan_koreksi' => $validated['tindakan_koreksi'],
            'tindakan_korektif' => $validated['tindakan_korektif'],
            'status_temuan' => 'responded',
            // Save as UTC (Laravel default), but captured in WIB time
            'submitted_at' => \Carbon\Carbon::now('Asia/Jakarta')->setTimezone('UTC'),
        ];

        // Handle file uploads
        $files = $validated['bukti_perbaikan'];
        $uploadedFiles = [];

        foreach ($files as $file) {
            try {
                // Double-check file is valid before processing
                if (!$file || !($file instanceof \Illuminate\Http\UploadedFile)) {
                    continue;
                }

                // Skip if file is not valid or has no size
                if (!$file->isValid() || $file->getSize() === 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['bukti_perbaikan' => 'File tidak valid atau kosong: ' . $file->getClientOriginalName()]);
                }

                // Skip if extension cannot be determined
                if (!$file->getClientOriginalExtension()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['bukti_perbaikan' => 'File tidak memiliki ekstensi valid: ' . $file->getClientOriginalName()]);
                }

                // Ensure finding ID is valid
                if (empty($finding->id)) {
                    \Log::error("Finding ID is empty in response method");
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['bukti_perbaikan' => 'ID temuan tidak valid']);
                }

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $uniqueName = md5($finding->id . time() . rand(1000, 9999)) . '.' . $extension;

                // CAPTURE FILE SIZE BEFORE MOVE (temp file might not be accessible later)
                $fileSize = $file->getSize();

                // Build directory path - use storage_path for direct file system access
                $storageDir = storage_path('app/public/perbaikan/' . $finding->id);

                \Log::info("Attempting to store file", [
                    'file' => $originalName,
                    'storageDir' => $storageDir,
                    'finding_id' => $finding->id,
                    'size' => $fileSize,
                    'uniqueName' => $uniqueName
                ]);

                // Create directory if it doesn't exist
                if (!is_dir($storageDir)) {
                    @mkdir($storageDir, 0755, true);
                }

                // Move file directly to storage
                $fullPath = $storageDir . DIRECTORY_SEPARATOR . $uniqueName;

                if (!$file->move($storageDir, $uniqueName)) {
                    \Log::error("Failed to move file", [
                        'file' => $originalName,
                        'storageDir' => $storageDir,
                        'uniqueName' => $uniqueName
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['bukti_perbaikan' => 'Gagal menyimpan file: ' . $originalName]);
                }

                // Verify file was actually saved
                if (!file_exists($fullPath)) {
                    \Log::error("File was moved but does not exist", ['path' => $fullPath]);
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['bukti_perbaikan' => 'File tidak tersimpan dengan benar']);
                }

                // Store relative path for database (perbaikan/finding_id/filename)
                $relativePath = 'perbaikan/' . $finding->id . '/' . $uniqueName;

                $uploadedFiles[] = [
                    'path' => $relativePath,
                    'name' => $originalName,
                    'size' => $fileSize,
                    'uploaded_at' => now(),
                ];
            } catch (\Throwable $e) {
                \Log::error("File upload error in response: " . $e->getMessage(), [
                    'exception_class' => get_class($e),
                    'file' => $file->getClientOriginalName() ?? 'unknown',
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['bukti_perbaikan' => 'Gagal upload file: ' . $e->getMessage()]);
            }
        }

        if (empty($uploadedFiles)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bukti_perbaikan' => 'Tidak ada file yang berhasil diunggah']);
        }

        $updateData['bukti_perbaikan'] = $uploadedFiles;
        $finding->update($updateData);

        return redirect()->route('audit.show', $finding->audit_id)
            ->with('success', 'Tanggapan untuk temuan berhasil dikirim.');
    }

    /**
     * Handle auditor verification (approve/reject) of a finding.
     */
    public function verify(Request $request, AuditFinding $finding)
    {
        if (auth()->user()->role !== 'auditor') {
            return redirect()->back()->with('error', 'Hanya auditor yang dapat melakukan verifikasi.');
        }

        $request->validate([
            'aksi' => 'required|in:approve,reject',
            'catatan_auditor' => 'required_if:aksi,reject|string',
        ]);

        if ($request->aksi == 'approve') {
            // Update finding status to closed
            $finding->update([
                'status_temuan' => 'closed',
                'completion_reason' => 'accepted_by_auditor',
                'catatan_auditor' => null,
            ]);

            $auditId = $finding->audit_id;
            $audit = Audit::find($auditId);

            // Count semua findings dan yang sudah closed untuk audit ini
            $closedCount = \DB::table('audit_findings')
                ->where('audit_id', $auditId)
                ->where('status_temuan', 'closed')
                ->count();

            $totalCount = \DB::table('audit_findings')
                ->where('audit_id', $auditId)
                ->count();

            \Log::info("APPROVE: Audit={$auditId}, Total Findings={$totalCount}, Closed={$closedCount}");

            // Jika SEMUA findings sudah closed, langsung close audit
            if ($totalCount > 0 && $closedCount === $totalCount) {
                \Log::info("APPROVE: Semua findings closed! Auto-closing audit {$auditId}");

                $audit->update(['status' => 'finished']);

                \Log::info("APPROVE: Audit {$auditId} status updated to 'finished'");
                return redirect()->back()->with('success', '✅ Temuan berhasil ditutup! Semua temuan selesai - Audit otomatis SELESAI.');
            }

            \Log::info("APPROVE: Masih ada findings terbuka ({$closedCount}/{$totalCount})");
            return redirect()->back()->with('success', 'Temuan berhasil ditutup (CLOSED).');
        }

        if ($request->aksi == 'reject') {
            $finding->update([
                'status_temuan' => 'open', // Kembalikan ke open agar auditee bisa revisi
                'catatan_auditor' => $request->catatan_auditor,
            ]);
            return redirect()->back()->with('success', 'Temuan ditolak dan dikembalikan ke Auditee untuk revisi.');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Reopen a closed finding (Auditor only).
     */
    public function reopen(AuditFinding $finding)
    {
        if (auth()->user()->role !== 'auditor') {
            return redirect()->back()->with('error', 'Hanya auditor yang dapat membuka kembali temuan.');
        }

        if ($finding->status_temuan !== 'closed') {
            return redirect()->back()->with('error', 'Hanya temuan yang sudah ditutup yang dapat dibuka kembali.');
        }

        $finding->update([
            'status_temuan' => 'open',
            'completion_reason' => null,
        ]);

        return redirect()->back()->with('success', 'Temuan berhasil dibuka kembali. Auditee dapat memberikan respons baru.');
    }

    /**
     * Update an existing finding (likely by auditor).
     */
    public function update(Request $request, AuditFinding $finding)
    {
        if (auth()->user()->role !== 'auditor') {
            return redirect()->back()->with('error', 'Hanya auditor yang dapat mengubah temuan.');
        }

        $validated = $request->validate([
            'kategori' => 'required|string|in:major,minor,observasi',
            'klausul' => 'required|string|max:255',
            'std_referensi' => 'nullable|string|max:255',
            'auditor_nama' => 'required|string|max:255',
            
            'uraian_temuan' => 'required|string',
            'akar_masalah' => 'nullable|string',
            'tindakan_koreksi' => 'nullable|string',
            'tindakan_korektif' => 'nullable|string',
            'deadline' => 'nullable|date',
            'lokasi' => 'nullable|string|max:255',
            'bukti_perbaikan' => 'nullable|array|max:5',
            'bukti_perbaikan.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $updateData = [
            'kategori' => $validated['kategori'],
            'klausul' => $validated['klausul'],
            'std_referensi' => $validated['std_referensi'],
            'auditor_nama' => $validated['auditor_nama'],
            'uraian_temuan' => $validated['uraian_temuan'],
            'akar_masalah' => $validated['akar_masalah'],
            'tindakan_koreksi' => $validated['tindakan_koreksi'],
            'tindakan_korektif' => $validated['tindakan_korektif'],
            'deadline' => $validated['deadline'],
            'lokasi' => $validated['lokasi'],
        ];

        // Handle file uploads if provided
        if ($request->hasFile('bukti_perbaikan')) {
            $files = $request->file('bukti_perbaikan');
            $uploadedFiles = [];

            // Ensure it's an array
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                try {
                    // Skip empty/null files
                    if (!$file) {
                        \Log::warning("File is null or empty in update method");
                        continue;
                    }

                    // Check if it's an actual UploadedFile instance
                    if (!($file instanceof \Illuminate\Http\UploadedFile)) {
                        \Log::warning("File is not an UploadedFile instance", ['type' => gettype($file)]);
                        continue;
                    }

                    // Skip if file is not valid
                    if (!$file->isValid()) {
                        \Log::warning("File is not valid", ['name' => $file->getClientOriginalName()]);
                        continue;
                    }

                    // Skip if file has no size
                    if ($file->getSize() === 0) {
                        \Log::warning("File is empty (0 bytes)", ['name' => $file->getClientOriginalName()]);
                        continue;
                    }

                    // Skip if extension cannot be determined
                    $extension = $file->getClientOriginalExtension();
                    if (empty($extension)) {
                        \Log::warning("File has no extension", ['name' => $file->getClientOriginalName()]);
                        continue;
                    }

                    // Verify finding ID is valid
                    if (empty($finding->id)) {
                        \Log::error("Finding ID is empty");
                        continue;
                    }

                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();  // CAPTURE SIZE BEFORE MOVE
                    $uniqueName = md5($finding->id . time() . rand(1000, 9999)) . '.' . $extension;
                    $storageDir = storage_path('app/public/perbaikan/' . $finding->id);

                    \Log::info("Storing file in update", [
                        'file' => $originalName,
                        'storageDir' => $storageDir,
                        'size' => $fileSize
                    ]);

                    // Create directory if doesn't exist
                    if (!is_dir($storageDir)) {
                        @mkdir($storageDir, 0755, true);
                    }

                    // Move file directly
                    if (!$file->move($storageDir, $uniqueName)) {
                        \Log::error("Failed to move file in update", [
                            'file' => $originalName,
                            'storageDir' => $storageDir
                        ]);
                        continue;
                    }

                    // Verify file exists
                    $fullPath = $storageDir . DIRECTORY_SEPARATOR . $uniqueName;
                    if (!file_exists($fullPath)) {
                        \Log::error("File moved but not found", ['path' => $fullPath]);
                        continue;
                    }

                    $relativePath = 'perbaikan/' . $finding->id . '/' . $uniqueName;

                    $uploadedFiles[] = [
                        'path' => $relativePath,
                        'name' => $originalName,
                        'size' => $fileSize,
                        'uploaded_at' => now(),
                    ];
                } catch (\Throwable $e) {
                    \Log::error("File upload error in update: " . $e->getMessage(), [
                        'exception' => $e,
                        'file' => $file->getClientOriginalName() ?? 'unknown',
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue;
                }
            }

            if (!empty($uploadedFiles)) {
                $updateData['bukti_perbaikan'] = $uploadedFiles;
            }
        }

        $finding->update($updateData);

        return redirect()->back()->with('success', 'Temuan berhasil diperbarui.');
    }

    /**
     * Delete an existing finding (likely by auditor).
     */
    public function destroy(AuditFinding $finding)
    {
        if (auth()->user()->role !== 'auditor') {
            return redirect()->back()->with('error', 'Hanya auditor yang dapat menghapus temuan.');
        }

        try {
            // Hapus file-file terkait dari storage
            if (is_array($finding->bukti_perbaikan)) {
                foreach ($finding->bukti_perbaikan as $bukti) {
                    $pathToDelete = null;
                    if (is_array($bukti) && isset($bukti['path'])) {
                        $pathToDelete = $bukti['path'];
                    } elseif (is_string($bukti)) {
                        $pathToDelete = $bukti;
                    }

                    if ($pathToDelete) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToDelete);
                    }
                }
            }

            // Hapus record dari database
            $finding->delete();

            return redirect()->back()->with('success', 'Temuan berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Gagal menghapus temuan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus temuan.');
        }
    }

    /**
     * Display a stored evidence file.
     */
    public function showEvidence($path)
    {
        try {
            // Security check 1: Path must not contain '..'
            if (str_contains($path, '..')) {
                \Log::warning('Invalid path pattern detected', ['path' => $path]);
                abort(403, 'Invalid path pattern.');
            }

            // Security check 2: Path must be within the 'perbaikan' folder.
            if (strpos(trim($path), 'perbaikan/') !== 0) {
                \Log::warning('Access denied - path outside allowed directory', ['path' => $path]);
                abort(403, 'Access denied. Path is outside of allowed directory.');
            }

            // Normalize slashes for the current operating system
            $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $fullStoragePath = storage_path('app/public/' . $normalizedPath);

            \Log::info('Attempting to access evidence file', [
                'original_path' => $path,
                'normalized_path' => $normalizedPath,
                'full_path' => $fullStoragePath,
                'exists' => file_exists($fullStoragePath)
            ]);

            if (!file_exists($fullStoragePath)) {
                \Log::error('Evidence file not found', ['path' => $fullStoragePath]);
                abort(404, 'File not found.');
            }

            // Verify it's actually a file, not a directory
            if (!is_file($fullStoragePath)) {
                \Log::error('Path is not a file', ['path' => $fullStoragePath]);
                abort(403, 'Invalid file path.');
            }

            // Check file is readable
            if (!is_readable($fullStoragePath)) {
                \Log::error('File is not readable', ['path' => $fullStoragePath]);
                abort(403, 'File is not readable.');
            }

            return response()->file($fullStoragePath);
        } catch (\Exception $e) {
            \Log::error('Error in showEvidence: ' . $e->getMessage(), [
                'exception' => $e,
                'path' => $path
            ]);
            abort(500, 'Error accessing file: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific evidence file for a finding.
     */
    public function destroyEvidence(AuditFinding $finding, $index)
    {
        // Authorization: ensure the logged-in user is the auditee for this finding
        if (auth()->id() !== $finding->audit->auditee_id) {
            return redirect()->back()->with('error', 'Anda tidak berwenang menghapus bukti ini.');
        }

        $bukti_perbaikan = $finding->bukti_perbaikan ?? [];

        if (isset($bukti_perbaikan[$index])) {
            $fileToDelete = $bukti_perbaikan[$index];
            $pathToDelete = null;

            if (is_array($fileToDelete) && isset($fileToDelete['path'])) {
                $pathToDelete = $fileToDelete['path'];
            } elseif (is_string($fileToDelete)) {
                $pathToDelete = $fileToDelete;
            }

            if ($pathToDelete) {
                // Delete file from storage
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToDelete);
            }

            // Remove from array and re-index
            unset($bukti_perbaikan[$index]);
            $finding->bukti_perbaikan = array_values($bukti_perbaikan);
            $finding->save();

            return redirect()->back()->with('success', 'Bukti berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus bukti.');
    }
}
