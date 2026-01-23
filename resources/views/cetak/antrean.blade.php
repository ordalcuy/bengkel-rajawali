<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Antrean Bengkel Rajawali Motor</title>
    <style>
        @page {
            size: 72mm auto;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            width: 58mm;
            margin: 0 auto;
            padding: 5px;
            font-size: 9pt;
            color: #000;
            background-color: #fff;
        }
        
        /* Wrapper untuk kedua struk */
        .struk-wrapper {
            width: 100%;
        }
        
        .container {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            text-align: center;
        }
        
        /* Label jenis struk */
        .struk-label {
            background-color: #000;
            color: #fff;
            padding: 3px 8px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        /* Garis potong */
        .cut-line {
            border: none;
            border-top: 2px dashed #000;
            margin: 15px 0;
            position: relative;
        }
        .cut-line::before {
            content: '✂ potong di sini';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 5px;
            font-size: 6pt;
            color: #666;
        }
        
        /* Header */
        .header {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #000;
        }
        .header h1 {
            margin: 0;
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 7pt;
            color: #333;
        }
        
        /* Queue Number Box */
        .queue-box {
            background-color: #000;
            color: #fff;
            padding: 8px 0;
            margin: 8px 0;
        }
        .queue-number {
            font-size: 28pt;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 2px;
        }
        
        /* Details Section */
        .details {
            text-align: left;
            font-size: 8pt;
            margin: 8px 0;
            padding: 8px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            vertical-align: top;
            padding: 2px 0;
        }
        .details td:first-child {
            width: 55px;
            font-weight: bold;
        }
        .details td:nth-child(2) {
            width: 8px;
            text-align: center;
        }
        .details td:last-child {
            word-break: break-word;
        }
        
        /* QR Code Section */
        .qr-section {
            margin: 10px 0;
            padding: 8px 0;
        }
        .qr-section img {
            width: 80px;
            height: 80px;
        }
        .qr-section p {
            font-size: 7pt;
            margin: 5px 0 0;
            color: #333;
        }
        
        /* Footer */
        .footer {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 7pt;
            color: #333;
        }
        .footer p {
            margin: 2px 0;
        }
        .footer .thanks {
            font-weight: bold;
            font-size: 8pt;
        }
        
        /* Back Button */
        .btn-kembali {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .btn-kembali:hover {
            background-color: #2980b9;
        }
        
        @media print {
            .btn-kembali {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .cut-line::before {
                background: transparent;
            }
        }
        @media screen {
            body {
                width: 100%;
                background-color: #333;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding-top: 30px;
                min-height: 100vh;
            }
            .struk-wrapper {
                width: 58mm;
                background: white;
                padding: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                height: fit-content;
                margin-bottom: 20px;
            }
            .container {
            }
            .btn-kembali {
                display: block;
            }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(showBackButton, 1000);">
    <!-- Tombol Kembali -->
    <a href="{{ route('filament.admin.resources.antreans.index') }}" class="btn-kembali" id="btnKembali">
        ← Kembali
    </a>

    <div class="struk-wrapper">
        <!-- STRUK 1: PELANGGAN -->
        <div class="container">
            <div class="header">
                <h1>{{ config('bengkel.nama', 'BENGKEL RAJAWALI MOTOR') }}</h1>
                <p>Jl. Mertojoyo Selatan No.4, Merjosari</p>
                <p>Lowokwaru, Kota Malang</p>
                <p>Telp: 0856-4552-3234</p>
            </div>

            <div class="queue-box">
                <div class="queue-number">{{ $antrean->nomor_antrean }}</div>
            </div>

            <div class="details">
                <table>
                    <tr>
                        <td>Tanggal</td>
                        <td>:</td>
                        <td>{{ $antrean->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>:</td>
                        <td>{{ $antrean->created_at->format('H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Pelanggan</td>
                        <td>:</td>
                        <td>{{ $nama_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td>Plat</td>
                        <td>:</td>
                        <td>{{ $antrean->kendaraan->nomor_plat }}</td>
                    </tr>
                    @if($antrean->layanan && $antrean->layanan->count() > 0)
                    <tr>
                        <td>Layanan</td>
                        <td>:</td>
                        <td>
                            @php
                                $jenisMap = [
                                    'ringan' => 'Ringan',
                                    'sedang' => 'Sedang', 
                                    'berat' => 'Berat'
                                ];
                                $jenisLayanan = $antrean->layanan->pluck('jenis_layanan')
                                    ->unique()
                                    ->map(fn($j) => $jenisMap[$j] ?? ucfirst($j))
                                    ->implode(', ');
                            @endphp
                            {{ $jenisLayanan }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/lacak-id/' . $antrean->id) }}" alt="QR Code">
                <p>Scan untuk pantau status servis.</p>
                <p>Cek manual melalui website:</p>
                <p style="margin-top: 3px; font-weight: bold; font-size: 8pt;">{{ config('app.url') }}</p>
            </div>

            <div class="footer">
                <p class="thanks">Terima Kasih</p>
                <p>Silakan menunggu kendaraan Anda dikerjakan</p>
            </div>
        </div>

        <!-- GARIS POTONG -->
        <hr class="cut-line">

        <!-- STRUK 2: ARSIP BENGKEL -->
        <div class="container">
            <div class="header">
                <h1>{{ config('bengkel.nama', 'BENGKEL RAJAWALI MOTOR') }}</h1>
                <p>Jl. Mertojoyo Selatan No.4, Merjosari</p>
                <p>Lowokwaru, Kota Malang</p>
                <p>Telp: 0856-4552-3234</p>
            </div>

            <div class="queue-box">
                <div class="queue-number">{{ $antrean->nomor_antrean }}</div>
            </div>

            <div class="details">
                <table>
                    <tr>
                        <td>Tanggal</td>
                        <td>:</td>
                        <td>{{ $antrean->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>:</td>
                        <td>{{ $antrean->created_at->format('H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Pelanggan</td>
                        <td>:</td>
                        <td>{{ $nama_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td>Plat</td>
                        <td>:</td>
                        <td>{{ $antrean->kendaraan->nomor_plat }}</td>
                    </tr>
                    @if($antrean->layanan && $antrean->layanan->count() > 0)
                    <tr>
                        <td>Layanan</td>
                        <td>:</td>
                        <td>{{ $jenisLayanan }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/lacak-id/' . $antrean->id) }}" alt="QR Code">
                <p>Scan untuk pantau status servis.</p>
            </div>

            <div class="footer">
                <p class="thanks">Terima Kasih</p>
                <p>Silakan menunggu kendaraan Anda dikerjakan</p>
            </div>
        </div>
    </div>

    <script>
        function showBackButton() {
            document.getElementById('btnKembali').style.display = 'block';
        }
        window.onafterprint = function() {
            showBackButton();
        };
    </script>
</body>
</html>