@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('riwayat.index') }}" class="btn btn-outline-secondary">
                &larr; Kembali ke Daftar Unit
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Audit: <strong>{{ $unit->name ?? 'Unit' }}</strong></h5>
            </div>

            <div class="card-body">

                {{-- === FORM FILTER START === --}}
                <form action="{{ route('riwayat.show', $unit->id) }}" method="GET" class="mb-4 p-3 bg-light rounded border">
                    <div class="row g-2 align-items-end">

                        {{-- 1. Filter Jenis Audit (Internal/Eksternal) --}}
                        <div class="col-md-3">
                            <label for="jenis" class="form-label fw-bold small text-muted">Jenis Audit</label>
                            <select name="jenis" class="form-select">
                                <option value="">-- Semua Jenis --</option>
                                <option value="internal" {{ request('jenis') === 'internal' ? 'selected' : '' }}>Internal
                                </option>
                                <option value="eksternal" {{ request('jenis') === 'eksternal' ? 'selected' : '' }}>Eksternal
                                </option>
                            </select>
                        </div>

                        {{-- 2. Filter Standar --}}
                        <div class="col-md-3">
                            <label for="standard" class="form-label fw-bold small text-muted">Standar Audit</label>
                            <select name="standard" class="form-select">
                                <option value="">-- Semua Standar --</option>

                                @foreach ($standardsList as $std)
                                    <option value="{{ $std->id }}"
                                        {{ request('standard') == $std->id ? 'selected' : '' }}>
                                        {{ $std->kode }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- 3. Filter Bulan --}}
                        <div class="col-md-2">
                            <label for="month" class="form-label fw-bold small text-muted">Bulan</label>
                            <select name="month" class="form-select">
                                <option value="">-- Semua --</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMM') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Filter Tahun --}}
                        <div class="col-md-2">
                            <label for="year" class="form-label fw-bold small text-muted">Tahun</label>
                            <select name="year" class="form-select">
                                <option value="">-- Semua --</option>
                                @for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- 5. Tombol Filter --}}
                        <div class="col-md-2">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success text-white">
                                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                                </button>
                            </div>
                        </div>

                        {{-- Tombol Reset --}}
                        @if (request('jenis') || request('month') || request('year') || request('standard'))
                            <div class="col-12 mt-2 text-end">
                                <a href="{{ route('riwayat.show', $unit->id) }}"
                                    class="text-danger text-decoration-none small">
                                    <i class="bi bi-x-circle"></i> Reset semua filter
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
                {{-- === FORM FILTER END === --}}

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal Audit</th>
                                <th>Jenis Audit</th>
                                <th>Standar</th>
                                <th>Status</th>
                                <th>Lead Auditor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatAudits as $index => $audit)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if (!empty($audit->tanggal_audit))
                                            {{ \Carbon\Carbon::parse($audit->tanggal_audit)->locale('id')->isoFormat('D MMMM Y') }}
                                        @else
                                            {{ $audit->created_at->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $jenisAudit = $audit->standard->jenis_audit ?? null;
                                        @endphp
                                        @if ($jenisAudit === 'internal')
                                            <span class="badge bg-info bg-opacity-10 text-info">Internal</span>
                                        @elseif($jenisAudit === 'eksternal')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Eksternal</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Menampilkan Nama Standar di Tabel --}}
                                        <span class="fw-bold text-dark">
                                            {{ $audit->standard->kode ?? 'Standar #' . $audit->standard_id }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- LOGIKA STATUS DIPERBAIKI DISINI --}}
                                        @php
                                            $today = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
                                            $deadline = \Carbon\Carbon::parse($audit->deadline)->timezone('Asia/Jakarta')->startOfDay();
                                            $statusRaw = strtolower($audit->status);
                                            $isClosed = in_array($statusRaw, ['finished', 'selesai (closed)', 'closed']);
                                            
                                            // Cek apakah ada finding yang closed by system
                                            // Asumsi relation: $audit->findings sudah di-load atau lazy load
                                            $hasForcedFinding = $audit->findings->where('completion_reason', 'deadline_exceeded')->count() > 0;
                                            
                                            // Variabel Tampilan Default
                                            $badgeClass = 'bg-secondary';
                                            $statusText = 'Open';
                                            $statusIcon = 'bi-file-earmark';

                                            if ($isClosed) {
                                                // Jika DB closed, cek alasan (telat atau normal)
                                                // Jika tanggal sudah lewat atau ada tanda forced -> Merah
                                                if ($deadline->lessThan($today) || $hasForcedFinding) {
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Selesai (Lewat Deadline)';
                                                    $statusIcon = 'bi-x-circle';
                                                } else {
                                                    // Normal -> Hijau
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Selesai (Diterima)';
                                                    $statusIcon = 'bi-check-circle';
                                                }
                                            } elseif (in_array($statusRaw, ['ongoing', 'proses', 'process'])) {
                                                $badgeClass = 'bg-warning text-dark';
                                                $statusText = 'Proses';
                                                $statusIcon = 'bi-hourglass-split';
                                            }
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">
                                            <i class="bi {{ $statusIcon }}"></i> {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $audit->auditor->name ?? ($audit->user->name ?? 'Admin') }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('audit.show', $audit->id) }}"
                                                class="btn btn-sm btn-primary">Lihat
                                                Detail</a>
                                            @if (Auth::user()->role == 'auditor')
                                                <form action="{{ route('audit.destroy', $audit->id) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus audit ini beserta semua temuannya?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="bi bi-search" style="font-size: 2rem;"></i></div>
                                        <em>Tidak ada data audit yang ditemukan untuk filter ini.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection