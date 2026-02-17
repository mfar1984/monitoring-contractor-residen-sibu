<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOC {{ $noc->noc_number }} - Print</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            font-size: 9px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .info-value {
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }
        
        th {
            background-color: #f0f0f0;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #000;
            vertical-align: top;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .budget-summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #000;
        }
        
        .budget-summary h3 {
            font-size: 11px;
            margin-bottom: 8px;
        }
        
        .budget-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .budget-item {
            font-size: 9px;
        }
        
        .budget-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .budget-value {
            font-size: 11px;
            font-weight: bold;
        }
        
        .signatures {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }
        
        .signature-box {
            border: 1px solid #000;
            padding: 15px;
            min-height: 100px;
        }
        
        .signature-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 10px;
        }
        
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 9px;
        }
        
        .signature-info {
            font-size: 8px;
            color: #666;
            margin-top: 5px;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
            Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-left: 5px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>NOTICE OF CHANGE (NOC)</h1>
        <h2>{{ $noc->noc_number }}</h2>
        <div style="font-size: 10px;">{{ $noc->parliament?->name ?? $noc->dun?->name ?? '-' }}</div>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">NOC Number:</div>
                <div class="info-value">{{ $noc->noc_number }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">NOC Date:</div>
                <div class="info-value">{{ $noc->noc_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Parliament / DUN:</div>
                <div class="info-value">{{ $noc->parliament?->name ?? $noc->dun?->name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ $noc->status }}</div>
            </div>
        </div>
    </div>

    @php
        $totalOriginal = 0;
        $totalNew = 0;
        foreach($projectEntries as $entry) {
            $kosAsal = $entry->pivot->kos_asal ?? 0;
            $kosBaru = $entry->pivot->kos_baru ?? 0;
            $totalOriginal += $kosAsal;
            $totalNew += $kosBaru > 0 ? $kosBaru : 0;
        }
        $budgetDifference = $totalOriginal - $totalNew;
    @endphp

    <div class="budget-summary">
        <h3>Budget Summary</h3>
        <div class="budget-grid">
            <div class="budget-item">
                <div class="budget-label">Total Original Budget:</div>
                <div class="budget-value">RM {{ number_format($totalOriginal, 2) }}</div>
            </div>
            <div class="budget-item">
                <div class="budget-label">Total New Budget:</div>
                <div class="budget-value">RM {{ number_format($totalNew, 2) }}</div>
            </div>
            <div class="budget-item">
                <div class="budget-label">Budget Difference:</div>
                <div class="budget-value">RM {{ number_format($budgetDifference, 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">Bil</th>
                <th style="width: 50px;">Tahun RTP</th>
                <th style="width: 60px;">No Projek</th>
                <th style="width: 120px;">Nama Projek Asal</th>
                <th style="width: 120px;">Nama Projek Baru</th>
                <th style="width: 70px;" class="text-right">Kos Asal (RM)</th>
                <th style="width: 70px;" class="text-right">Kos Baru (RM)</th>
                <th style="width: 90px;">Agensi Asal</th>
                <th style="width: 90px;">Agensi Baru</th>
                <th style="width: 100px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projectEntries as $index => $entry)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $entry->pivot->tahun_rtp ?? '-' }}</td>
                <td>{{ $entry->pivot->no_projek ?? '-' }}</td>
                <td>{{ $entry->pivot->nama_projek_asal ?? '-' }}</td>
                <td>{{ $entry->pivot->nama_projek_baru ?: 'No change' }}</td>
                <td class="text-right">{{ number_format($entry->pivot->kos_asal ?? 0, 2) }}</td>
                <td class="text-right">{{ $entry->pivot->kos_baru ? number_format($entry->pivot->kos_baru, 2) : 'No change' }}</td>
                <td>{{ $entry->pivot->agensi_pelaksana_asal ?? '-' }}</td>
                <td>{{ $entry->pivot->agensi_pelaksana_baru ?: 'No change' }}</td>
                <td>
                    @php
                        $nocNote = \App\Models\NocNote::find($entry->pivot->noc_note_id);
                    @endphp
                    {{ $nocNote?->name ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No projects found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-grid">
            <div class="signature-box">
                <div class="signature-title">First Approver</div>
                @if($noc->first_approver_id)
                    <div class="signature-info">
                        <strong>Name:</strong> {{ $noc->firstApprover?->full_name ?? '-' }}<br>
                        <strong>Date:</strong> {{ $noc->first_approved_at?->format('d/m/Y') ?? '-' }}<br>
                        @if($noc->first_approval_remarks)
                        <strong>Remarks:</strong> {{ $noc->first_approval_remarks }}
                        @endif
                    </div>
                @else
                    <div class="signature-line">
                        Signature: _______________________
                    </div>
                    <div class="signature-info">
                        Name: _______________________<br>
                        Date: _______________________
                    </div>
                @endif
            </div>

            <div class="signature-box">
                <div class="signature-title">Second Approver</div>
                @if($noc->second_approver_id)
                    <div class="signature-info">
                        <strong>Name:</strong> {{ $noc->secondApprover?->full_name ?? '-' }}<br>
                        <strong>Date:</strong> {{ $noc->second_approved_at?->format('d/m/Y') ?? '-' }}<br>
                        @if($noc->second_approval_remarks)
                        <strong>Remarks:</strong> {{ $noc->second_approval_remarks }}
                        @endif
                    </div>
                @else
                    <div class="signature-line">
                        Signature: _______________________
                    </div>
                    <div class="signature-info">
                        Name: _______________________<br>
                        Date: _______________________
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div style="margin-top: 20px; font-size: 8px; color: #666; text-align: center;">
        Printed on {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
