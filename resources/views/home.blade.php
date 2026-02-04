@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- HEADER UTAMA --}}
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Dashboard Monitoring</h4>
                <p class="text-secondary mb-0">
                    Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong>!
                    <br><small class="text-muted">Pantau perkembangan audit unit terkini di sini.</small>
                </p>
            </div>
            <div class="d-none d-md-block">
                <div class="card border-0 bg-light px-3 py-2">
                    <span class="fw-bold text-success">
                        <i class="bi bi-calendar-check me-2"></i> {{ \Carbon\Carbon::now('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- KOLOM KIRI: GRAFIK --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <h6 class="fw-bold text-muted mb-4 text-start">PROGRES KESELURUHAN</h6>

                        @php
                            $pctSelesai = ($totalAudit > 0) ? ($totalSelesai / $totalAudit) * 100 : 0;
                            $pctProses  = ($totalAudit > 0) ? ($totalProses / $totalAudit) * 100 : 0;
                            $stackProses = $pctSelesai + $pctProses;
                        @endphp

                        <div class="position-relative d-inline-block mb-3" style="width: 200px; height: 200px;">
                            <svg width="100%" height="100%" viewBox="0 0 42 42" class="donut">
                                <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="#fff"></circle>
                                <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f8f9fa" stroke-width="4"></circle>

                                @if($totalAudit > 0)
                                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                        stroke="#6c757d" stroke-width="4" stroke-dasharray="100 0" stroke-dashoffset="25"></circle>
                                    @if($stackProses > 0)
                                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                        stroke="#ffc107" stroke-width="4"
                                        stroke-dasharray="{{ $stackProses }} {{ 100 - $stackProses }}" stroke-dashoffset="25"></circle>
                                    @endif
                                    @if($pctSelesai > 0)
                                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                        stroke="#198754" stroke-width="4"
                                        stroke-dasharray="{{ $pctSelesai }} {{ 100 - $pctSelesai }}" stroke-dashoffset="25"></circle>
                                    @endif
                                @else
                                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                        stroke="#e9ecef" stroke-width="4" stroke-dasharray="100 0" stroke-dashoffset="25"></circle>
                                @endif
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h1 class="display-4 fw-bold text-dark mb-0">{{ $totalAudit }}</h1>
                                <span class="badge bg-light text-secondary border">TOTAL AUDIT</span>
                            </div>
                        </div>

                        <div class="row text-center mt-2 small">
                            <div class="col-4">
                                <span class="d-block fw-bold text-success">{{ $totalSelesai }}</span>
                                <span class="text-muted" style="font-size: 11px;">Selesai</span>
                            </div>
                            <div class="col-4 border-start border-end">
                                <span class="d-block fw-bold text-warning">{{ $totalProses }}</span>
                                <span class="text-muted" style="font-size: 11px;">Proses</span>
                            </div>
                            <div class="col-4">
                                <span class="d-block fw-bold text-secondary">{{ $totalOpen }}</span>
                                <span class="text-muted" style="font-size: 11px;">Open</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-lg-8">
                {{-- STATISTIK BARIS 1 --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 bg-primary bg-gradient text-white">
                            <div class="card-body p-3 text-center">
                                <h2 class="fw-bold mb-0">{{ $totalAudit }}</h2>
                                <small class="text-white-50">Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 border-start border-5 border-secondary">
                            <div class="card-body p-3 text-center">
                                <h2 class="fw-bold text-secondary mb-0">{{ $totalOpen }}</h2>
                                <small class="text-muted">Open</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 border-start border-5 border-warning">
                            <div class="card-body p-3 text-center">
                                <h2 class="fw-bold text-warning mb-0">{{ $totalProses }}</h2>
                                <small class="text-muted">Proses</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 border-start border-5 border-success">
                            <div class="card-body p-3 text-center">
                                <h2 class="fw-bold text-success mb-0">{{ $totalSelesai }}</h2>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DEADLINE TERDEKAT --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-alarm text-danger me-2"></i> Perhatian Khusus (Deadline Terdekat)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($upcomingDeadlines as $item)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3 text-center" style="width: 50px;">
                                            <small class="d-block fw-bold">{{ \Carbon\Carbon::parse($item->deadline)->format('d') }}</small>
                                            <small class="d-block" style="font-size: 10px;">{{ \Carbon\Carbon::parse($item->deadline)->format('M') }}</small>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $item->auditee->name ?? 'Unit' }}</h6>
                                            <small class="text-muted">{{ $item->standard->kode ?? '-' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-light">
                                    <p class="text-muted mb-0 small">Semua jadwal audit aman (belum ada yang mendesak).</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK PER STANDAR DAN UNIT --}}
        <div class="row mt-4 mb-4">
            {{-- GRAFIK PER STANDAR - DONUT CHART LAYOUT --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart text-primary me-2"></i>Status Audit Per Standar</h6>
                    </div>
                    <div class="card-body">
                        @if(count($standardDetails) > 0)
                            <div class="row g-4">
                                @foreach($standardDetails as $index => $std)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-1 border-light h-100">
                                            <div class="card-body text-center p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-bold text-muted mb-0" style="font-size: 13px;">{{ $std['nama'] }}</h6>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#standardDetailsModal{{ $index }}" title="Lihat Detail">
                                                        <i class="bi bi-eye" style="font-size: 12px;"></i>
                                                    </button>
                                                </div>
                                                
                                                <div class="position-relative d-inline-block mb-3" style="width: 150px; height: 150px;">
                                                    <svg width="100%" height="100%" viewBox="0 0 42 42" class="donut">
                                                        <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="#fff"></circle>
                                                        <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f8f9fa" stroke-width="4"></circle>
                                                        
                                                        @php
                                                            $pctOpen = ($std['total'] > 0) ? ($std['open'] / $std['total']) * 100 : 0;
                                                            $pctOngoing = ($std['total'] > 0) ? ($std['ongoing'] / $std['total']) * 100 : 0;
                                                            $pctFinished = ($std['total'] > 0) ? ($std['finished'] / $std['total']) * 100 : 0;
                                                            
                                                            $stackOngoing = $pctOpen + $pctOngoing;
                                                        @endphp

                                                        @if($std['total'] > 0)
                                                            {{-- Open (Gray) --}}
                                                            @if($pctOpen > 0)
                                                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                                stroke="#6c757d" stroke-width="4"
                                                                stroke-dasharray="{{ $pctOpen }} {{ 100 - $pctOpen }}" stroke-dashoffset="25"></circle>
                                                            @endif
                                                            {{-- Ongoing (Yellow) --}}
                                                            @if($pctOngoing > 0)
                                                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                                stroke="#ffc107" stroke-width="4"
                                                                stroke-dasharray="{{ $pctOngoing }} {{ 100 - $pctOngoing }}" stroke-dashoffset="{{ (int)(25 - $pctOpen) }}"></circle>
                                                            @endif
                                                            {{-- Finished (Green) --}}
                                                            @if($pctFinished > 0)
                                                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                                stroke="#198754" stroke-width="4"
                                                                stroke-dasharray="{{ $pctFinished }} {{ 100 - $pctFinished }}" stroke-dashoffset="{{ (int)(25 - $stackOngoing) }}"></circle>
                                                            @endif
                                                        @else
                                                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                                stroke="#e9ecef" stroke-width="4" stroke-dasharray="100 0" stroke-dashoffset="25"></circle>
                                                        @endif
                                                    </svg>
                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                        <h5 class="fw-bold text-dark mb-0">{{ $std['total'] }}</h5>
                                                    </div>
                                                </div>

                                                <div class="row text-center small mt-2">
                                                    <div class="col-4">
                                                        <span class="d-block fw-bold text-secondary">{{ $std['open'] }}</span>
                                                        <span class="text-muted" style="font-size: 10px;">Open</span>
                                                    </div>
                                                    <div class="col-4 border-start border-end">
                                                        <span class="d-block fw-bold text-warning">{{ $std['ongoing'] }}</span>
                                                        <span class="text-muted" style="font-size: 10px;">Proses</span>
                                                    </div>
                                                    <div class="col-4">
                                                        <span class="d-block fw-bold text-success">{{ $std['finished'] }}</span>
                                                        <span class="text-muted" style="font-size: 10px;">Selesai</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mb-0">Belum ada data standar audit</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL AUDIT TERBARU --}}
        <div class="card shadow-sm border-0 mt-2">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Aktifitas Audit Terbaru</h6>
                </div>
                
                {{-- TOMBOL SHORTCUT + LIHAT SEMUA --}}
                <div class="d-flex gap-2">
                    @if(Auth::user()->role == 'auditor')
                        {{-- Tombol Shortcut Buat Jadwal --}}
                        <a href="{{ route('audit.create') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Buat Jadwal
                        </a>
                        <a href="{{ route('riwayat.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    @else
                        {{-- Jika Auditee, mungkin tombol ajukan audit --}}
                        <a href="{{ route('audit.request.form') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Ajukan Audit
                        </a>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Unit / Auditee</th>
                            <th>Tanggal Audit</th>
                            <th>Deadline</th>
                            <th>Jenis Audit</th>
                            <th>Standar</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestAudits as $audit)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block">{{ $audit->auditee->name ?? '-' }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($audit->tanggal_audit)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($audit->deadline)->format('d M Y') }}</td>
                                <td>
                                    @if(optional($audit->standard)->jenis_audit == 'internal')
                                        <span class="badge bg-info bg-opacity-10 text-info">Internal</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Eksternal</span>
                                    @endif
                                </td>
                                <td>{{ $audit->standard->kode ?? '-' }}</td>
                                <td>
                                    {{-- STATUS SESUAI REQUEST --}}
                                    @if($audit->status_type === 'accepted')
                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i> Selesai (Diterima Auditor)</span>
                                    @elseif($audit->status_type === 'deadline_exceeded')
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> {{ $audit->status_label }}</span>
                                    @elseif($audit->status_type === 'ongoing')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass"></i> Proses</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-file-earmark"></i> Open</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Tombol Detail --}}
                                        <a href="{{ route('audit.show', $audit->id) }}" class="btn btn-sm btn-light border" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Tombol Hapus (Hanya Auditor) --}}
                                        @if(Auth::user()->role == 'auditor')
                                            <form action="{{ route('audit.destroy', $audit->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus audit ini? Semua data terkait akan hilang permanen.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">Data kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- CHART.JS LIBRARY --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <script>
    </script>

    {{-- MODAL DETAIL UNTUK SETIAP STANDAR --}}
    @foreach($standardDetails as $idx => $standard)
    <div class="modal fade" id="standardDetailsModal{{ $idx }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>Detail {{ $standard['nama'] }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <h5 class="text-success mb-0">{{ $standard['finished'] }}</h5>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <h5 class="text-warning mb-0">{{ $standard['ongoing'] }}</h5>
                                <small class="text-muted">Proses</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="bg-secondary bg-opacity-10 rounded p-3">
                                <h5 class="text-secondary mb-0">{{ $standard['open'] }}</h5>
                                <small class="text-muted">Open</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <h5 class="text-primary mb-0">{{ $standard['total'] }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Daftar Audit (Unit Kerja)</h6>
                    @if(count($standard['audits']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unit Kerja</th>
                                        <th>Jenis Audit</th>
                                        <th>Status</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Deadline</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($standard['audits'] as $audit)
                                        <tr>
                                            <td class="small fw-bold">{{ $audit['unit_kerja'] }}</td>
                                            <td class="small">
                                                @if($audit['jenis_audit'] === 'internal')
                                                    <span class="badge bg-info bg-opacity-10 text-info">Internal</span>
                                                @elseif($audit['jenis_audit'] === 'eksternal')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Eksternal</span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @php
                                                    $statusLower = strtolower($audit['status']);
                                                    if (in_array($statusLower, ['finished', 'selesai (closed)', 'closed'])) {
                                                        echo '<span class="badge bg-success">Selesai</span>';
                                                    } elseif (in_array($statusLower, ['ongoing', 'proses', 'process'])) {
                                                        echo '<span class="badge bg-warning text-dark">Proses</span>';
                                                    } else {
                                                        echo '<span class="badge bg-secondary">Open</span>';
                                                    }
                                                @endphp
                                            </td>
                                            <td class="small">{{ $audit['tanggal_audit'] ?? '-' }}</td>
                                            <td class="small">{{ $audit['deadline'] }}</td>
                                            <td class="small">
                                                <a href="{{ route('audit.show', $audit['id']) }}" class="btn btn-sm btn-light border" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada audit untuk standar ini.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endsection