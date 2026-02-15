<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Project Print - {{ $preProject->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 5.5pt;
            line-height: 1.05;
            color: #000;
            width: 100%;
        }

        .print-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .print-header {
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 2px solid #000;
        }

        .print-header h1 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .print-header p {
            font-size: 8pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 1px 2px;
            text-align: left;
            vertical-align: middle;
            font-size: 5.5pt;
            word-wrap: break-word;
            overflow: hidden;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            line-height: 1.05;
        }

        .section-header {
            background-color: #d0d0d0;
            font-weight: bold;
            text-align: center;
            padding: 2px;
        }

        /* Optimized column widths for A4 landscape - total should be ~100% */
        .col-bil { width: 2%; }
        .col-nama { width: 7%; }
        .col-kategori { width: 4.5%; }
        .col-skop { width: 5.5%; }
        .col-cost { width: 3.3%; }
        .col-tempoh { width: 4.5%; }
        .col-kawasan { width: 3.8%; }
        .col-perancangan { width: 3.2%; }
        .col-status { width: 3.2%; }
        .col-khidmat { width: 2.8%; }
        .col-agensi { width: 4.5%; }
        .col-kaedah { width: 3.8%; }
        .col-ownership { width: 3.8%; }
        .col-jkkk { width: 4.5%; }
        .col-aset { width: 2.8%; }
        .col-boq { width: 2.8%; }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .notes {
            margin-top: 6px;
            font-size: 5.5pt;
        }

        .signature-section {
            margin-top: 10px;
        }

        .signature-box {
            width: 100%;
        }

        .signature-box p {
            font-size: 6.5pt;
            margin-bottom: 2px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 25px;
            padding-top: 2px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
            
            @page {
                margin: 8mm;
            }
        }

        @media screen {
            .print-button {
                position: fixed;
                top: 10px;
                right: 10px;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12pt;
                z-index: 1000;
            }

            .print-button:hover {
                background-color: #0056b3;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print</button>

    <div class="print-container">
        <!-- Header -->
        <div class="print-header">
            <h1>MAKLUMAT PROJEK PRA-PELAKSANAAN</h1>
            <p>Material Road Transformation Projects (RTP)</p>
        </div>

        <!-- Main Table -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="col-bil">BIL</th>
                    <th rowspan="2" class="col-nama">NAMA PROJEK</th>
                    <th rowspan="2" class="col-kategori">KATEGORI PROJEK</th>
                    <th rowspan="2" class="col-skop">SKOP PROJEK</th>
                    <th colspan="6" class="section-header">KOS PROJEK (RM)</th>
                    <th rowspan="2" class="col-tempoh">TEMPOH PELAKSANAAN</th>
                    <th colspan="4" class="section-header">KAWASAN</th>
                    <th colspan="3" class="section-header">PERANCANGAN</th>
                    <th rowspan="2" class="col-status">STATUS TANAH</th>
                    <th rowspan="2" class="col-khidmat">KHIDMAT RUNDINGAN</th>
                    <th rowspan="2" class="col-agensi">AGENSI PELAKSANA</th>
                    <th rowspan="2" class="col-kaedah">KAEDAH PELAKSANAAN</th>
                    <th rowspan="2" class="col-ownership">PROJECT OWNERSHIP</th>
                    <th rowspan="2" class="col-jkkk">NAMA JKKK</th>
                    <th rowspan="2" class="col-aset">ASET KERAJAAN NEGERI</th>
                    <th rowspan="2" class="col-boq">KETERSEDIAAN BOQ</th>
                </tr>
                <tr>
                    <!-- KOS PROJEK columns -->
                    <th class="col-cost">KOS SEBENAR PROJEK</th>
                    <th class="col-cost">KOS RUNDINGAN</th>
                    <th class="col-cost">KOS PEMERIKSAAN LSS</th>
                    <th class="col-cost">SST</th>
                    <th class="col-cost">LAIN-LAIN</th>
                    <th class="col-cost">JUMLAH</th>
                    <!-- KAWASAN columns -->
                    <th class="col-kawasan">BAHAGIAN</th>
                    <th class="col-kawasan">DAERAH</th>
                    <th class="col-kawasan">PARLIMEN</th>
                    <th class="col-kawasan">DUN</th>
                    <!-- PERANCANGAN columns -->
                    <th class="col-perancangan">PELAN TAPAK</th>
                    <th class="col-perancangan">STATUS TANAH</th>
                    <th class="col-perancangan">KHIDMAT RUNDINGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $preProject->name }}</td>
                    <td class="text-center">{{ $preProject->projectCategory->name ?? '-' }}</td>
                    <td>{{ $preProject->project_scope ?? '-' }}</td>
                    <td class="text-right">{{ number_format($preProject->actual_project_cost ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($preProject->consultation_cost ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($preProject->lss_inspection_cost ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($preProject->sst ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($preProject->others_cost ?? 0, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($preProject->total_cost ?? 0, 2) }}</strong></td>
                    <td class="text-center">{{ $preProject->implementation_period ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->division->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->district->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->parliamentLocation->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->dun->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->site_layout ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->landTitleStatus->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->consultation_service ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->landTitleStatus->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->consultation_service ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->implementingAgency->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->implementationMethod->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->projectOwnership->name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->jkkk_name ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->state_government_asset ?? '-' }}</td>
                    <td class="text-center">{{ $preProject->bill_of_quantity ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Notes -->
        <div class="notes">
            <p><strong>Nota:</strong></p>
            <p>(**) Sila nyatakan</p>
            <p style="margin-top: 4px;">Saya dengan ini bersetuju dengan senarai, skop dan kos projek seperti di atas yang telah dimuktamadkan di dalam Makmal Rural Transformation Projects (RTP) pada:</p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <!-- ADUN/Parliament Section -->
            <table style="width: 500px; border: none; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 35px; font-size: 6.5pt; width: 200px;">Nama ADUN, Ahli Parlimen / Wakil</td>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 35px; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 8px; font-size: 6.5pt; width: 200px;">Tandatangan</td>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 8px; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0; margin: 0; font-size: 6.5pt; width: 200px;">Tarikh</td>
                    <td style="border: none; padding: 0; margin: 0; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
            </table>

            <!-- Separator Line -->
            <hr style="border: none; border-top: 1px solid #000; margin: 15px 0; width: 100%;">

            <!-- Saksi Section -->
            <p style="font-size: 7pt; font-weight: bold; margin: 0 0 8px 0;">Saksi</p>
            <table style="width: 500px; border: none; border-collapse: collapse;">
                <tr>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 35px; font-size: 6.5pt; width: 200px;">Nama</td>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 35px; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 8px; font-size: 6.5pt; width: 200px;">Tandatangan</td>
                    <td style="border: none; padding: 0; margin: 0; padding-bottom: 8px; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0; margin: 0; font-size: 6.5pt; width: 200px;">Tarikh</td>
                    <td style="border: none; padding: 0; margin: 0; font-size: 6.5pt;">: ___________________________________________________________</td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
