<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrean - Bengkel Rajawali Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700,900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        body { background-color: #f1f5f9; font-family: 'Open Sans', sans-serif; overflow: hidden; }
        .font-title { font-family: 'Montserrat', sans-serif; }
        .card-anim { animation: cardAnim 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; }
        @keyframes cardAnim {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="flex flex-col h-screen">

    <header class="bg-white shadow-md p-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <svg class="h-8 w-8 text-slate-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-4.243-4.243l3.275-3.275a4.5 4.5 0 0 0-6.336 4.486c.061.58.035 1.193-.14 1.743H12z" /></svg>
            <h1 class="text-xl font-title font-black text-slate-800">BENGKEL RAJAWALI MOTOR</h1>
        </div>
        <div id="clock" class="text-xl font-bold font-mono text-slate-600"></div>
    </header>

    <main class="flex-grow grid grid-cols-12 grid-rows-6 gap-4 p-4">

        <div class="col-span-8 row-span-6 bg-white rounded-lg shadow-lg flex flex-col p-6">
            <h2 class="font-title font-bold text-2xl text-slate-500">NOMOR ANTRIAN</h2>
            <div id="now-serving-number" class="font-title font-black text-blue-600 text-[15rem] leading-none flex-grow flex items-center justify-center">
                {{ $now_serving?->nomor_antrean ?? '-' }}
            </div>
            <div id="now-serving-details" class="bg-slate-100 rounded-md p-4 grid grid-cols-3 gap-4 text-center">
                <div>
                    <h3 class="text-sm font-bold text-slate-500">PLAT NOMOR</h3>
                    <p id="plat-nomor" class="text-2xl font-bold text-slate-800">{{ $now_serving?->kendaraan->nomor_plat ?? '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-500">LAYANAN</h3>
                    <p id="nama-layanan" class="text-2xl font-bold text-slate-800">{{ $now_serving?->layanan->pluck('nama_layanan')->join(', ') ?? '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-500">MEKANIK</h3>
                    <p id="nama-mekanik" class="text-2xl font-bold text-slate-800">{{ $now_serving?->karyawan->nama_karyawan ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-span-4 row-span-4 bg-black rounded-lg shadow-lg">
            <iframe class="w-full h-full rounded-lg" src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1&loop=1&playlist=dQw4w9WgXcQ&controls=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>

        <div class="col-span-4 row-span-2 bg-slate-800 text-white rounded-lg shadow-lg flex flex-col justify-center items-center p-4">
            <h2 class="font-title font-bold text-xl text-slate-400">ANTRIAN SELANJUTNYA</h2>
            <p id="next-in-line" class="font-title font-black text-7xl">{{ $next_in_line?->id ?? '-' }}</p>
        </div>
    </main>

    <script>
        // ... (Fungsi playSound dan updateClock tidak berubah)
        function playSound(text) { if ('speechSynthesis' in window) { window.speechSynthesis.cancel(); const utterance = new SpeechSynthesisUtterance(text); utterance.lang = 'id-ID'; utterance.rate = 0.9; window.speechSynthesis.speak(utterance); } }
        function updateClock() { const now = new Date(); const hours = String(now.getHours()).padStart(2, '0'); const minutes = String(now.getMinutes()).padStart(2, '0'); document.getElementById('clock').textContent = `${hours}:${minutes}`; }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    <script src="{{ asset('js/tts-player.js') }}"></script>
<script type="module">
    // 1. Buat instance TTSPlayer agar bisa diakses di mana saja dalam script ini
    const ttsPlayer = new TTSPlayer();

    // 2. Dengarkan channel 'antrean-channel' untuk event 'AntreanDipanggil'
    Echo.channel('antrean-channel')
        .listen('AntreanDipanggil', (e) => {
            console.log('Event Panggilan Diterima:', e); // Untuk debugging

            // 3. Update teks di layar
            const nowServingElement = document.getElementById('now-serving-number');
            const mechanicElement = document.getElementById('now-serving-mechanic');

            if (nowServingElement) {
                nowServingElement.innerText = e.nomorAntrean;
            }
            if (mechanicElement) {
                mechanicElement.innerText = e.namaKaryawan;
            }

            // 4. Buat kalimat yang akan diucapkan
            const textToSpeak = `Nomor Antrean, ${e.nomorAntrean}, menuju ke, ${e.namaKaryawan}`;

            // 5. TAMBAHKAN KE ANTREN SUARA. INI BAGIAN PENTINGNYA!
            ttsPlayer.addToQueue(textToSpeak);
        });

    // Opsional: Jika Anda ingin memutar suara saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', () => {
        @if($nowServing)
            const initialText = `Nomor Antrean, {{ $nowServing->nomor_antrean }}, menuju ke, {{ $nowServing->karyawan->nama_karyawan ?? 'Loket' }}`;
            // ttsPlayer.addToQueue(initialText); // Hapus komentar jika ingin ada suara saat refresh
        @endif
    });
</script>
</body>
</html>