@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Standar Audit Baru
                    </div>

                    <div class="card-body">
                        {{-- Error Validation --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('audit.standards.store') }}">
                            @csrf

                            {{-- Kode --}}
                            <div class="mb-3">
                                <label class="fw-bold">Kode Standar</label>
                                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                    placeholder="Contoh: ISO 9001, ISO 14001" value="{{ old('kode') }}" required>
                                @error('kode')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Nama Standar --}}
                            <div class="mb-3">
                                <label class="fw-bold">Nama Standar</label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                    placeholder="Contoh: Sistem Manajemen Mutu" value="{{ old('nama') }}" required>
                                @error('nama')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Jenis Audit --}}
                            <div class="mb-3">
                                <label class="fw-bold">Jenis Audit</label>
                                <select name="jenis_audit" class="form-select @error('jenis_audit') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Jenis Audit --</option>
                                    <option value="internal" {{ old('jenis_audit') == 'internal' ? 'selected' : '' }}>Internal
                                    </option>
                                    <option value="eksternal" {{ old('jenis_audit') == 'eksternal' ? 'selected' : '' }}>
                                        Eksternal</option>
                                </select>
                                @error('jenis_audit')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Deskripsi --}}
                            <div class="mb-3">
                                <label class="fw-bold">Deskripsi (Opsional)</label>
                                <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                    rows="4" placeholder="Jelaskan standar ini...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('audit.standards.index') }}" class="btn btn-secondary px-4">Batal</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Simpan Standar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection