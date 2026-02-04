@extends('layouts.app')

@section('content')
<style>
    /* 1. Global Page Style - Konsisten dengan Dashboard */
    .page-header { margin-bottom: 2rem; }
    
    /* 2. Premium Card Style */
    .card-modern { 
        border: none !important; 
        border-radius: 20px !important; 
        background: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02) !important;
    }

    /* 3. Enterprise Table Style */
    .table-modern thead th {
        background-color: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #475569;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    .table-modern tbody td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }

    /* 4. Modern Badges */
    .badge-soft {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.7rem;
    }
    .bg-soft-info { background: #e0f2fe; color: #0369a1; }
    .bg-soft-danger { background: #fee2e2; color: #b91c1c; }

    /* 5. Action Buttons Refinement */
    .btn-circle {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.2s;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .btn-circle:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .btn-edit:hover { color: #d97706; border-color: #fbbf24; background: #fffbeb; }
    .btn-delete:hover { color: #dc2626; border-color: #fca5a5; background: #fef2f2; }
</style>

<div class="container py-2">
    {{-- BARIS 1: HEADER & ACTION --}}
    <div class="row align-items-center page-header">
        <div class="col-md-7">
            <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -1px;">Kelola Standar Audit</h2>
            <p class="text-muted small mb-0">Manajemen daftar protokol dan regulasi audit sistem.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="{{ route('audit.standards.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-plus-circle me-2"></i>Tambah Standar Baru
            </a>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="small fw-bold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="small fw-bold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card card-modern overflow-hidden">
        <div class="card-header bg-white py-4 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">Daftar Regulasi Aktif</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Kode Referensi</th>
                        <th>Nama Standar Protokol</th>
                        <th>Kategori Audit</th>
                        <th>Deskripsi Singkat</th>
                        <th class="text-end pe-4">Aksi Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $standard)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $standard->kode }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $standard->nama }}</div>
                            </td>
                            <td>
                                @if($standard->jenis_audit == 'internal')
                                    <span class="badge-soft bg-soft-info text-uppercase">
                                        <i class="bi bi-shield-check me-1"></i>Internal
                                    </span>
                                @else
                                    <span class="badge-soft bg-soft-danger text-uppercase">
                                        <i class="bi bi-globe me-1"></i>Eksternal
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small italic">
                                    {{ $standard->deskripsi ? Str::limit($standard->deskripsi, 50) : 'Tidak ada deskripsi' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('audit.standards.edit', $standard->id) }}" 
                                       class="btn-circle btn-edit text-muted" title="Edit Standar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('audit.standards.destroy', $standard->id) }}" method="POST" 
                                          id="delete-form-{{ $standard->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-circle btn-delete text-muted" 
                                                onclick="confirmDelete('{{ $standard->id }}')" title="Hapus Standar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-3">
                                    <i class="bi bi-journal-x display-1 text-light"></i>
                                    <p class="text-muted mt-3">Belum ada standar audit yang terdaftar.</p>
                                    <a href="{{ route('audit.standards.create') }}" class="btn btn-sm btn-outline-success rounded-pill px-4">Buat Sekarang</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Script Konfirmasi SweetAlert --}}
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Standar Audit?',
        text: "Standar yang dihapus tidak dapat dipulihkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>
@endsection