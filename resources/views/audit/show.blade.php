@extends('layouts.app')

@section('content')

    <style>
        /* Styling Laporan Grid */
        .report-header {
            border: 2px solid #000;
            margin-bottom: 20px;
            background: #fff;
        }

        .report-header td {
            border: 1px solid #000;
            padding: 5px 10px;
            vertical-align: top;
        }

        .report-header label {
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            display: block;
            margin-bottom: 2px;
        }

        .report-header span {
            font-weight: bold;
            font-size: 1rem;
            color: #000;
            display: block;
        }

        /* Styling Tabel */
        .table-audit {
            border: 2px solid #000;
            font-size: 0.85rem;
            min-width: 1400px;
            width: 100%;
        }

        .table-audit thead th {
            vertical-align: middle;
            text-align: center;
            border: 1px solid #000;
            background-color: #e9ecef;
            text-transform: uppercase;
            font-weight: bold;
            white-space: nowrap;
            min-width: auto;
        }

        .table-audit tbody td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 8px;
            white-space: normal;
            word-wrap: break-word;
        }

        /* PERUBAHAN 1: CSS untuk garis pemisah tebal */
        .thick-border-left {
            border-left: 3px solid #000 !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .bg-input-auditee {
            background-color: #f0fdf4;
        }

        /* Form Respon Auditee Styling */
        .bg-input-auditee textarea {
            min-height: 60px;
            resize: vertical;
        }

        .bg-input-auditee .form-control-sm {
            font-size: 0.875rem;
        }

        .bg-input-auditee .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                shadow: none !important;
            }

            .table-audit th {
                background-color: #ccc !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <div class="container-fluid px-4 pb-5">

        {{-- SUCCESS/ERROR MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

{{-- HEADER & ACTIONS - Memastikan sejajar dengan Navbar --}}
<div class="d-flex justify-content-between align-items-center mb-4 mt-2 no-print">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-text text-success me-2"></i>Audit Non Conformity Report
        </h4>
        <p class="text-muted small mb-0">ID Plan: <span class="fw-bold text-primary">{{ $audit->id_audit_plan ?? '-' }}</span></p>
    </div>

    <div class="d-flex gap-2">
        {{-- BACK BUTTON --}}
        <a href="{{ route('home') }}" class="btn btn-light border btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>

        {{-- AUDITOR ONLY ACTIONS --}}
        @if(Auth::user()->role == 'auditor')
            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold" 
                    data-bs-toggle="modal" data-bs-target="#editDeadlineModal">
                <i class="bi bi-pencil-square me-1"></i> Edit Audit
            </button>
            
            {{-- DROPDOWN EXPORT - Jauh lebih profesional daripada window.print --}}
            <div class="dropdown">
                <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export Laporan
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 15px;">
                    <li>
                        <a class="dropdown-item py-2 small fw-bold" href="{{ route('audit.export.pdf', $audit->id) }}">
                            <i class="bi bi-file-pdf text-danger me-2"></i>Format PDF (FM-PTPN1-V03-005)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small fw-bold" href="{{ route('audit.export.excel', $audit->id) }}">
                            <i class="bi bi-file-excel text-success me-2"></i>Format Excel (.xlsx)
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button onclick="window.print()" class="dropdown-item py-2 small fw-bold">
                            <i class="bi bi-printer me-2"></i>Cetak Cepat (Browser)
                        </button>
                    </li>
                </ul>
            </div>
        @endif
    </div>
</div>

        {{-- INFO AUDIT --}}
        <div class="report-header shadow-sm">
            <table class="w-100 border-0">
                <tr>
                    <td width="25%">
                        <label>Organization / Unit</label>
                        <span
                            class="text-uppercase">{{ $audit->auditee->unit_kerja ?? $audit->auditee->name ?? '-' }}</span>
                    </td>
                    <td width="8%">
                        <label>Audit No.</label>
                        <span>{{ $audit->id_audit_plan ?? '-' }}</span>
                    </td>
                    <td width="28%">
                        <label>Standard (Main)</label>
                        <span>{{ $audit->standard->kode ?? 'ISO' }}</span>
                    </td>
                    <td width="20%">
                        <label>Audit Date</label>
                        <span>{{ \Carbon\Carbon::parse($audit->tanggal_audit)->format('d M Y') }}</span>
                    </td>
                    <td width="19%">
                        <label>Lead Auditor</label>
                        <span>{{ $audit->auditor_name ?? $audit->auditor->name ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <label>Deadline</label>
                        <span>{{ \Carbon\Carbon::parse($audit->deadline)->format('d M Y') }}</span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- TABEL TEMUAN --}}
        <div class="card shadow-lg border-0 mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-audit mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 40px;">No.</th>
                                <th style="min-width: 100px;">SOP/IK/FORM/Standar</th>
                                <th style="min-width: 120px;">NO. SOP/IK/FORM/Klausul</th>
                                <th style="min-width: 90px;">Kategori Temuan</th>
                                <th style="min-width: 100px;">Pemeriksa</th>
                                <th style="min-width: 80px;">Lokasi</th>
                                <th style="min-width: 140px;">Uraian Ketidaksesuaian</th>
                                <th style="min-width: 120px;">Waktu Penyelesaian</th>
                                {{-- PERUBAHAN 2: Tambah class thick-border-left di Header --}}
                                <th style="min-width: 140px;" class="thick-border-left">Penyebab Ketidaksesuaian</th>
                                <th style="min-width: 140px;">Tindakan Koreksi</th>
                                <th style="min-width: 120px;">Tindakan Korektif</th>
                                <th style="min-width: 100px;">Evidence</th>
                                <th style="min-width: 150px;">Hasil Verifikasi Pemeriksa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audit->findings->sortBy('id') as $index => $finding)
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="text-center small">{{ $finding->std_referensi ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $finding->klausul }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $finding->kategori == 'major' ? 'danger' : ($finding->kategori == 'minor' ? 'warning' : 'white text-dark border border-dark') }}">
                                            {{ $finding->kategori == 'observasi' ? 'Obs' : ucfirst($finding->kategori) }}
                                        </span>
                                    </td>
                                    {{-- <td class="text-center small">{{ $finding->auditor_nama ?? '-' }}</td> --}}
                                    <td class="text-center fw-bold text-primary" title="{{ $finding->auditor_nama }}">
    {{ $finding->inisial_input ?? '-' }}
</td>
                                    <td class="text-center small">{{ $finding->lokasi ?? '-' }}</td>
                                    <td class="small">
                                        {{ $finding->uraian_temuan }}
                                    </td>

                                    {{-- KOLOM: Waktu Penyelesaian --}}
                                    @if(Auth::user()->role == 'auditee' && $finding->status_temuan == 'open' && !in_array(strtolower($audit->status), ['finished', 'closed', 'selesai (closed)']))
                                        <td class="p-2 bg-input-auditee text-center small">
                                            @if($finding->deadline)
                                                <span
                                                    class="fw-bold">{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @else
                                        <td class="text-center small">
                                            @if($finding->deadline)
                                                {{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif
                                    {{-- LOGIKA TAMPILAN RESPON AUDITEE --}}
                                    @if(Auth::user()->role == 'auditee' && $finding->status_temuan == 'open' && !in_array(strtolower($audit->status), ['finished', 'closed', 'selesai (closed)']))
                                        {{-- Penyebab Ketidaksesuaian Form --}}
                                        <td class="p-2 bg-input-auditee">
                                            <textarea id="akar-masalah-{{ $finding->id }}"
                                                class="form-control form-control-sm akar-masalah-field" rows="3"
                                                placeholder="Jelaskan penyebab/akar masalah..."
                                                required>{{ old('akar_masalah', $finding->akar_masalah) }}</textarea>
                                            @error('akar_masalah')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        {{-- Tindakan Koreksi Form --}}
                                        <td class="p-2 bg-input-auditee">
                                            <textarea id="tindakan-koreksi-{{ $finding->id }}"
                                                class="form-control form-control-sm tindakan-koreksi-field" rows="3"
                                                placeholder="Jelaskan tindakan koreksi..."
                                                required>{{ old('tindakan_koreksi', $finding->tindakan_koreksi) }}</textarea>
                                            @error('tindakan_koreksi')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        {{-- Tindakan Korektif Form --}}
                                        <td class="p-2 bg-input-auditee">
                                            <textarea id="tindakan-korektif-{{ $finding->id }}"
                                                class="form-control form-control-sm tindakan-korektif-field" rows="3"
                                                placeholder="Jelaskan tindakan korektif..."
                                                required>{{ old('tindakan_korektif', $finding->tindakan_korektif) }}</textarea>
                                            @error('tindakan_korektif')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </td>

                                        {{-- Evidence Upload Form --}}
                                        <td class="p-2 bg-input-auditee small">
                                            @php
                                                $existingEvidence = $finding->bukti_perbaikan;
                                                if (is_string($existingEvidence))
                                                    $existingEvidence = [$existingEvidence];
                                                if (is_null($existingEvidence))
                                                    $existingEvidence = [];
                                                $totalExisting = count($existingEvidence);
                                                $remainingSlots = 5 - $totalExisting;
                                            @endphp

                                            {{-- Existing Evidence --}}
                                            @if($totalExisting > 0)
                                                <div class="mb-2" style="max-height: 100px; overflow-y: auto;">
                                                    <small class="text-muted d-block mb-1"><strong>Bukti Terdahulu:</strong></small>
                                                    @foreach($existingEvidence as $idx => $bukti)
                                                        @php
                                                            $filePath = is_array($bukti) && isset($bukti['path']) ? $bukti['path'] : $bukti;
                                                            $fileName = is_array($bukti) && isset($bukti['name']) ? $bukti['name'] : pathinfo($filePath, PATHINFO_BASENAME);
                                                        @endphp
                                                        <div class="d-flex justify-content-between align-items-center p-1 mb-1"
                                                            style="background-color: #e8f5e9; border-radius: 3px; font-size: 0.75rem;">
                                                            <a href="{{ URL::temporarySignedRoute('finding.evidence', now()->addMinutes(30), ['path' => $filePath]) }}"
                                                                target="_blank" class="text-truncate me-2" title="{{ $fileName }}">
                                                                <i class="bi bi-file-check"></i> {{ $fileName }}
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-danger btn-xs py-0 px-1 delete-evidence-btn"
                                                                data-delete-url="{{ route('finding.evidence.destroy', ['finding' => $finding->id, 'index' => $idx]) }}"
                                                                data-csrf-token="{{ csrf_token() }}" title="Hapus" style="min-width: 25px;">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-muted mb-2" style="font-size: 0.85rem;"><i
                                                        class="bi bi-info-circle"></i> Belum ada bukti</div>
                                            @endif

                                            {{-- Selected Files Preview --}}
                                            <div id="bukti-preview-{{ $finding->id }}" class="mb-2" style="display: none;">
                                                <small class="text-info d-block mb-1"><strong>File Baru yang
                                                        Dipilih:</strong></small>
                                                <div class="selected-files-{{ $finding->id }}"
                                                    style="max-height: 100px; overflow-y: auto;"></div>
                                            </div>

                                            {{-- File Input --}}
                                            <input type="file" id="bukti-file-{{ $finding->id }}" name="bukti_perbaikan[]"
                                                class="form-control form-control-sm bukti-input-{{ $finding->id }}" multiple
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                            <small class="text-muted d-block mt-1">Max: {{ $remainingSlots }}/5 file</small>
                                            <small class="text-info d-block mt-1"><i class="bi bi-info-circle"></i> Klik pilih file
                                                untuk menambah (bukan mengganti)</small>
                                            @error('bukti_perbaikan')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </td>
                                    @else
                                        {{-- VIEW MODE: Display only --}}
                                        {{-- Penyebab Ketidaksesuaian Display --}}
                                        <td class="small">
                                            {{ $finding->akar_masalah ?? '-' }}
                                        </td>

                                        {{-- Tindakan Koreksi Display --}}
                                        <td class="small">
                                            {{ $finding->tindakan_koreksi ?? '-' }}
                                        </td>

                                        {{-- Tindakan Korektif Display --}}
                                        <td class="small">
                                            {{ $finding->tindakan_korektif ?? '-' }}
                                        </td>

                                        {{-- Evidence Display --}}
                                        <td class="small">
                                            @if($finding->bukti_perbaikan)
                                                @php
                                                    $displayEvidence = $finding->bukti_perbaikan;
                                                    if (is_string($displayEvidence))
                                                        $displayEvidence = [$displayEvidence];
                                                @endphp
                                                <div>
                                                    @foreach($displayEvidence as $idx => $bukti)
                                                        @php
                                                            $filePath = is_array($bukti) && isset($bukti['path']) ? $bukti['path'] : $bukti;
                                                            $fileName = is_array($bukti) && isset($bukti['name']) ? $bukti['name'] : pathinfo($filePath, PATHINFO_BASENAME);
                                                            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                                                            // Determine file icon based on extension
                                                            $fileIcon = 'bi-file';
                                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                                                                $fileIcon = 'bi-image';
                                                            } elseif ($extension === 'pdf') {
                                                                $fileIcon = 'bi-file-pdf';
                                                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                                                $fileIcon = 'bi-file-word';
                                                            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                                $fileIcon = 'bi-file-spreadsheet';
                                                            }
                                                        @endphp
                                                        <div class="mb-2">
                                                            <a href="{{ URL::temporarySignedRoute('finding.evidence', now()->addMinutes(30), ['path' => $filePath]) }}"
                                                                target="_blank" class="text-decoration-none small"
                                                                title="Buka: {{ $fileName }}"
                                                                onclick="handleFileClick(event, '{{ $fileName }}')">
                                                                <i class="bi {{ $fileIcon }}"></i> {{ $fileName }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                    @if($finding->submitted_at)
                                                        <div class="text-muted"
                                                            style="font-size: 0.75rem; margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd;">
                                                            @php
                                                                // Ensure proper UTC to WIB conversion
                                                                $submittedTime = $finding->submitted_at;
                                                                if (is_string($submittedTime)) {
                                                                    $submittedTime = \Carbon\Carbon::parse($submittedTime, 'UTC');
                                                                } else {
                                                                    // If already Carbon, ensure it's treated as UTC first
                                                                    if (!$submittedTime->timezone) {
                                                                        $submittedTime = $submittedTime->setTimezone('UTC');
                                                                    }
                                                                }
                                                                $wibTime = $submittedTime->setTimezone('Asia/Jakarta');
                                                            @endphp
                                                            {{ $wibTime->format('d M Y H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- Kolom: Hasil Verifikasi Pemeriksa --}}
                                    <td class="text-center align-middle" style="min-width: 150px;">
                                        {{-- STATUS BADGE --}}
                                        @if($finding->status_temuan == 'open')
                                            <span class="badge bg-danger mb-2 d-block">
                                                <i class="bi bi-circle-fill"></i> OPEN
                                            </span>
                                        @elseif($finding->status_temuan == 'responded')
                                            <span class="badge bg-warning mb-2 d-block">
                                                <i class="bi bi-clock-history"></i> RESPONDED
                                            </span>
                                        @elseif($finding->status_temuan == 'closed')
                                            <span class="badge bg-success mb-2 d-block">
                                                <i class="bi bi-check-circle-fill"></i> CLOSED
                                            </span>
                                        @endif

                                        {{-- REJECTION MESSAGE FOR AUDITEE --}}
                                        @if(Auth::user()->role == 'auditee' && $finding->status_temuan == 'open' && $finding->catatan_auditor)
                                            <div class="alert alert-danger mb-2 py-2 px-2 small" style="font-size: 0.8rem;">
                                                <div class="fw-bold mb-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Ditolak Auditor
                                                </div>
                                                <div style="white-space: pre-wrap; word-wrap: break-word;">
                                                    {{ $finding->catatan_auditor }}</div>
                                            </div>
                                        @endif

                                        {{-- TOMBOL CRUD KHUSUS AUDITOR --}}
                                        @if(Auth::user()->role == 'auditor')
                                            <div class="d-flex justify-content-center gap-1 no-print flex-wrap">
                                                @if($finding->status_temuan !== 'closed')
                                                    <button type="button" class="btn btn-warning btn-sm py-1 px-2" title="Edit Temuan"
                                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $finding->id }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                @endif

                                                @if($finding->status_temuan !== 'closed')
                                                    <form action="{{ route('audit.finding.destroy', $finding->id) }}" method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus temuan ini?');" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm py-1 px-2"
                                                            title="Hapus Temuan">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- REOPEN BUTTON --}}
                                                @if($finding->status_temuan == 'closed')
                                                    <form action="{{ route('finding.reopen', $finding->id) }}" method="POST"
                                                        class="m-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-info btn-sm py-1 px-2"
                                                            title="Buka Kembali Temuan">
                                                            <i class="bi bi-arrow-clockwise"></i> Buka Lagi
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            @if($finding->status_temuan == 'responded')
                                                <div class="d-flex flex-column gap-2 mt-2 no-print" style="width: 100%;">
                                                    <form action="{{ route('finding.verify', $finding->id) }}" method="POST"
                                                        class="w-100 m-0">
                                                        @csrf
                                                        <input type="hidden" name="aksi" value="approve">
                                                        <button type="submit" class="btn btn-success btn-sm w-100 py-1">
                                                            <i class="bi bi-check-lg"></i> Terima
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-danger btn-sm w-100 py-1"
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $finding->id }}">
                                                        <i class="bi bi-x-lg"></i> Tolak
                                                    </button>
                                                </div>
                                            @endif

                                            {{-- Modal Reject --}}
                                            <div class="modal fade text-start no-print" id="rejectModal{{ $finding->id }}"
                                                tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Tolak Respon Temuan No. {{ $loop->iteration }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('finding.verify', $finding->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="aksi" value="reject">
                                                            <div class="modal-body">
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Catatan untuk Auditee
                                                                        (wajib)</label>
                                                                    <textarea name="catatan_auditor" class="form-control" rows="4"
                                                                        required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak & Kirim</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MODAL EDIT --}}
                                            <div class="modal fade text-start no-print" id="editModal{{ $finding->id }}"
                                                tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning">
                                                            <h5 class="modal-title fw-bold">Edit Temuan No. {{ $loop->iteration }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('audit.finding.update', $finding->id) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                                                                {{-- SOP/IK/FORM/Standar --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">SOP/IK/FORM/Standar</label>
                                                                    <input type="text" name="std_referensi"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $finding->std_referensi }}"
                                                                        placeholder="Masukkan standar/referensi">
                                                                </div>

                                                                {{-- NO. SOP/IK/FORM/Klausul --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">NO. SOP/IK/FORM/Klausul</label>
                                                                    <input type="text" name="klausul"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $finding->klausul }}" required
                                                                        placeholder="Masukkan nomor klausul">
                                                                </div>

                                                                {{-- Kategori Temuan --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Kategori Temuan</label>
                                                                    <select name="kategori" class="form-select form-select-sm"
                                                                        required>
                                                                        <option value="">-- Pilih Kategori --</option>
                                                                        <option value="observasi" {{ $finding->kategori == 'observasi' ? 'selected' : '' }}>Observasi</option>
                                                                        <option value="minor" {{ $finding->kategori == 'minor' ? 'selected' : '' }}>Minor</option>
                                                                        <option value="major" {{ $finding->kategori == 'major' ? 'selected' : '' }}>Major</option>
                                                                    </select>
                                                                </div>

                                                                {{-- Pemeriksa --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Pemeriksa</label>
                                                                    <input type="text" name="auditor_nama"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $finding->auditor_nama }}" required
                                                                        placeholder="Nama auditor/pemeriksa">
                                                                </div>

                                                                {{-- Lokasi --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Lokasi</label>
                                                                    <input type="text" name="lokasi"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $finding->lokasi }}" placeholder="Lokasi temuan">
                                                                </div>

                                                                {{-- Uraian Ketidaksesuaian --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Uraian Ketidaksesuaian</label>
                                                                    <textarea name="uraian_temuan"
                                                                        class="form-control form-control-sm" rows="3" required
                                                                        placeholder="Jelaskan uraian ketidaksesuaian...">{{ $finding->uraian_temuan }}</textarea>
                                                                </div>

                                                                {{-- Penyebab Ketidaksesuaian --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Penyebab Ketidaksesuaian</label>
                                                                    <textarea name="akar_masalah"
                                                                        class="form-control form-control-sm" rows="3"
                                                                        placeholder="Jelaskan penyebab/akar masalah...">{{ $finding->akar_masalah }}</textarea>
                                                                </div>

                                                                {{-- Tindakan Koreksi --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Tindakan Koreksi</label>
                                                                    <textarea name="tindakan_koreksi"
                                                                        class="form-control form-control-sm" rows="3"
                                                                        placeholder="Jelaskan tindakan koreksi...">{{ $finding->tindakan_koreksi }}</textarea>
                                                                </div>

                                                                {{-- Tindakan Korektif --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Tindakan Korektif</label>
                                                                    <textarea name="tindakan_korektif"
                                                                        class="form-control form-control-sm" rows="3"
                                                                        placeholder="Masukkan tindakan korektif yang disetujui...">{{ $finding->tindakan_korektif }}</textarea>
                                                                </div>

                                                                {{-- Waktu Penyelesaian --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Waktu Penyelesaian</label>
                                                                    <input type="date" name="deadline"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $finding->deadline }}"
                                                                        placeholder="Pilih tanggal deadline">
                                                                </div>

                                                                {{-- Upload Bukti --}}
                                                                <div class="mb-2">
                                                                    <label class="fw-bold small">Bukti Perbaikan (max 5 file,
                                                                        opsional)</label>
                                                                    <input type="file" name="bukti_perbaikan[]"
                                                                        class="form-control form-control-sm bukti-input-edit"
                                                                        multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
                                                                        onchange="handleMultipleFilesEdit(this, 'bukti-list-edit-{{ $finding->id }}', 'bukti-counter-edit-{{ $finding->id }}')">
                                                                    <small class="text-muted d-block mb-2">💡 Klik "Choose File"
                                                                        berkali-kali untuk menambah file (max 5)</small>
                                                                    <div class="bukti-counter-edit mt-1 small"
                                                                        id="bukti-counter-edit-{{ $finding->id }}">
                                                                        <i class="bi bi-info-circle"></i> <span
                                                                            class="bukti-count-edit">0</span> file akan diunggah
                                                                    </div>
                                                                    <div id="bukti-list-edit-{{ $finding->id }}" class="mt-2"></div>

                                                                    {{-- Menampilkan bukti existing di modal edit --}}
                                                                    @if($finding->bukti_perbaikan)
                                                                        <div class="mt-2 small">
                                                                            <strong>Bukti saat ini:</strong>
                                                                            <ul>
                                                                                @foreach(is_array($finding->bukti_perbaikan) ? $finding->bukti_perbaikan : [$finding->bukti_perbaikan] as $idx => $bukti)
                                                                                    <li>
                                                                                        @php $path = is_array($bukti) ? $bukti['path'] : $bukti; @endphp
                                                                                        <a href="{{ URL::temporarySignedRoute('finding.evidence', now()->addMinutes(30), ['path' => $path]) }}"
                                                                                            target="_blank">Bukti {{ $idx + 1 }}</a>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan
                                                                    Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif(Auth::user()->role == 'auditee' && $finding->status_temuan == 'open' && !in_array(strtolower($audit->status), ['finished', 'closed', 'selesai (closed)']))
                                            {{-- SUBMIT BUTTON FOR AUDITEE --}}
                                            <form id="finding-form-{{ $finding->id }}"
                                                action="{{ route('finding.response', $finding->id) }}" method="POST"
                                                enctype="multipart/form-data" class="m-0">
                                                @csrf
                                                <input type="hidden" name="akar_masalah" id="hidden-akar-masalah-{{ $finding->id }}"
                                                    value="">
                                                <input type="hidden" name="tindakan_koreksi"
                                                    id="hidden-tindakan-koreksi-{{ $finding->id }}" value="">
                                                <input type="hidden" name="tindakan_korektif"
                                                    id="hidden-tindakan-korektif-{{ $finding->id }}" value="">
                                                <div id="hidden-files-{{ $finding->id }}"></div>
                                                <button type="submit" class="btn btn-danger btn-sm w-100 py-2 fw-bold no-print"
                                                    onclick="return prepareFormSubmission({{ $finding->id }})">
                                                    <i class="bi bi-send"></i> Kirim Respon
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4 text-muted">Belum ada temuan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MODAL EVIDENCE PER FINDING --}}
        @foreach($audit->findings->sortBy('id') as $finding)
            @php
                $buktiData = $finding->bukti_perbaikan;
                if (is_string($buktiData)) {
                    $buktiData = [$buktiData];
                } elseif (is_null($buktiData)) {
                    $buktiData = [];
                }
            @endphp

            @if(count($buktiData) > 0)
                <div class="modal fade" id="evidenceModal{{ $finding->id }}" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Bukti: Temuan #{{ $loop->iteration }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                @foreach($buktiData as $buktiIndex => $bukti)
                                    <div style="display: {{ $buktiIndex === 0 ? 'block' : 'none' }}" class="bukti-container"
                                        data-bukti-id="{{ $buktiIndex }}">
                                        @php
                                            $path = is_array($bukti) && isset($bukti['path']) ? $bukti['path'] : $bukti;
                                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                            $fileUrl = URL::temporarySignedRoute('finding.evidence', now()->addMinutes(30), ['path' => $path]);
                                        @endphp

                                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                            <div class="text-center bg-dark">
                                                <img src="{{ $fileUrl }}" alt="Bukti" class="img-fluid" style="max-height:80vh;">
                                            </div>
                                        @elseif(in_array($ext, ['pdf']))
                                            <iframe src="{{ $fileUrl }}" frameborder="0" style="width:100%; height:80vh;"></iframe>
                                        @elseif(in_array($ext, ['doc', 'docx', 'xls', 'xlsx']))
                                            @php
                                                $officePreviewUrl = 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($fileUrl);
                                            @endphp
                                            <iframe src="{{ $officePreviewUrl }}" frameborder="0" style="width:100%; height:75vh;"></iframe>
                                            <div class="p-3 text-center bg-light border-top">
                                                <p class="small text-muted mb-2">Jika pratinjau tidak muncul, server Microsoft tidak dapat
                                                    mengakses file di lingkungan lokal Anda.<br>Silakan unduh file untuk membukanya.</p>
                                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-download"></i> Unduh / Buka File
                                                </a>
                                            </div>
                                        @else
                                            <div class="p-4 text-center">
                                                <p class="mb-2">Pratinjau tidak tersedia untuk tipe file ini.</p>
                                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary">Unduh / Buka</a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- NAVIGASI MODAL --}}
                                @if(count($buktiData) > 1)
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top">
                                        <button class="btn btn-sm btn-secondary prev-bukti"
                                            onclick="showPrevBukti({{ $finding->id }})"><i class="bi bi-chevron-left"></i>
                                            Sebelumnya</button>
                                        <span class="bukti-counter" data-current="1" data-total="{{ count($buktiData) }}">Bukti
                                            1 dari {{ count($buktiData) }}</span>
                                        <button class="btn btn-sm btn-secondary next-bukti"
                                            onclick="showNextBukti({{ $finding->id }}, {{ count($buktiData) }})">Berikutnya <i
                                                class="bi bi-chevron-right"></i></button>
                                    </div>
                                    <script>
                                        function showNextBukti(findingId, total) {
                                            const modal = document.querySelector(`#evidenceModal${findingId}`);
                                            const containers = modal.querySelectorAll('.bukti-container');
                                            const counter = modal.querySelector('.bukti-counter');
                                            let current = parseInt(counter.getAttribute('data-current'));

                                            if (current < total) {
                                                containers.forEach(c => c.style.display = 'none');
                                                containers[current].style.display = 'block';
                                                current++;
                                                counter.setAttribute('data-current', current);
                                                counter.textContent = `Bukti ${current} dari ${total}`;
                                            }
                                        }

                                        function showPrevBukti(findingId) {
                                            const modal = document.querySelector(`#evidenceModal${findingId}`);
                                            const containers = modal.querySelectorAll('.bukti-container');
                                            const counter = modal.querySelector('.bukti-counter');
                                            let current = parseInt(counter.getAttribute('data-current'));

                                            if (current > 1) {
                                                containers.forEach(c => c.style.display = 'none');
                                                current--;
                                                containers[current - 1].style.display = 'block';
                                                counter.setAttribute('data-current', current);
                                                counter.textContent = `Bukti ${current} dari ${containers.length}`;
                                            }
                                        }
                                    </script>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- FORM INPUT TEMUAN BARU --}}
        @if(Auth::user()->role == 'auditor' && !in_array(strtolower($audit->status), ['finished', 'closed', 'selesai (closed)']))
            <div class="card border-danger mt-4 shadow-sm no-print">
                <div class="card-header bg-danger text-white fw-bold"><i class="bi bi-plus-lg"></i> Input Temuan Baru</div>
                <div class="card-body bg-light">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle"></i> Kesalahan Validasi:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('audit.finding.store', $audit->id) }}" method="POST" id="formTemuanBaru" novalidate>
                        @csrf
                        <div class="row g-2">
                            {{-- SOP/IK/FORM/Standar --}}
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">SOP/IK/FORM/Standar</label>
                                <input type="text" name="std_referensi" class="form-control form-control-sm"
                                    placeholder="Standar" value="{{ old('std_referensi') }}">
                                @error('std_referensi')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- NO. SOP/IK/FORM/Klausul --}}
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">NO. SOP/IK/FORM/Klausul <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="klausul" class="form-control form-control-sm" placeholder="Klausul"
                                    required value="{{ old('klausul') }}">
                                @error('klausul')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Kategori Temuan --}}
                            <div class="col-md-1">
                                <label class="small fw-bold mb-1">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select form-select-sm" required>
                                    <option value="">--Pilih--</option>
                                    <option value="observasi" {{ old('kategori') == 'observasi' ? 'selected' : '' }}>Obs</option>
                                    <option value="minor" {{ old('kategori') == 'minor' ? 'selected' : '' }}>Minor</option>
                                    <option value="major" {{ old('kategori') == 'major' ? 'selected' : '' }}>Major</option>
                                </select>
                                @error('kategori')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Pemeriksa --}}
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">Pemeriksa <span class="text-danger">*</span></label>
                                <input type="text" name="auditor_nama" class="form-control form-control-sm"
                                    placeholder="Nama auditor" required value="{{ old('auditor_nama', Auth::user()->name) }}">
                                @error('auditor_nama')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Lokasi --}}
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" name="lokasi" class="form-control form-control-sm"
                                    placeholder="Lokasi temuan" value="{{ old('lokasi') }}" required>
                                @error('lokasi')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Waktu Penyelesaian --}}
                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">
                                    <i class="bi bi-calendar-check"></i> Waktu Penyelesaian <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="deadline"
                                    class="form-control form-control-sm @error('deadline') is-invalid @enderror"
                                    value="{{ old('deadline') }}" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                    title="Pilih tanggal deadline" required>
                                @if ($audit->deadline)
                                    <small class="text-muted d-block mt-1">Max:
                                        {{ \Carbon\Carbon::parse($audit->deadline)->format('d M Y') }}</small>
                                @endif
                                @error('deadline')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 2: Uraian Full Width --}}
                        <div class="row g-2 mt-2">
                            {{-- Uraian Ketidaksesuaian --}}
                            <div class="col-md-12">
                                <label class="small fw-bold mb-1">Uraian Ketidaksesuaian <span
                                        class="text-danger">*</span></label>
                                <textarea name="uraian_temuan"
                                    class="form-control form-control-sm @error('uraian_temuan') is-invalid @enderror" rows="3"
                                    placeholder="Jelaskan uraian ketidaksesuaian (minimal 10 karakter)..."
                                    required>{{ old('uraian_temuan') }}</textarea>
                                <small class="text-muted d-block mt-1"><span id="charCount">0</span>/500 karakter</small>
                                @error('uraian_temuan')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Button Submit --}}
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                <i class="bi bi-save"></i> Simpan Temuan
                            </button>
                            <button type="reset" class="btn btn-secondary btn-sm fw-bold">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('formTemuanBaru');
                    const uraianField = form.querySelector('textarea[name="uraian_temuan"]');
                    const charCount = document.getElementById('charCount');
                    const deadlineField = form.querySelector('input[name="deadline"]');
                    const auditDeadline = "{{ $audit->deadline ? \Carbon\Carbon::parse($audit->deadline)->format('Y-m-d') : '' }}";

                    // Update character count
                    if (uraianField) {
                        uraianField.addEventListener('input', function () {
                            charCount.textContent = this.value.length;
                        });
                        charCount.textContent = uraianField.value.length;
                    }

                    // Set max deadline
                    if (deadlineField && auditDeadline) {
                        deadlineField.setAttribute('max', auditDeadline);
                    }

                    // Form validation
                    form.addEventListener('submit', function (e) {
                        // Validate uraian length
                        if (uraianField.value.trim().length < 10) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'warning',
                                title: 'Uraian Terlalu Singkat',
                                text: 'Uraian ketidaksesuaian harus minimal 10 karakter',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }

                        // Validate deadline if provided
                        if (deadlineField.value && auditDeadline) {
                            if (deadlineField.value > auditDeadline) {
                                e.preventDefault();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Deadline Tidak Valid',
                                    text: 'Deadline temuan tidak boleh melebihi deadline audit (' + auditDeadline + ')',
                                    confirmButtonText: 'OK'
                                });
                                return false;
                            }
                        }
                    });
                });
            </script>
        @endif

        {{-- Jika audit sudah selesai --}}
        @if(Auth::user()->role == 'auditor' && in_array(strtolower($audit->status), ['finished', 'closed', 'selesai (closed)']))
            <div class="alert alert-info mt-4" role="alert">
                <i class="bi bi-info-circle"></i>
                Audit ini sudah {{ strtolower($audit->status) }}. Anda tidak bisa menambah temuan baru.
            </div>
        @endif
    </div>

    @if(Auth::user()->role == 'auditor')
        <div class="modal fade" id="editDeadlineModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Audit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('audit.update', $audit->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="fw-bold">Audit No.</label>
                                <input type="text" name="id_audit_plan" class="form-control"
                                    value="{{ $audit->id_audit_plan ?? '' }}" placeholder="Masukkan nomor audit" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Tanggal Audit</label>
                                <input type="date" name="tanggal_audit" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($audit->tanggal_audit)->format('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Lead Auditor</label>
                                <input type="text" name="auditor_name" class="form-control"
                                    value="{{ $audit->auditor_name ?? $audit->auditor->name ?? '' }}"
                                    placeholder="Nama lead auditor" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold text-danger">Deadline (Batas Waktu)</label>
                                <input type="date" name="deadline" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($audit->deadline)->format('Y-m-d') }}" required>
                                <small class="text-muted">Masukkan tanggal deadline yang benar (harus sama atau setelah tanggal
                                    audit).</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

{{-- JAVASCRIPTS --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Listener untuk tombol Hapus Bukti
        document.querySelectorAll('.delete-evidence-btn').forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation(); // Stop event bubbling

                const deleteUrl = this.dataset.deleteUrl;
                const csrfToken = this.dataset.csrfToken;

                deleteEvidence(this, deleteUrl, csrfToken);
            });
        });
    });

    // Fungsi untuk handle file click dengan error handling
    function handleFileClick(event, fileName) {
        // Allow the default behavior to proceed
        // The browser will open the file in a new tab
        console.log('Opening file:', fileName);
    }

    // Fungsi Hapus Bukti via Form Dinamis dengan SweetAlert2
    function deleteEvidence(button, deleteUrl, csrfToken) {
        Swal.fire({
            title: 'Hapus Bukti?',
            text: 'Anda yakin ingin menghapus bukti ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                button.disabled = true;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.style.display = 'none';

                // Input _method=DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);

                // Input _token (CSRF)
                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = csrfToken;
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Storage untuk selected files - TRULY PERSISTENT
    let selectedFilesAuditee = [];
    let selectedFilesEdit = {};

    // Handle multiple files untuk auditee response
    function handleMultipleFiles(input, listElementId, counterElementId, maxSlots = 5) {
        const newFiles = Array.from(input.files);

        if (newFiles.length === 0) {
            displayFileList(listElementId, [], 'auditee');
            return;
        }

        for (let file of newFiles) {
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    text: file.name + ' melebihi 10MB',
                    confirmButtonText: 'OK'
                });
                input.value = '';
                selectedFilesAuditee = [];
                displayFileList(listElementId, [], 'auditee');
                return;
            }
        }

        selectedFilesAuditee = [];
        for (let newFile of newFiles) {
            const isDuplicate = selectedFilesAuditee.some(f => f.name === newFile.name && f.size === newFile.size);
            if (!isDuplicate) {
                selectedFilesAuditee.push(newFile);
            }
        }

        if (selectedFilesAuditee.length > maxSlots) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Maksimal ' + maxSlots + ' file yang dapat diunggah. Anda memilih ' + selectedFilesAuditee.length + ' file.',
                confirmButtonText: 'OK'
            });
            selectedFilesAuditee = selectedFilesAuditee.slice(0, maxSlots);
        }

        const counterElement = document.querySelector(`#${counterElementId} .bukti-count`);
        if (counterElement) counterElement.textContent = selectedFilesAuditee.length;

        updateFileInput(input, selectedFilesAuditee);
        displayFileList(listElementId, selectedFilesAuditee, 'auditee');
    }

    function handleMultipleFilesEdit(input, listElementId, counterElementId) {
        const findingId = counterElementId.split('-').pop();
        const newFiles = Array.from(input.files);

        if (newFiles.length === 0) {
            displayFileList(listElementId, [], 'edit');
            return;
        }

        for (let file of newFiles) {
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    text: file.name + ' melebihi 10MB',
                    confirmButtonText: 'OK'
                });
                input.value = '';
                selectedFilesEdit[findingId] = [];
                displayFileList(listElementId, [], 'edit');
                return;
            }
        }

        if (!selectedFilesEdit[findingId]) {
            selectedFilesEdit[findingId] = [];
        } else {
            selectedFilesEdit[findingId] = [];
        }

        for (let newFile of newFiles) {
            const isDuplicate = selectedFilesEdit[findingId].some(f => f.name === newFile.name && f.size === newFile.size);
            if (!isDuplicate) {
                selectedFilesEdit[findingId].push(newFile);
            }
        }

        if (selectedFilesEdit[findingId].length > 5) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Maksimal 5 file yang dapat diunggah. Anda memilih ' + selectedFilesEdit[findingId].length + ' file.',
                confirmButtonText: 'OK'
            });
            selectedFilesEdit[findingId] = selectedFilesEdit[findingId].slice(0, 5);
        }

        const counterElement = document.querySelector(`#${counterElementId} .bukti-count-edit`);
        if (counterElement) counterElement.textContent = selectedFilesEdit[findingId].length;

        updateFileInput(input, selectedFilesEdit[findingId]);
        displayFileList(listElementId, selectedFilesEdit[findingId], 'edit');
    }

    function updateFileInput(input, filesArray) {
        const dt = new DataTransfer();

        // Filter out any empty/invalid files
        const validFiles = filesArray.filter(file => {
            return file && file.size > 0 && file.name && file.name.trim().length > 0;
        });

        validFiles.forEach(file => {
            dt.items.add(file);
        });

        input.files = dt.files;
    }

    function displayFileList(elementId, filesArray, type) {
        const listElement = document.querySelector(`#${elementId}`);
        listElement.innerHTML = '';

        if (filesArray.length === 0) {
            return;
        }

        const fileList = document.createElement('div');
        fileList.className = 'border rounded p-2 bg-light';

        const title = document.createElement('small');
        title.className = 'fw-bold d-block mb-2 text-secondary';
        title.textContent = '📋 File yang akan diunggah:';
        fileList.appendChild(title);

        filesArray.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'd-flex justify-content-between align-items-center py-1 border-bottom';
            fileItem.innerHTML = `
            <small>
                <i class="bi bi-file-earmark"></i> 
                ${file.name} 
                <span class="text-muted">(${(file.size / 1024).toFixed(0)} KB)</span>
            </small>
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFile(${index}, '${type}', '${elementId}')">
                <i class="bi bi-x-circle"></i>
            </button>
        `;
            fileList.appendChild(fileItem);
        });

        listElement.appendChild(fileList);
    }

    function removeFile(index, type, listElementId) {
        if (type === 'auditee') {
            selectedFilesAuditee.splice(index, 1);
            const input = document.querySelector('.bukti-input');
            updateFileInput(input, selectedFilesAuditee);
            const counter = document.querySelector('.bukti-count');
            if (counter) counter.textContent = selectedFilesAuditee.length;
            displayFileList(listElementId, selectedFilesAuditee, 'auditee');
        } else if (type === 'edit') {
            const findingId = listElementId.split('-').pop();
            if (selectedFilesEdit[findingId]) {
                selectedFilesEdit[findingId].splice(index, 1);
                const input = document.querySelector(`#editModal${findingId} .bukti-input-edit`);
                if (input) {
                    updateFileInput(input, selectedFilesEdit[findingId]);
                    const counter = document.querySelector(`#editModal${findingId} .bukti-count-edit`);
                    if (counter) counter.textContent = selectedFilesEdit[findingId].length;
                    displayFileList(listElementId, selectedFilesEdit[findingId], 'edit');
                }
            }
        }
    }

    document.addEventListener('hidden.bs.modal', function (e) {
        if (e.target.id.startsWith('editModal')) {
            const findingId = e.target.id.replace('editModal', '');
            selectedFilesEdit[findingId] = [];
            const input = e.target.querySelector('.bukti-input-edit');
            if (input) input.value = '';
            const listDisplay = document.querySelector(`#bukti-list-edit-${findingId}`);
            if (listDisplay) listDisplay.innerHTML = '';
            const counter = document.querySelector(`#bukti-counter-edit-${findingId} .bukti-count-edit`);
            if (counter) counter.textContent = '0';
        }
    });

    // Add form submission handlers
    document.addEventListener('DOMContentLoaded', function () {
        // Handle all form submissions to disable buttons
        const forms = document.querySelectorAll('form[method="POST"]');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                // Validate file inputs before submit
                const fileInputs = this.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    // Remove any empty files from input
                    const dt = new DataTransfer();
                    Array.from(input.files).forEach(file => {
                        if (file.size > 0 && file.name && file.name.trim().length > 0) {
                            dt.items.add(file);
                        }
                    });
                    input.files = dt.files;
                });

                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.classList.contains('no-disable')) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                }
            });
        });
    });

    // Auto-scroll to findings table when success message appears (after adding new finding)
    document.addEventListener('DOMContentLoaded', function () {
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            // Check if this is a success from adding a new finding
            const alertText = successAlert.textContent;
            if (alertText.includes('Temuan audit baru berhasil ditambahkan') || alertText.includes('berhasil')) {
                // Scroll to the findings table
                const tableCard = document.querySelector('.table-audit').closest('.card');
                if (tableCard) {
                    setTimeout(function () {
                        tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 500);
                }
            }
        }
    });

    // Handle file preview for auditee evidence upload
    document.addEventListener('DOMContentLoaded', function () {
        // Get all bukti file inputs
        const buktiInputs = document.querySelectorAll('[class*="bukti-input-"]');

        buktiInputs.forEach(input => {
            // Extract finding ID from class name (bukti-input-{id})
            const findingId = input.className.match(/bukti-input-(\d+)/)?.[1];
            if (!findingId) return;

            input.addEventListener('change', function () {
                accumulateBuktiFiles(findingId);
            });
        });
    });

    // Store accumulated files for each finding
    const accumulatedFiles = {};

    // Accumulate files (add to existing, not replace)
    function accumulateBuktiFiles(findingId) {
        const input = document.querySelector(`.bukti-input-${findingId}`);
        const preview = document.getElementById(`bukti-preview-${findingId}`);
        const selectedFiles = document.querySelector(`.selected-files-${findingId}`);

        if (!input || !preview || !selectedFiles) return;

        // Initialize accumulated files for this finding if not exists
        if (!accumulatedFiles[findingId]) {
            accumulatedFiles[findingId] = [];
        }

        // Get newly selected files and add to accumulated list
        const newFiles = Array.from(input.files);

        // Add new files to accumulated array (avoid duplicates)
        newFiles.forEach(newFile => {
            // Check if file already exists by name and size
            const exists = accumulatedFiles[findingId].some(f =>
                f.name === newFile.name && f.size === newFile.size
            );
            if (!exists) {
                accumulatedFiles[findingId].push(newFile);
            }
        });

        // Limit to 5 files max
        if (accumulatedFiles[findingId].length > 5) {
            accumulatedFiles[findingId] = accumulatedFiles[findingId].slice(0, 5);
            alert('Maksimal 5 file. File terakhir yang ditambahkan tidak disimpan.');
        }

        // Update preview
        updateBuktiPreview(findingId);

        // Clear the input for next selection
        input.value = '';
    }

    // Update bukti preview when files are selected
    function updateBuktiPreview(findingId) {
        const preview = document.getElementById(`bukti-preview-${findingId}`);
        const selectedFiles = document.querySelector(`.selected-files-${findingId}`);

        if (!preview || !selectedFiles) return;

        const files = accumulatedFiles[findingId] || [];

        if (files.length === 0) {
            preview.style.display = 'none';
            selectedFiles.innerHTML = '';
            return;
        }

        preview.style.display = 'block';
        selectedFiles.innerHTML = '';

        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'd-flex justify-content-between align-items-center p-2 mb-1';
            fileItem.style.cssText = 'background-color: #e3f2fd; border-radius: 3px; font-size: 0.85rem;';

            const fileInfo = document.createElement('div');
            fileInfo.className = 'd-flex align-items-center flex-grow-1';

            const icon = document.createElement('i');
            icon.className = 'bi bi-file me-2';
            icon.style.color = '#1976d2';

            const fileName = document.createElement('span');
            fileName.textContent = file.name;
            fileName.className = 'text-truncate';
            fileName.title = file.name;

            const fileSize = document.createElement('small');
            fileSize.className = 'text-muted ms-2';
            fileSize.textContent = `(${(file.size / 1024).toFixed(2)} KB)`;

            fileInfo.appendChild(icon);
            fileInfo.appendChild(fileName);
            fileInfo.appendChild(fileSize);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger ms-2';
            removeBtn.style.cssText = 'padding: 2px 8px; font-size: 0.75rem;';
            removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
            removeBtn.title = 'Hapus file ini';
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                removeFileFromSelection(findingId, index);
            });

            fileItem.appendChild(fileInfo);
            fileItem.appendChild(removeBtn);
            selectedFiles.appendChild(fileItem);
        });
    }

    // Remove file from selection
    function removeFileFromSelection(findingId, indexToRemove) {
        if (accumulatedFiles[findingId]) {
            accumulatedFiles[findingId].splice(indexToRemove, 1);
            updateBuktiPreview(findingId);
        }
    }

    // Prepare form for submission
    function prepareFormSubmission(findingId) {
        // Get values from textarea fields
        const arkarMasalah = document.getElementById(`akar-masalah-${findingId}`)?.value || '';
        const tindakanKoreksi = document.getElementById(`tindakan-koreksi-${findingId}`)?.value || '';
        const tindakanKorektif = document.getElementById(`tindakan-korektif-${findingId}`)?.value || '';

        // Validate required fields
        if (!arkarMasalah.trim()) {
            alert('Penyebab Ketidaksesuaian harus diisi');
            return false;
        }
        if (!tindakanKoreksi.trim()) {
            alert('Tindakan Koreksi harus diisi');
            return false;
        }
        if (!tindakanKorektif.trim()) {
            alert('Tindakan Korektif harus diisi');
            return false;
        }

        // Check if there are files or existing files
        const hasAccumulatedFiles = (accumulatedFiles[findingId] || []).length > 0;
        const hasExistingFiles = document.querySelector(`.selected-files-${findingId} a`) !== null;

        if (!hasAccumulatedFiles && !hasExistingFiles) {
            alert('Bukti perbaikan harus disertakan');
            return false;
        }

        // Set hidden field values
        document.getElementById(`hidden-akar-masalah-${findingId}`).value = arkarMasalah;
        document.getElementById(`hidden-tindakan-koreksi-${findingId}`).value = tindakanKoreksi;
        document.getElementById(`hidden-tindakan-korektif-${findingId}`).value = tindakanKorektif;

        // Add files to form
        const filesContainer = document.getElementById(`hidden-files-${findingId}`);
        filesContainer.innerHTML = '';

        const files = accumulatedFiles[findingId] || [];
        const form = document.getElementById(`finding-form-${findingId}`);

        // Create a new FormData to handle files properly
        if (files.length > 0) {
            // Remove old file inputs if any
            form.querySelectorAll('input[name="bukti_perbaikan[]"]').forEach(el => el.remove());

            // Add new file inputs
            files.forEach((file, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `bukti_file_${index}`;
                filesContainer.appendChild(input);
            });

            // Use FormData to submit files
            const formData = new FormData(form);

            // Add files
            files.forEach((file, index) => {
                formData.append('bukti_perbaikan[]', file);
            });

            // Submit using fetch
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Gagal mengirim respon');
                        console.error('Server error:', response);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                    console.error('Error:', error);
                });

            return false;
        }

        return true;
    }
</script>