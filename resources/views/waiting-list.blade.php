<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitor Pengerjaan - Bengkel Rajawali Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
    <style>
        body { background-color: #f1f1f1; font-family: sans-serif; }
        .job-item { animation: fadeIn 0.5s ease-out; display: flex; justify-content: space-between; align-items: center;}
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body class="p-4">
<header class="bg-white shadow-md p-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <svg class="h-8 w-8 text-slate-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-4.243-4.243l3.275-3.275a4.5 4.5 0 0 0-6.336 4.486c.061.58.035 1.193-.14 1.743H12z" /></svg>
            <h1 class="text-xl font-title font-black text-slate-800">MONITOR PENGERJAAN AKTIF</h1>
        </div>
        <div id="clock" class="text-xl font-bold font-mono text-slate-600"></div>
    </header>    
    <div class="grid grid-cols-3 gap-4 h-[88vh]">
        
        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col">
            <h2 class="text-2xl font-bold text-center text-gray-700 border-b-4 border-green-500 pb-2 mb-4">RINGAN</h2>
            <ul id="list-ringan" class="text-2xl font-semibold space-y-3">
                {{-- Tampilkan data awal dari Controller --}}
                @foreach($initialList['ringan'] ?? [] as $item)
                    <li class="bg-green-100 text-green-800 p-3 rounded-lg job-item">
                        <span>{{ $item->nomor_antrean }}</span>
                        <span class="text-lg font-medium text-gray-600">{{ $item->karyawan?->nama_karyawan ?? 'N/A' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col">
            <h2 class="text-2xl font-bold text-center text-gray-700 border-b-4 border-blue-500 pb-2 mb-4">SEDANG</h2>
            <ul id="list-sedang" class="text-2xl font-semibold space-y-3">
                {{-- Tampilkan data awal dari Controller --}}
                @foreach($initialList['sedang'] ?? [] as $item)
                    <li class="bg-blue-100 text-blue-800 p-3 rounded-lg job-item">
                        <span>{{ $item->nomor_antrean }}</span>
                        <span class="text-lg font-medium text-gray-600">{{ $item->karyawan?->nama_karyawan ?? 'N/A' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col">
            <h2 class="text-2xl font-bold text-center text-gray-700 border-b-4 border-red-500 pb-2 mb-4">BERAT</h2>
            <ul id="list-berat" class="text-2xl font-semibold space-y-3">
                 {{-- Tampilkan data awal dari Controller --}}
                 @foreach($initialList['berat'] ?? [] as $item)
                    <li class="bg-red-100 text-red-800 p-3 rounded-lg job-item">
                        <span>{{ $item->nomor_antrean }}</span>
                        <span class="text-lg font-medium text-gray-600">{{ $item->karyawan?->nama_karyawan ?? 'N/A' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- JavaScript Anda sudah benar dan tidak perlu diubah --}}
    <script type="module">
        function renderLists(lists) {
            const listMapping = {
                ringan: { el: document.getElementById('list-ringan'), css: 'bg-green-100 text-green-800' },
                sedang: { el: document.getElementById('list-sedang'), css: 'bg-blue-100 text-blue-800' },
                berat: { el: document.getElementById('list-berat'), css: 'bg-red-100 text-red-800' }
            };
            Object.values(listMapping).forEach(map => map.el.innerHTML = '');
            for (const [jenis, items] of Object.entries(lists)) {
                if (listMapping[jenis]) {
                    items.forEach(item => {
                        const li = document.createElement('li');
                        li.className = `${listMapping[jenis].css} p-3 rounded-lg job-item`;
                        li.innerHTML = `<span>${item.nomor_antrean}</span><span class="text-lg font-medium text-gray-600">${item.mekanik}</span>`;
                        listMapping[jenis].el.appendChild(li);
                    });
                }
            }
        }
        Echo.channel('waiting-list-channel').listen('WaitingListUpdated', (e) => {
            console.log('Data pengerjaan aktif diterima:', e.waitingList);
            renderLists(e.waitingList);
        });
    </script>
    <script>
        // ... (Fungsi playSound dan updateClock tidak berubah)
        function playSound(text) { if ('speechSynthesis' in window) { window.speechSynthesis.cancel(); const utterance = new SpeechSynthesisUtterance(text); utterance.lang = 'id-ID'; utterance.rate = 0.9; window.speechSynthesis.speak(utterance); } }
        function updateClock() { const now = new Date(); const hours = String(now.getHours()).padStart(2, '0'); const minutes = String(now.getMinutes()).padStart(2, '0'); document.getElementById('clock').textContent = `${hours}:${minutes}`; }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>