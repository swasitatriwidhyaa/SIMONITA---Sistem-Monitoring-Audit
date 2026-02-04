<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\RiwayatAuditController;
use App\Http\Controllers\AuditStandardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// MENGARAHKAN LANGSUNG KE LOGIN
Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Route evidence tetap di luar middleware auth jika memang membutuhkan akses tanda tangan digital
Route::get('/evidence/{path}', [FindingController::class, 'showEvidence'])
    ->name('finding.evidence')
    ->where('path', '.*')
    ->middleware('signed');

Route::middleware(['auth'])->group(function () {
    // ==========================================
    // 0. Audit Standards Management (Auditor only)
    // ==========================================
    Route::get('/audit/standards', [AuditStandardController::class, 'index'])->name('audit.standards.index');
    Route::get('/audit/standards/create', [AuditStandardController::class, 'create'])->name('audit.standards.create');
    Route::post('/audit/standards', [AuditStandardController::class, 'store'])->name('audit.standards.store');
    Route::get('/audit/standards/{standard}/edit', [AuditStandardController::class, 'edit'])->name('audit.standards.edit');
    Route::put('/audit/standards/{standard}', [AuditStandardController::class, 'update'])->name('audit.standards.update');
    Route::delete('/audit/standards/{standard}', [AuditStandardController::class, 'destroy'])->name('audit.standards.destroy');

    Route::get('/audit/{id}/export-pdf', [AuditController::class, 'exportPdf'])->name('audit.export.pdf');
    Route::get('/audit/{id}/export-excel', [AuditController::class, 'exportExcel'])->name('audit.export.excel');

    // ... (Sisa route Anda tetap sama sesuai yang Anda kirimkan)
    Route::get('/audit/create', [AuditController::class, 'create'])->name('audit.create');
    Route::post('/audit/store', [AuditController::class, 'store'])->name('audit.store');
    Route::get('/audit/{id}', [AuditController::class, 'show'])->name('audit.show');
    Route::post('/audit/{id}/findings', [AuditController::class, 'storeFinding'])->name('audit.finding.store');

    Route::get('/audit/request/new', [AuditController::class, 'requestForm'])->name('audit.request.form');
    Route::post('/audit/request/submit', [AuditController::class, 'submitRequest'])->name('audit.submit_request');
    Route::post('/audit/{id}/approve', [AuditController::class, 'approveAudit'])->name('audit.approve');

    Route::post('/finding/{finding}/response', [FindingController::class, 'response'])->name('finding.response');
    Route::post('/finding/{finding}/verify', [FindingController::class, 'verify'])->name('finding.verify');
    Route::post('/finding/{finding}/reopen', [FindingController::class, 'reopen'])->name('finding.reopen');

    Route::get('/riwayat-unit', [RiwayatAuditController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat-unit/{id}', [RiwayatAuditController::class, 'show'])->name('riwayat.show');
    Route::post('/riwayat-unit/auditee', [RiwayatAuditController::class, 'storeAuditee'])->name('riwayat.auditee.store');
    Route::delete('/riwayat-unit/auditee/{user}', [RiwayatAuditController::class, 'destroyAuditee'])->name('riwayat.auditee.destroy');
    Route::put('/riwayat-unit/auditee/{user}', [RiwayatAuditController::class, 'updateAuditee'])->name('riwayat.auditee.update');

    Route::put('/finding/{finding}/update', [FindingController::class, 'update'])->name('audit.finding.update');
    Route::delete('/finding/{finding}/destroy', [FindingController::class, 'destroy'])->name('audit.finding.destroy');
    Route::delete('/finding/{finding}/evidence/{index}', [FindingController::class, 'destroyEvidence'])->name('finding.evidence.destroy');

    Route::post('/audit/{id}/close', [AuditController::class, 'closeAudit'])->name('audit.close');
    Route::post('/audit/{id}/reopen', [AuditController::class, 'reopenAudit'])->name('audit.reopen');
    Route::put('/audit/{id}/update', [AuditController::class, 'update'])->name('audit.update');
    Route::delete('/audit/{id}/destroy', [AuditController::class, 'destroy'])->name('audit.destroy');
});
