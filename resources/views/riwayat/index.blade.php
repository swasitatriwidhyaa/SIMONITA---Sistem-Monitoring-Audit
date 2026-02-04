@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- HEADER & TOMBOL TAMBAH --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="bi bi-archive-fill me-2"></i> Arsip Riwayat Audit</h2>
        @if(Auth::user()->role == 'auditor')
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addKebunModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kebun
            </button>
        @endif
    </div>

    {{-- BARIS FILTER WILAYAH --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('riwayat.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill ps-3">
                            <i class="bi bi-filter text-muted"></i>
                        </span>
                        <select name="wilayah" class="form-select border-start-0 rounded-end-pill">
                            <option value="">-- Semua Wilayah --</option>
                            <option value="Kantor Regional" {{ request('wilayah') == 'Kantor Regional' ? 'selected' : '' }}>Kantor Regional</option>
                            <option value="Wilayah Lampung" {{ request('wilayah') == 'Wilayah Lampung' ? 'selected' : '' }}>Wilayah Lampung</option>
                            <option value="Wilayah Sumatera Selatan" {{ request('wilayah') == 'Wilayah Sumatera Selatan' ? 'selected' : '' }}>Wilayah Sumatera Selatan</option>
                            <option value="Wilayah Bengkulu" {{ request('wilayah') == 'Wilayah Bengkulu' ? 'selected' : '' }}>Wilayah Bengkulu</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('riwayat.index') }}" class="btn btn-outline-secondary rounded-pill w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- GRID UNIT KERJA --}}
    <div class="row">
        @forelse($units as $unit)
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <span class="badge bg-opacity-10 bg-success text-success rounded-pill px-3 py-2 small">
                        <i class="bi bi-geo-alt-fill me-1"></i> {{ $unit->wilayah ?? 'Wilayah Belum Diatur' }}
                    </span>
                </div>
                <div class="card-body d-flex flex-column px-4">
                    <h5 class="card-title fw-bold text-dark mt-2 mb-1">{{ $unit->unit_kerja ?? $unit->name }}</h5>
                    <p class="card-text text-muted small mb-4">
                        <i class="bi bi-envelope me-1"></i> {{ $unit->email }}
                    </p>
                    
                    <div class="mt-auto pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('riwayat.show', $unit->id) }}" class="btn btn-success btn-sm flex-grow-1 rounded-pill fw-bold">
                                Lihat Riwayat
                            </a>
                            @if(Auth::user()->role == 'auditor')
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editKebunModal{{ $unit->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('riwayat.auditee.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Hapus unit ini beserta seluruh riwayatnya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="alert alert-info rounded-4 border-0 shadow-sm py-5">
                    <i class="bi bi-info-circle fs-2 d-block mb-3"></i>
                    Belum ada data kebun yang sesuai dengan filter.
                </div>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $units->links() }}
    </div>
</div>

{{-- MODAL TAMBAH KEBUN --}}
@if(Auth::user()->role == 'auditor')
<div class="modal fade" id="addKebunModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-success text-white border-0 rounded-top-4 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Kebun Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('riwayat.auditee.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">NAMA KEBUN / UNIT</label>
                        <input type="text" class="form-control rounded-3" name="unit_kerja" placeholder="Contoh: Unit Bergen" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">WILAYAH / KANTOR</label>
                        <select name="wilayah" class="form-select rounded-3" required>
                            <option value="" disabled selected>-- Pilih Wilayah --</option>
                            <option value="Kantor Regional">Kantor Regional</option>
                            <option value="Wilayah Lampung">Wilayah Lampung</option>
                            <option value="Wilayah Sumatera Selatan">Wilayah Sumatera Selatan</option>
                            <option value="Wilayah Bengkulu">Wilayah Bengkulu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">EMAIL LOGIN</label>
                        <input type="email" class="form-control rounded-3" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PASSWORD AKSES</label>
                        <input type="password" class="form-control rounded-3" name="password" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT KEBUN --}}
@foreach($units as $unit)
<div class="modal fade" id="editKebunModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-warning border-0 rounded-top-4 py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Data Kebun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('riwayat.auditee.update', $unit->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">NAMA KEBUN / UNIT</label>
                        <input type="text" class="form-control rounded-3" name="unit_kerja" value="{{ $unit->unit_kerja }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">WILAYAH / KANTOR</label>
                        <select name="wilayah" class="form-select rounded-3" required>
                            <option value="Kantor Regional" {{ $unit->wilayah == 'Kantor Regional' ? 'selected' : '' }}>Kantor Regional</option>
                            <option value="Wilayah Lampung" {{ $unit->wilayah == 'Wilayah Lampung' ? 'selected' : '' }}>Wilayah Lampung</option>
                            <option value="Wilayah Sumatera Selatan" {{ $unit->wilayah == 'Wilayah Sumatera Selatan' ? 'selected' : '' }}>Wilayah Sumatera Selatan</option>
                            <option value="Wilayah Bengkulu" {{ $unit->wilayah == 'Wilayah Bengkulu' ? 'selected' : '' }}>Wilayah Bengkulu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">EMAIL</label>
                        <input type="email" class="form-control rounded-3" name="email" value="{{ $unit->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PASSWORD BARU (OPSIONAL)</label>
                        <input type="password" class="form-control rounded-3" name="password" placeholder="Kosongkan jika tidak diganti">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection