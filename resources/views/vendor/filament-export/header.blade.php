<div class="header">
    <div class="company-name">{{ config('bengkel.nama', 'Bengkel Rajawali Motor') }}</div>
    <div class="company-address">{{ config('bengkel.alamat', 'Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang') }}</div>
    <div class="company-phone">Telp: {{ config('bengkel.telepon', '085645523234') }}</div>
    <div class="report-title">Laporan Antrean Selesai</div>
    <div class="report-period">Periode: {{ now()->format('d/m/Y') }}</div>
</div>