<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Analysis Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }
        
        h1 {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        
        .approval-section {
            margin-bottom: 20px;
        }
        
        .approval-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 10px;
        }
        
        .approval-member {
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: #e0e0e0;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #999;
            font-size: 8px;
        }
        
        td {
            padding: 5px 4px;
            border: 1px solid #999;
            font-size: 8px;
        }
        
        .qualified {
            background-color: #d4edda;
        }
        
        .not-qualified {
            background-color: #f8d7da;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-grid {
            display: table;
            width: 100%;
        }
        
        .signature-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        
        .signature-name {
            font-weight: bold;
        }
        
        .signature-position {
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>FINANCIAL ANALYSIS REPORT</h1>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">BIL:</span>
            <span>{{ $analysis->project_number ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NAMA PROJEK:</span>
            <span>{{ $analysis->project_name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">AGENSI:</span>
            <span>{{ $analysis->agency_name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">ANGGARAN JABATAN:</span>
            <span>RM {{ number_format($analysis->department_budget, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DAERAH:</span>
            <span>{{ $analysis->district_name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">KELAS/KEPALA PROJEK:</span>
            <span>{{ $analysis->project_class ?? '-' }}</span>
        </div>
    </div>
    
    <div class="approval-section">
        <div class="approval-title">SENARAI (Approval Committee)</div>
        @foreach($analysis->approvals as $approval)
        <div class="approval-member">
            {{ $approval->position }} - {{ $approval->name }} ({{ $approval->department }})
        </div>
        @endforeach
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">BIL</th>
                <th style="width: 8%;">NAMA KONTRAKTOR</th>
                <th style="width: 6%;">NO DAFTAR</th>
                <th style="width: 4%;">KELAS</th>
                <th style="width: 6%;">TEMPOH SAH</th>
                <th style="width: 6%;">UPKJ</th>
                <th style="width: 7%;">BEBAN KONTRAK</th>
                <th style="width: 7%;">REKOD PRESTASI</th>
                <th style="width: 7%;">MIN MODAL</th>
                <th style="width: 10%;">PENYATA BANK</th>
                <th style="width: 7%;">PURATA 3 BULAN</th>
                <th style="width: 6%;">DEPOSIT</th>
                <th style="width: 6%;">KREDIT</th>
                <th style="width: 6%;">KREDIT TAMBAHAN</th>
                <th style="width: 8%;">KEPUTUSAN</th>
                <th style="width: 3%;">LAYAK</th>
            </tr>
        </thead>
        <tbody>
            @php $bil = 1; @endphp
            @foreach($analysis->contractors as $contractor)
            <tr class="{{ $contractor->is_qualified ? 'qualified' : 'not-qualified' }}">
                <td style="text-align: center;">{{ $bil++ }}</td>
                <td>{{ $contractor->contractor_name }}</td>
                <td>{{ $contractor->registration_number }}</td>
                <td>{{ $contractor->contractor_class }}</td>
                <td>{{ $contractor->registration_validity_date ? $contractor->registration_validity_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $contractor->upkj_classification }}</td>
                <td>{{ $contractor->current_contract_load ? 'RM ' . number_format($contractor->current_contract_load, 2) : '-' }}</td>
                <td>{{ $contractor->performance_record ?? '-' }}</td>
                <td>{{ $contractor->minimum_capital ? 'RM ' . number_format($contractor->minimum_capital, 2) : '-' }}</td>
                <td>
                    @php
                        $statements = $contractor->bankStatements()->orderBy('month_year', 'desc')->take(5)->get();
                        $values = [];
                        foreach($statements as $stmt) {
                            $values[] = $stmt->ending_balance ? 'RM ' . number_format($stmt->ending_balance, 2) : '-';
                        }
                        echo implode(', ', $values);
                    @endphp
                </td>
                <td>{{ $contractor->three_month_average ? 'RM ' . number_format($contractor->three_month_average, 2) : '-' }}</td>
                <td>{{ $contractor->fixed_deposit ? 'RM ' . number_format($contractor->fixed_deposit, 2) : '-' }}</td>
                <td>{{ $contractor->credit_facility_balance ? 'RM ' . number_format($contractor->credit_facility_balance, 2) : '-' }}</td>
                <td>{{ $contractor->additional_credit_facility ? 'RM ' . number_format($contractor->additional_credit_facility, 2) : '-' }}</td>
                <td>{{ $contractor->meeting_decision ?? '-' }}</td>
                <td style="text-align: center;">{{ $contractor->is_qualified ? 'v' : 'x' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="15">JUMLAH: {{ $analysis->contractors->count() }} contractors</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
    @if($analysis->status === 'Approved')
    <div class="signature-section">
        <div class="signature-grid">
            @foreach($analysis->approvals->take(6) as $approval)
            <div class="signature-item">
                <div class="signature-line">
                    <div class="signature-name">{{ $approval->name }}</div>
                    <div class="signature-position">{{ $approval->position }}</div>
                    <div class="signature-position">{{ $approval->department }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #666;">
        Generated on {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
