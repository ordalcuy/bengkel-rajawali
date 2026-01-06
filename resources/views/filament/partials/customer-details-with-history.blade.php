<div class="fi-fo-field-wrp">
    <div class="space-y-6">
        <!-- Informasi Pelanggan -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm dark:shadow-none">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Pelanggan</h3>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $pengunjung->kendaraans->count() }} kendaraan
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Diri -->
                <div class="space-y-4">
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $pengunjung->nama_pengunjung }}</p>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomor Telepon</label>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $pengunjung->nomor_tlp }}</p>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</label>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $pengunjung->alamat }}</p>
                        </div>
                    </div>
                </div>

                <!-- Daftar Kendaraan -->
                <div class="space-y-4">
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Kendaraan Terdaftar</label>
                    <div class="space-y-3">
                        @forelse($pengunjung->kendaraans as $kendaraan)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $kendaraan->nomor_plat }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $kendaraan->merk->value }} • {{ $kendaraan->jenisKendaraan->nama_jenis }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada kendaraan terdaftar</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Servis -->
        @if($riwayatServis->count() > 0)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm dark:shadow-none">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Servis Terakhir</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $riwayatServis->count() }} servis
                </span>
            </div>

            <div class="space-y-4">
                @foreach($riwayatServis as $riwayat)
                    <div class="flex items-start justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($riwayat->status === 'Selesai') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($riwayat->status === 'Dikerjakan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                    {{ $riwayat->status }}
                                </span>
                                <span class="text-sm font-mono text-gray-500 dark:text-gray-400">
                                    {{ $riwayat->nomor_antrean }}
                                </span>
                            </div>
                            
                            <div class="space-y-2">
                                <!-- Layanan -->
                                @if($riwayat->layanan->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($riwayat->layanan as $layanan)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                                @if($layanan->jenis_layanan === 'ringan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($layanan->jenis_layanan === 'sedang') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                                {{ $layanan->nama_layanan }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Mekanik -->
                                @if($riwayat->karyawan)
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Mekanik: <span class="font-medium">{{ $riwayat->karyawan->nama_karyawan }}</span>
                                    </p>
                                @endif
                                
                                <!-- Waktu -->
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $riwayat->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-400">
                        Belum ada riwayat servis
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Pelanggan ini belum pernah melakukan servis sebelumnya.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>