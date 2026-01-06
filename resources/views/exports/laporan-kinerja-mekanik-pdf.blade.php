<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Kinerja Mekanik</title>
    <style>
        body {
            font-family: 'dejavusans', Arial, sans-serif;
            font-size: 12px;
            margin: 15px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #3B82F6;
        }
        .company-address {
            font-size: 12px;
            margin-bottom: 3px;
        }
        .company-phone {
            font-size: 12px;
            margin-bottom: 15px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-period {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        /* Summary Line */
        .summary-line {
            text-align: left;
            font-size: 12px;
            margin-bottom: 15px;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-line strong {
            color: #333;
        }
        
        .export-info {
            font-size: 10px;
            color: #666;
            text-align: right;
            margin-bottom: 15px;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }
        table th {
            background-color: #3B82F6;
            color: white;
            border: 1px solid #ddd;
            padding: 10px;
            font-weight: bold;
            font-size: 11px;
        }
        table th.text-left {
            text-align: left;
        }
        table th.text-right {
            text-align: right;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 10px;
            word-wrap: break-word;
        }
        table td.text-left {
            text-align: left;
        }
        table td.text-right {
            text-align: right;
        }
        table td.text-center {
            text-align: center;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company_name }}</div>
        <div class="company-address">{{ $company_address }}</div>
        <div class="company-phone">Telp: {{ $company_phone }}</div>
        <div class="report-title">{{ $report_title }}</div>
        <div class="report-period">Periode: {{ $report_period }}</div>
    </div>

    <!-- Summary Line -->
    <div class="summary-line">
        <strong>Ringkasan:</strong> Total Servis: {{ $total_servis }} | Rata-rata Durasi: {{ $avg_durasi }} menit | Mekanik Teraktif: {{ $top_mekanik }}
    </div>

    <div class="export-info">
        Dicetak pada: {{ $print_time }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="25%" class="text-left">Nama Mekanik</th>
                <th width="12%" class="text-right">Total Servis</th>
                <th width="14%" class="text-right">Total Durasi</th>
                <th width="14%" class="text-right">Rata-rata Durasi</th>
                <th width="10%" class="text-right">Ringan</th>
                <th width="10%" class="text-right">Sedang</th>
                <th width="10%" class="text-right">Berat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mekaniks as $index => $mekanik)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left">{{ $mekanik->nama_karyawan }}</td>
                <td class="text-right">{{ $mekanik->total_servis }}</td>
                <td class="text-right">{{ $mekanik->total_durasi_text }}</td>
                <td class="text-right">{{ $mekanik->avg_durasi_text }}</td>
                <td class="text-right">{{ $mekanik->servis_ringan }}</td>
                <td class="text-right">{{ $mekanik->servis_sedang }}</td>
                <td class="text-right">{{ $mekanik->servis_berat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data mekanik</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <strong>Total Mekanik: {{ $mekaniks->count() }}</strong> | Halaman 1 of 1
    </div>
</body>
</html>
