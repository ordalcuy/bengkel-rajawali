<div 
    x-data="{ 
        currentTime: '{{ now()->format('H:i:s') }}',
        currentDate: '{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}',
        init() {
            setInterval(() => {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                this.currentTime = `${hours}:${minutes}:${seconds}`;
            }, 1000);
        }
    }"
    class="fi-wi-datetime bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4"
>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                <p x-text="currentDate" class="text-lg font-semibold text-gray-900 dark:text-white"></p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Waktu</p>
                <p x-text="currentTime" class="text-lg font-semibold text-gray-900 dark:text-white font-mono"></p>
            </div>
        </div>
    </div>
</div>
