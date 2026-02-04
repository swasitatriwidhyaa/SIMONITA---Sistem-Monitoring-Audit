<!DOCTYPE html>
<html>
<head>
    <title>RTKK Export - {{ $audit->auditee->unit_kerja }}</title>
    <style>
        @page { 
            margin: 0.5cm; 
            size: a4 landscape; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 7px; /* Diperkecil agar muat banyak kolom */
            margin: 0; 
            padding: 0; 
            line-height: 1.1; 
            color: #000; 
        }
        
        .header-title { text-align: center; margin-bottom: 8px; }
        .header-title h2 { margin: 0; font-size: 11px; text-decoration: underline; text-transform: uppercase; }
        .header-title h3 { margin: 1px 0; font-size: 9px; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 5px; border: none !important; }
        .info-table td { padding: 1px 0; border: none !important; font-size: 8px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 8px; }
        th, td { border: 1px solid black; padding: 2px; word-wrap: break-word; vertical-align: top; }
        
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; font-size: 7px; }
        .col-idx { background-color: #fafafa; text-align: center; font-style: italic; font-size: 6px; height: 12px; }

        .thick-border-left { border-left: 2px solid #000 !important; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* FOOTER SETTINGS */
        .footer-wrapper { width: 100%; border: none !important; margin-top: 10px; }
        .footer-wrapper td { border: none !important; vertical-align: top; }
        
        .keterangan-box { border: 1px solid black !important; padding: 5px; width: 220px; background: #fff; }
        .keterangan-box label { font-weight: bold; text-decoration: underline; display: block; margin-bottom: 3px; font-size: 7px; }
        
        .summary-table { width: 180px; margin-top: 5px; }
        .summary-table th { background: #eee; padding: 2px; }

        .sig-box { text-align: center; width: 200px; font-size: 9px; }
    </style>
</head>
<body>

    <div class="header-title">
        <h2>REKAPITULASI TINDAKAN KOREKSI DAN KOREKTIF (RTKK)</h2>
        <h3>PTPN I - {{ strtoupper($audit->auditee->unit_kerja) }}</h3>
    </div>

    <table class="info-table">
        <tr>
            <td width="10%">Standard</td><td width="1%">:</td><td width="49%" class="fw-bold">{{ $audit->standard->kode }}</td>
            <td width="15%">Tanggal Audit</td><td width="1%">:</td><td width="24%">{{ \Carbon\Carbon::parse($audit->tanggal_audit)->isoFormat('D MMMM Y') }}</td>
        </tr>
        <tr>
            <td>No. Audit Plan</td><td>:</td><td class="fw-bold">{{ $audit->id_audit_plan ?? '-' }}</td>
            <td>Lead Auditor</td><td>:</td><td>{{ $audit->auditor_name ?? $audit->auditor->name }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                {{-- Pengaturan Lebar Kolom dalam Persen (%) agar total pas 100% --}}
                <th style="width: 2.5%;">No.</th>
                <th style="width: 7.5%;">SOP/IK/FORM/ Standar</th>
                <th style="width: 8.5%;">NO. SOP/IK/FORM/ Klausul</th>
                <th style="width: 6%;">Kategori Temuan</th>
                <th style="width: 5%;">Pemeriksa</th>
                <th style="width: 6.5%;">Lokasi</th>
                <th style="width: 13%;">Uraian Ketidaksesuaian</th>
                <th style="width: 7.5%;">Waktu Penyelesaian</th>
                <th style="width: 11.5%;" class="thick-border-left">Penyebab Ketidaksesuaian</th>
                <th style="width: 11.5%;">Tindakan Koreksi</th>
                <th style="width: 11.5%;">Tindakan Korektif</th>
                <th style="width: 4%;">Evidence</th>
                <th style="width: 7.5%;">Hasil Verifikasi</th>
            </tr>
            <tr class="col-idx">
                @for($i=1; $i<=13; $i++) <td>({{ $i }})</td> @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($audit->findings->sortBy('id') as $index => $finding)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $finding->std_referensi ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $finding->klausul }}</td>
                <td class="text-center">{{ strtoupper($finding->kategori == 'observasi' ? 'Obs' : $finding->kategori) }}</td>
                <td class="text-center fw-bold">{{ $finding->inisial_input ?? '-' }}</td>
                <td class="text-center">{{ $finding->lokasi }}</td>
                <td>{{ $finding->uraian_temuan }}</td>
                <td class="text-center">{{ $finding->deadline ? \Carbon\Carbon::parse($finding->deadline)->format('d/m/Y') : '-' }}</td>
                <td class="thick-border-left">{{ $finding->akar_masalah ?? '-' }}</td>
                <td>{{ $finding->tindakan_koreksi ?? '-' }}</td>
                <td>{{ $finding->tindakan_korektif ?? '-' }}</td>
                <td class="text-center">{{ $finding->status_temuan == 'closed' ? 'Ada' : '-' }}</td>
                <td class="text-center fw-bold">
                    @if($finding->status_temuan == 'open') OPEN
                    @elseif($finding->status_temuan == 'responded') RESPONDED
                    @elseif($finding->status_temuan == 'closed') CLOSED
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-wrapper">
        <tr>
            <td width="55%">
                <div class="keterangan-box">
                    <label>Keterangan Inisial Auditor:</label>
                    <table style="border:none !important; width: 100%;">
                        @php
                            $uniqueAuditors = $audit->findings->whereNotNull('inisial_input')->groupBy('inisial_input');
                        @endphp
                        @foreach($uniqueAuditors as $inisial => $findings)
                            <tr>
                                <td style="border:none; width: 35px;" class="fw-bold">[{{ $inisial }}]</td>
                                <td style="border:none; width: 10px;">:</td>
                                <td style="border:none;">{{ $findings->first()->auditor_nama }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <table class="summary-table">
                    <thead>
                        <tr><th width="120">Kategori Temuan</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Mayor</td><td class="text-center">{{ $summary['major'] }}</td></tr>
                        <tr><td>Minor</td><td class="text-center">{{ $summary['minor'] }}</td></tr>
                        <tr><td>Observasi</td><td class="text-center">{{ $summary['observasi'] }}</td></tr>
                        <tr style="background:#eee;"><td class="fw-bold">TOTAL TEMUAN</td><td class="text-center fw-bold">{{ array_sum($summary) }}</td></tr>
                    </tbody>
                </table>
            </td>

            <td width="45%" align="right">
                <div style="margin-bottom: 15px; font-size: 9px;">
                    Bandar Lampung, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                </div>
                
                <table style="border:none !important; width: 100%;">
                    <tr>
                        <td class="sig-box">
                            <strong>Perwakilan Manajemen,</strong>
                            <br><br><br><br><br>
                            ( .................................................... )
                            <br>
                            <span style="font-size: 7px;">Manajer / Penanggungjawab Unit</span>
                        </td>
                        <td class="sig-box">
                            <strong>Tim Auditor,</strong>
                            <br><br><br><br><br>
                            ( <strong>{{ $audit->auditor_name ?? $audit->auditor->name }}</strong> )
                            <br>
                            <span style="font-size: 7px;">Lead Auditor</span>
                        </td>
                    </tr>
                </table>

                
            </td>
        </tr>
    </table>

</body>
</html>