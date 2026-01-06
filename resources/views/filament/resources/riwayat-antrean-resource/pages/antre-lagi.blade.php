<x-filament-panels::page>
    <x-filament-panels::form wire:submit="createNewAntrean">
        {{ $this->form }}
        
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="fi-section rounded-xl border fi-border p-6">
            <h4 class="text-lg font-semibold fi-color-text mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Statistik Cepat
            </h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b fi-border">
                    <span class="fi-color-text-muted">Total Antrean Pelanggan</span>
                    <span class="font-semibold fi-color-primary">
                        {{ $record->pengunjung->antreans()->count() }}x
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b fi-border">
                    <span class="fi-color-text-muted">Kendaraan Terdaftar</span>
                    <span class="font-semibold fi-color-primary">
                        {{ $record->pengunjung->kendaraans()->count() }} unit
                    </span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="fi-color-text-muted">Antrean Terakhir</span>
                    <span class="font-semibold fi-color-primary">
                        {{ $record->created_at->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="fi-section rounded-xl border fi-border p-6 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
            <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Tips Cepat
            </h4>
            <ul class="space-y-2 text-blue-700 dark:text-blue-300 text-sm">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Periksa data pelanggan sebelum membuat antrean baru</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Pastikan kendaraan sesuai dengan layanan yang dipilih</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Pilih semua layanan yang diperlukan dalam satu antrean</span>
                </li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>