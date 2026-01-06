<x-filament-widgets::widget>
    <x-filament::section>
        <div 
            x-data="{
                time: new Date().toLocaleTimeString('id-ID', { hour12: false }),
                quotes: [
                    '⏳ Waktu terus berjalan, manfaatkan setiap detiknya',
                    '🌅 Hari ini adalah kesempatan baru untuk menjadi lebih baik',
                    '🔥 Jangan tunda, karena waktu tak akan menunggumu',
                    '💪 Gunakan waktumu untuk hal yang membuatmu berkembang',
                    '🌙 Setiap detik adalah anugerah, jangan sia-siakan',
                    '🕰️ Disiplin waktu adalah kunci kesuksesan',
                ],
                quote: '',
                init() {
                    this.quote = this.quotes[Math.floor(Math.random() * this.quotes.length)];
                    setInterval(() => {
                        this.time = new Date().toLocaleTimeString('id-ID', { hour12: false });
                    }, 1000);
                }
            }"
            x-init="init()"
            class="flex flex-col items-center justify-center py-8 space-y-6"
        >
            {{-- Hari & Tanggal --}}
            <div class="text-2xl font-semibold text-gray-800 dark:text-blue-300 tracking-wide">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </div>

            {{-- Jam Digital --}}
            <div 
                x-text="time"
                class="digital-clock text-7xl md:text-9xl font-mono font-bold text-green-600 dark:text-green-400 tracking-[0.25em]"
            ></div>

            {{-- Quote Otomatis --}}
            <div class="text-sm italic text-gray-600 dark:text-gray-400 mt-2 text-center" x-text="quote"></div>
        </div>

        {{-- Style --}}
        <style>
            .digital-clock {
                background: radial-gradient(circle at center, #f8fafc 60%, #e2e8f0);
                padding: 20px 40px;
                border-radius: 1rem;
                box-shadow: 
                    0 0 20px rgba(34, 197, 94, 0.3),
                    inset 0 0 15px rgba(34, 197, 94, 0.2);
                text-shadow: 
                    0 0 15px rgba(34, 197, 94, 0.7),
                    0 0 30px rgba(34, 197, 94, 0.4);
                transition: all 0.3s ease;
            }

            @media (min-width: 768px) {
                .digital-clock {
                    padding: 20px 60px;
                }
            }

            .dark .digital-clock {
                background: radial-gradient(circle at center, #000 60%, #0a0a0a);
                box-shadow: 
                    0 0 30px rgba(0, 255, 0, 0.4),
                    inset 0 0 20px rgba(0, 255, 0, 0.3);
                text-shadow: 
                    0 0 20px rgba(0, 255, 0, 0.9),
                    0 0 40px rgba(0, 255, 0, 0.6);
            }

            .digital-clock:hover {
                transform: scale(1.05);
                box-shadow: 
                    0 0 30px rgba(34, 197, 94, 0.5),
                    inset 0 0 20px rgba(34, 197, 94, 0.3);
                text-shadow: 
                    0 0 25px rgba(34, 197, 94, 0.9),
                    0 0 45px rgba(34, 197, 94, 0.6);
            }

            .dark .digital-clock:hover {
                box-shadow: 
                    0 0 40px rgba(0, 255, 0, 0.6),
                    inset 0 0 25px rgba(0, 255, 0, 0.4);
                text-shadow: 
                    0 0 30px rgba(0, 255, 0, 1),
                    0 0 60px rgba(0, 255, 0, 0.7);
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>