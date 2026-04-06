<?php

namespace App\Services;

use App\Models\FinancialAnalysis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialAnalysisExcelExport
{
    protected $spreadsheet;
    protected $sheet;
    protected $currentRow = 1;

    public function export(FinancialAnalysis $analysis)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        
        $this->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $this->sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        
        $this->addHeader($analysis);
        $this->addApprovalCommittee($analysis);
        $this->addContractorTable($analysis);
        
        $this->autoSizeColumns();
        
        $writer = new Xlsx($this->spreadsheet);
        $filename = 'financial_analysis_' . $analysis->id . '_' . date('YmdHis') . '.xlsx';
        $filepath = storage_path('app/public/exports/' . $filename);
        
        if (!file_exists(storage_path('app/public/exports'))) {
            mkdir(storage_path('app/public/exports'), 0755, true);
        }
        
        $writer->save($filepath);
        
        return $filepath;
    }

    protected function addHeader(FinancialAnalysis $analysis)
    {
        $this->sheet->setCellValue('A' . $this->currentRow, 'FINANCIAL ANALYSIS REPORT');
        $this->sheet->mergeCells('A' . $this->currentRow . ':T' . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true)->setSize(16);
        $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->currentRow += 2;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'BIL:');
        $this->sheet->setCellValue('B' . $this->currentRow, $analysis->project_number);
        $this->currentRow++;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'NAMA PROJEK:');
        $this->sheet->setCellValue('B' . $this->currentRow, $analysis->project_name);
        $this->currentRow++;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'AGENSI:');
        $this->sheet->setCellValue('B' . $this->currentRow, $analysis->agency_name);
        $this->currentRow++;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'ANGGARAN JABATAN:');
        $this->sheet->setCellValue('B' . $this->currentRow, 'RM ' . number_format($analysis->department_budget, 2));
        $this->currentRow++;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'DAERAH:');
        $this->sheet->setCellValue('B' . $this->currentRow, $analysis->district_name);
        $this->currentRow++;
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'KELAS/KEPALA PROJEK:');
        $this->sheet->setCellValue('B' . $this->currentRow, $analysis->project_class);
        $this->currentRow += 2;
    }

    protected function addApprovalCommittee(FinancialAnalysis $analysis)
    {
        $this->sheet->setCellValue('A' . $this->currentRow, 'SENARAI (Approval Committee)');
        $this->sheet->mergeCells('A' . $this->currentRow . ':T' . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true);
        $this->currentRow++;
        
        foreach ($analysis->approvals as $approval) {
            $this->sheet->setCellValue('A' . $this->currentRow, $approval->position);
            $this->sheet->setCellValue('B' . $this->currentRow, $approval->name);
            $this->sheet->setCellValue('C' . $this->currentRow, $approval->department);
            $this->currentRow++;
        }
        
        $this->currentRow += 2;
    }

    protected function addContractorTable(FinancialAnalysis $analysis)
    {
        $headers = [
            'BIL', 'NAMA PROJEK', 'AGENSI', 'ANGGARAN JABATAN', 'DAERAH', 'KELAS/KEPALA PROJEK',
            'SENARAI', 'CADANGAN NAMA KONTRAKTOR', 'NO DAFTAR SYARIKAT', 'KELAS', 
            'TEMPOH SAH PENDAFTARAN', 'UPKJ', 'BEBAN KONTRAK SEMASA', 'REKOD PRESTASI',
            'HAD MINIMUM MODAL (RM)', 'BAKI AKHIR BULAN DALAM PENYATA BULANAN BANK (RM)',
            'PURATA 3 BULAN TERAKHIR (RM)', 'DEPOSIT TETAP (RM)', 'BAKI KEMUDAHAN KREDIT (RM)',
            'KEMUDAHAN KREDIT TAMBAHAN (RM)', 'KEPUTUSAN MESYUARAT', 'JUSTIFIKASI',
            'LAYAK (v) ATAU TIDAK LAYAK (x)', 'CATATAN'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
            $this->sheet->getStyle($col . $this->currentRow)->getFont()->setBold(true);
            $this->sheet->getStyle($col . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->sheet->getStyle($col . $this->currentRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        $this->currentRow++;
        
        $contractors = $analysis->contractors()->with(['contractor', 'bankStatements'])->get();
        $bil = 1;
        
        foreach ($contractors as $contractor) {
            $this->sheet->setCellValue('A' . $this->currentRow, $bil);
            $this->sheet->setCellValue('B' . $this->currentRow, $analysis->project_name);
            $this->sheet->setCellValue('C' . $this->currentRow, $analysis->agency_name);
            $this->sheet->setCellValue('D' . $this->currentRow, 'RM ' . number_format($analysis->department_budget, 2));
            $this->sheet->setCellValue('E' . $this->currentRow, $analysis->district_name);
            $this->sheet->setCellValue('F' . $this->currentRow, $analysis->project_class);
            $this->sheet->setCellValue('G' . $this->currentRow, '');
            $this->sheet->setCellValue('H' . $this->currentRow, $contractor->contractor_name);
            $this->sheet->setCellValue('I' . $this->currentRow, $contractor->registration_number);
            $this->sheet->setCellValue('J' . $this->currentRow, $contractor->contractor_class);
            $this->sheet->setCellValue('K' . $this->currentRow, $contractor->registration_validity_date ? $contractor->registration_validity_date->format('d/m/Y') : '');
            $this->sheet->setCellValue('L' . $this->currentRow, $contractor->upkj_classification);
            $this->sheet->setCellValue('M' . $this->currentRow, $contractor->current_contract_load ? 'RM ' . number_format($contractor->current_contract_load, 2) : '');
            $this->sheet->setCellValue('N' . $this->currentRow, $contractor->performance_record);
            $this->sheet->setCellValue('O' . $this->currentRow, $contractor->minimum_capital ? 'RM ' . number_format($contractor->minimum_capital, 2) : '');
            
            $bankStatements = $contractor->bankStatements()->orderBy('month_year', 'desc')->take(5)->get();
            $bankValues = [];
            foreach ($bankStatements as $statement) {
                $bankValues[] = $statement->ending_balance ? 'RM ' . number_format($statement->ending_balance, 2) : '';
            }
            $this->sheet->setCellValue('P' . $this->currentRow, implode(', ', $bankValues));
            
            $this->sheet->setCellValue('Q' . $this->currentRow, $contractor->three_month_average ? 'RM ' . number_format($contractor->three_month_average, 2) : '');
            $this->sheet->setCellValue('R' . $this->currentRow, $contractor->fixed_deposit ? 'RM ' . number_format($contractor->fixed_deposit, 2) : '');
            $this->sheet->setCellValue('S' . $this->currentRow, $contractor->credit_facility_balance ? 'RM ' . number_format($contractor->credit_facility_balance, 2) : '');
            $this->sheet->setCellValue('T' . $this->currentRow, $contractor->additional_credit_facility ? 'RM ' . number_format($contractor->additional_credit_facility, 2) : '');
            $this->sheet->setCellValue('U' . $this->currentRow, $contractor->meeting_decision);
            $this->sheet->setCellValue('V' . $this->currentRow, $contractor->justification);
            $this->sheet->setCellValue('W' . $this->currentRow, $contractor->is_qualified ? 'v' : 'x');
            $this->sheet->setCellValue('X' . $this->currentRow, $contractor->remarks);
            
            $this->currentRow++;
            $bil++;
        }
        
        $this->sheet->setCellValue('A' . $this->currentRow, 'JUMLAH');
        $this->sheet->setCellValue('B' . $this->currentRow, $contractors->count());
        $this->sheet->mergeCells('B' . $this->currentRow . ':X' . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow . ':X' . $this->currentRow)->getFont()->setBold(true);
        $this->currentRow++;
    }

    protected function autoSizeColumns()
    {
        foreach (range('A', 'X') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
