<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Antrean Selesai</title>
    <style>
        /*
         * [SEDERHANA] mPDF sudah punya font UTF-8 bawaan ('dejavusans').
         * Kita tidak perlu @font-face atau link CDN.
         */
        body {
            font-family: 'dejavusans', sans-serif;
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
        .export-info {
            font-size: 10px;
            color: #666;
            text-align: right;
            margin-bottom: 15px;
        }
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
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 10px;
            word-wrap: break-word;
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
        .text-center {
            text-align: center;
        }
        .text-nowrap {
            white-space: nowrap;
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

    <div class="export-info">
        Dicetak pada: {{ $print_time }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%">No. Antrean</th>
                <th width="20%">Pelanggan</th>
                <th width="25%">Layanan</th>
                <th width="12%">Mekanik</th>
                <th width="12%">Mulai</th>
                <th width="12%">Selesai</th>
                <th width="11%">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($antreans as $antrean)
            <tr>
                <td class="text-center text-nowrap">{{ $antrean->nomor_antrean ?? 'N/A' }}</td>
                <td>
                    @if($antrean->pengunjung)
                        {{ $antrean->pengunjung->nama_pengunjung ?? 'N/A' }}
                    @elseif($antrean->kendaraan && $antrean->kendaraan->pengunjung)
                        {{ $antrean->kendaraan->pengunjung->nama_pengunjung ?? 'N/A' }}
                    @else
                        N/A
                    @endif
                </td>
                
                {{-- Display Jenis Layanan instead of individual services --}}
                <td>
                    @if($antrean->layanan->isNotEmpty())
                        @php
                            $jenisLayanan = $antrean->layanan->pluck('jenis_layanan')->unique()->map(function($jenis) {
                                return match($jenis) {
                                    'ringan' => 'Servis Ringan',
                                    'sedang' => 'Servis Sedang',
                                    'berat' => 'Servis Berat',
                                    default => ucfirst($jenis)
                                };
                            });
                        @endphp
                        {{ htmlspecialchars($jenisLayanan->implode(', '), ENT_QUOTES, 'UTF-8') }}
                    @else
                        N/A
                    @endif
                </td>
                
                <td>{{ $antrean->karyawan->nama_karyawan ?? 'N/A' }}</td>
                <td class="text-nowrap">
                    @if($antrean->waktu_mulai)
                        {{ \Carbon\Carbon::parse($antrean->waktu_mulai)->format('d/m/Y H:i') }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-nowrap">
                    @if($antrean->waktu_selesai)
                        {{ \Carbon\Carbon::parse($antrean->waktu_selesai)->format('d/m/Y H:i') }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-center text-nowrap">
                    @if($antrean->waktu_mulai && $antrean->waktu_selesai)
                        @php
                            try {
                                $mulai = \Carbon\Carbon::parse($antrean->waktu_mulai);
                                $selesai = \Carbon\Carbon::parse($antrean->waktu_selesai);
                                $diff = $mulai->diff($selesai);
                                $parts = [];
                                
                                if ($diff->h > 0) $parts[] = $diff->h . ' jam';
                                if ($diff->i > 0) $parts[] = $diff->i . ' menit';
                                if (empty($parts) && $diff->s > 0) $parts[] = $diff->s . ' detik';
                                
                                $durasi = implode(' ', $parts) ?: '< 1 menit';
                            } catch (Exception $e) {
                                $durasi = 'N/A';
                            }
                        @endphp
                        {{ $durasi }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data antrean selesai</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <strong>Total Antrean: {{ $total_antrean }}</strong> | Halaman 1 of 1
    </div>
</body>
</html>