<?php

namespace App\Exports;

use App\Models\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AuditExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithCustomStartCell
{
    protected $audit;

    public function __construct($auditId)
    {
        $this->audit = Audit::with(['findings', 'auditee', 'standard'])->findOrFail($auditId);
    }

    public function collection()
    {
        return $this->audit->findings;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function map($finding): array
    {
        static $no = 1;
        return [
            $no++,                                          // (1) No
            $finding->score ?? '0',                         // (2) Score
            $this->audit->standard->kode,                  // (3) Prosedur
            $finding->klausul,                              // (4) Pertanyaan
            $finding->inisial_input ?? '-',                 // (5) Auditor
            $finding->lokasi,                               // (6) Lokasi
            $finding->uraian_temuan,                        // (7) Uraian
            $finding->akar_masalah ?? '-',                  // (8) Penyebab
            $finding->tindakan_koreksi ?? '-',               // (9) Koreksi
            $finding->tindakan_korektif ?? '-',              // (10) Korektif
            \Carbon\Carbon::parse($finding->deadline)->format('d/m/Y'), // (11) Deadline
            $finding->status_temuan == 'closed' ? 'Ada' : '-',          // (12) Evidence
            $finding->status_temuan == 'closed' ? 'CLOSE' : 'OPEN'      // (13) Verifikasi
        ];
    }

    public function headings(): array
    {
        return [
            ['No.', 'Score', 'Prosedur', 'Pertanyaan Audit', 'Auditor', 'Lokasi', 'Uraian Ketidaksesuaian', 'Penyebab Ketidaksesuaian', 'Tindakan Koreksi', 'Tindakan Korektif', 'Waktu Penyelesaian', 'Evidence', 'Hasil Verifikasi Auditor'],
            ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', '(11)', '(12)', '(13)']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 8, 'C' => 15, 'D' => 15, 'E' => 10, 'F' => 15, 
            'G' => 40, 'H' => 30, 'I' => 35, 'J' => 35, 'K' => 15, 'L' => 12, 'M' => 15
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', 'REKAPITULASI TINDAKAN KOREKSI DAN KOREKTIF (RTKK)');
        $sheet->setCellValue('A2', 'PTPN I - ' . strtoupper($this->audit->auditee->unit_kerja));
        $sheet->setCellValue('A3', 'Tanggal Audit : ' . \Carbon\Carbon::parse($this->audit->tanggal_audit)->isoFormat('D MMMM Y'));

        $sheet->mergeCells('A1:M1'); $sheet->mergeCells('A2:M2'); $sheet->mergeCells('A3:M3');
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
        
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:M7")->getFont()->setBold(true);
        $sheet->getStyle("A6:M$lastRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A6:M$lastRow")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("G8:J$lastRow")->getAlignment()->setWrapText(true);

        return [];
    }
}