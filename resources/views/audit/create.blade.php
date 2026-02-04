@extends('layouts.app')

@section('content')


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-calendar-plus me-2"></i>Buat Jadwal Audit Baru
                    </div>

                    <div class="card-body">
                        {{-- MENAMPILKAN ERROR VALIDASI --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('audit.store') }}" id="auditForm">
                            @csrf

                            {{-- 1. JENIS AUDIT (CUSTOM DROPDOWN) --}}
                            <div class="mb-3">
                                <label class="fw-bold mb-1">Pilih Jenis Audit</label>

                                {{-- Input Hidden --}}
                                <input type="hidden" name="standard_id" id="standardId" value="{{ old('standard_id') }}">

                                {{-- Trigger Dropdown --}}
                                <div class="position-relative">
                                    <div class="form-control d-flex justify-content-between align-items-center bg-white"
                                        style="cursor: pointer;" onclick="toggleCustomDropdown(event)">
                                        <span id="selectedDisplay"
                                            class="{{ old('standard_id') ? 'text-dark' : 'text-muted' }}">
                                            @if(old('standard_id'))
                                                Standar Terpilih (ID: {{ old('standard_id') }})
                                            @else
                                                -- Pilih Jenis Audit --
                                            @endif
                                        </span>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </div>

                                    {{-- Dropdown Menu Container --}}
                                    <div id="customDropdown"
                                        class="d-none position-absolute w-100 border rounded shadow-sm bg-white mt-1"
                                        style="z-index: 1050; max-height: 300px; overflow-y: auto;">

                                        {{-- Kategori: INTERNAL --}}
                                        <div class="border-bottom">
                                            <div class="p-2 d-flex justify-content-between align-items-center bg-light list-group-item-action"
                                                style="cursor: pointer;" onclick="toggleGroup(event, 'internal')">

                                                {{-- PERUBAHAN DISINI: text-success diganti text-dark --}}
                                                <span class="fw-bold text-dark">Internal</span>

                                                <i id="internal-icon" class="bi bi-chevron-right small"></i>
                                            </div>
                                            <div id="internal-group" class="d-none bg-white">
                                                @forelse($standards->where('jenis_audit', 'internal') as $standard)
                                                    <div class="p-2 ps-4 border-bottom list-group-item-action"
                                                        style="cursor: pointer;"
                                                        onclick="selectItem({{ $standard->id }}, '{{ $standard->kode }} - {{ $standard->nama ?? 'Standar' }}', event)">
                                                        {{ $standard->kode }} - {{ $standard->nama }}
                                                    </div>
                                                @empty
                                                    <div class="p-2 ps-4 text-muted fst-italic"><small>Tidak ada data</small>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Kategori: EKSTERNAL --}}
                                        <div>
                                            <div class="p-2 d-flex justify-content-between align-items-center bg-light list-group-item-action"
                                                style="cursor: pointer;" onclick="toggleGroup(event, 'eksternal')">

                                                {{-- PERUBAHAN DISINI: text-primary diganti text-dark --}}
                                                <span class="fw-bold text-dark">Eksternal</span>

                                                <i id="eksternal-icon" class="bi bi-chevron-right small"></i>
                                            </div>
                                            <div id="eksternal-group" class="d-none bg-white">
                                                @forelse($standards->where('jenis_audit', 'eksternal') as $standard)
                                                    <div class="p-2 ps-4 border-bottom list-group-item-action"
                                                        style="cursor: pointer;"
                                                        onclick="selectItem({{ $standard->id }}, '{{ $standard->kode }} - {{ $standard->nama ?? 'Standar' }}', event)">
                                                        {{ $standard->kode }} - {{ $standard->nama }}
                                                    </div>
                                                @empty
                                                    <div class="p-2 ps-4 text-muted fst-italic"><small>Tidak ada data</small>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- 2. PILIH AUDITEE (CUSTOM DROPDOWN) --}}
                            <div class="mb-3">
                                <label class="fw-bold mb-1">Pilih Auditee (Unit yang diperiksa)</label>

                                {{-- Input Hidden --}}
                                <input type="hidden" name="auditee_id" id="auditeeId" value="{{ old('auditee_id') }}">

                                {{-- Trigger Dropdown --}}
                                <div class="position-relative">
                                    <div class="form-control d-flex justify-content-between align-items-center bg-white"
                                        style="cursor: pointer;" onclick="toggleAuditeeDropdown(event)">
                                        <span id="selectedAuditeeDisplay"
                                            class="{{ old('auditee_id') ? 'text-dark' : 'text-muted' }}">
                                            @if(old('auditee_id'))
                                                Auditee Terpilih (ID: {{ old('auditee_id') }})
                                            @else
                                                -- Pilih Auditee --
                                            @endif
                                        </span>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </div>

                                    {{-- Dropdown Menu Container --}}
                                    <div id="auditeeDropdown"
                                        class="d-none position-absolute w-100 border rounded shadow-sm bg-white mt-1"
                                        style="z-index: 1050; max-height: 300px; overflow-y: auto;">

                                        {{-- Kantor Regional (Tanpa Collapse) --}}
                                        @if(!empty($auditeesGrouped['Kantor Regional']))
                                            @foreach($auditeesGrouped['Kantor Regional'] as $auditee)
                                                <div class="p-2 border-bottom list-group-item-action" style="cursor: pointer;"
                                                    onclick="selectAuditeeItem({{ $auditee->id }}, '{{ $auditee->unit_kerja }}', event)">
                                                    <strong>{{ $auditee->unit_kerja }}</strong>
                                                </div>
                                            @endforeach
                                        @endif

                                        {{-- Loop Wilayah Secara Dinamis --}}
@foreach($auditeesGrouped as $wilayah => $units)
    {{-- Skip Kantor Regional karena sudah ditampilkan di atas --}}
    @if($wilayah !== 'Kantor Regional')
        <div class="border-bottom">
            <div class="p-2 d-flex justify-content-between align-items-center bg-light list-group-item-action"
                style="cursor: pointer;"
                onclick="toggleAuditeeGroup(event, '{{ Str::slug($wilayah) }}')">
                <span class="fw-bold text-dark">{{ $wilayah }}</span>
                <i id="{{ Str::slug($wilayah) }}-icon" class="bi bi-chevron-right small"></i>
            </div>
            <div id="{{ Str::slug($wilayah) }}-group" class="d-none bg-white">
                @foreach($units as $auditee)
                    <div class="p-2 ps-4 border-bottom list-group-item-action"
                        style="cursor: pointer;"
                        onclick="selectAuditeeItem({{ $auditee->id }}, '{{ $auditee->unit_kerja }}', event)">
                        {{ $auditee->unit_kerja }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- 3. TANGGAL & DEADLINE --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Tanggal Audit Dimulai</label>
                                    <input type="date" name="tanggal_audit"
                                        class="form-control @error('tanggal_audit') is-invalid @enderror"
                                        value="{{ old('tanggal_audit') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold text-danger">Batas Waktu (Deadline)</label>
                                    <input type="date" name="deadline"
                                        class="form-control @error('deadline') is-invalid @enderror"
                                        value="{{ old('deadline') }}" required>
                                    <small class="text-muted">Batas akhir penyelesaian.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('home') }}" class="btn btn-secondary px-4">Batal</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Simpan Jadwal
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- DROPDOWN STANDAR ---
        // 1. Toggle Buka/Tutup Menu Utama
        function toggleCustomDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('customDropdown');
            dropdown.classList.toggle('d-none');
        }

        // 2. Toggle Buka/Tutup Kategori
        function toggleGroup(event, groupName) {
            event.stopPropagation();

            const groupContent = document.getElementById(groupName + '-group');
            const icon = document.getElementById(groupName + '-icon');

            groupContent.classList.toggle('d-none');

            // Ubah Icon Panah (Kanan / Bawah)
            if (groupContent.classList.contains('d-none')) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        }

        // 3. Saat Item Dipilih
        function selectItem(id, text, event) {
            event.stopPropagation();

            document.getElementById('standardId').value = id;

            const display = document.getElementById('selectedDisplay');
            display.textContent = text;
            display.classList.remove('text-muted');
            display.classList.add('text-dark', 'fw-bold');

            document.getElementById('customDropdown').classList.add('d-none');
        }

        // --- DROPDOWN AUDITEE ---
        function toggleAuditeeDropdown(event) {
            event.stopPropagation();
            document.getElementById('auditeeDropdown').classList.toggle('d-none');
        }

        function toggleAuditeeGroup(event, groupName) {
            event.stopPropagation();
            const groupContent = document.getElementById(groupName + '-group');
            const icon = document.getElementById(groupName + '-icon');
            groupContent.classList.toggle('d-none');
            if (groupContent.classList.contains('d-none')) {
                icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
            } else {
                icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
            }
        }

        function selectAuditeeItem(id, text, event) {
            event.stopPropagation();
            document.getElementById('auditeeId').value = id;
            const display = document.getElementById('selectedAuditeeDisplay');
            display.textContent = text;
            display.classList.remove('text-muted');
            display.classList.add('text-dark', 'fw-bold');
            document.getElementById('auditeeDropdown').classList.add('d-none');
        }


        // 4. Tutup dropdown jika klik di luar
        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('customDropdown');
            if (dropdown && !event.target.closest('.position-relative')) {
                dropdown.classList.add('d-none');
            }

            const auditeeDropdown = document.getElementById('auditeeDropdown');
            if (auditeeDropdown && !event.target.closest('.position-relative')) {
                auditeeDropdown.classList.add('d-none');
            }
        });
    </script>

    <style>
        .list-group-item-action:hover {
            background-color: #f0f8ff !important;
            transition: 0.2s;
        }
    </style>
@endsection