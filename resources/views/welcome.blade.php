<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrean Bengkel - Rajawali {{ config('bengkel.nama', 'Bengkel Kita') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        secondary: '#0ea5e9', // Sky blue for accents
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Custom scrollbar for table */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Glassmorphism Utilities */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .glass-dark {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Hero Background Pattern */
        .hero-pattern {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* QR Scanner Styles Override */
        .qr-scanner-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .qr-scanner-overlay.active {
            opacity: 1;
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: #0ea5e9;
            box-shadow: 0 0 4px #0ea5e9;
            top: 0;
            animation: scan 2s linear infinite;
        }

        @keyframes scan {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        /* Smooth Anchor Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Selection Color */
        ::selection {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased overflow-x-hidden selection:bg-blue-500 selection:text-white">
    
    <!-- QR Scanner Overlay -->
    <div class="qr-scanner-overlay flex items-center justify-center p-4" id="qrScannerOverlay">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="qrScannerContainer">
            <div class="p-4 bg-gray-900 flex justify-between items-center text-white">
                <h3 class="font-semibold text-lg flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Scan QR Code
                </h3>
                <button id="closeScanner" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="relative bg-black aspect-square overflow-hidden rounded-lg">
                <div id="qr-reader" class="w-full h-full"></div>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div id="scannerStatus" class="text-sm text-gray-500 text-center mb-3">Menyiapkan kamera...</div>
                <div id="scannerError" class="text-xs text-red-500 mt-1 hidden text-center"></div>
                
                <!-- Upload Image Option -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label for="qrImageUpload" class="block w-full cursor-pointer">
                        <div class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih Foto QR dari Galeri</span>
                        </div>
                    </label>
                    <input type="file" id="qrImageUpload" accept="image/*" class="hidden">
                    <p class="text-xs text-gray-500 text-center mt-2">Atau pilih foto QR yang sudah tersimpan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Navbar with Glassmorphism -->
    <nav class="fixed w-full z-40 top-0 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-white/20 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <div class="bg-gradient-to-tr from-blue-600 to-cyan-500 text-white p-1.5 rounded-lg shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.72,11L13.71,8.5H16V7H13.72L12.72,11M20,12C18.44,12 17.09,12.79 16.28,14H13.5A2,2 0 0,0 11.5,12A2,2 0 0,0 9.5,14H7.82C6.84,12.75 5.3,12 3.5,12C1.67,12 0,13.67 0,15.5C0,17.33 1.67,19 3.5,19C5.3,19 6.84,18.25 7.82,17H9.5A2,2 0 0,0 11.5,19A2,2 0 0,0 13.5,17H16.28C17.09,18.21 18.44,19 20,19C21.83,19 23.5,17.33 23.5,15.5C23.5,13.67 21.83,12 20,12M3.5,17C2.78,17 2,16.22 2,15.5C2,14.78 2.78,14 3.5,14C4.22,14 5,14.78 5,15.5C5,16.22 4.22,17 3.5,17M20,17C19.28,17 18.5,16.22 18.5,15.5C18.5,14.78 19.28,14 20,14C20.72,14 21.5,14.78 21.5,15.5C21.5,16.22 20.72,17 20,17M9.35,11H11V7H9.35C9.75,7.37 10.11,7.78 10.42,8.24L9.35,11Z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-gray-900">
                        {{ config('bengkel.nama', 'Bengkel Kita') }}
                    </span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="#" class="text-blue-600 font-medium border-b-2 border-blue-600 py-1 transition-colors">Beranda</a>
                    <a href="#cek-antrean" class="text-gray-600 hover:text-blue-600 font-medium py-1 transition-colors">Lacak Status</a>
                    <a href="#antrean-saat-ini" class="text-gray-600 hover:text-blue-600 font-medium py-1 transition-colors">Antrean Aktif</a>
                    <a href="#informasi" class="text-gray-600 hover:text-blue-600 font-medium py-1 transition-colors">Informasi</a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-600 hover:text-blue-600 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-24 pb-16 lg:pt-32 lg:pb-24 overflow-hidden">
        <!-- Animated Background Shapes -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                <div class="lg:w-1/2 text-center lg:text-left">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 shadow-sm">
                        <span class="flex h-2 w-2 rounded-full bg-blue-600 mr-2 animate-pulse"></span>
                        Real-time Monitoring System
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6 tracking-tight">
                        Pantau Kendaraan Anda <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">Tanpa Antre</span>
                    </h1>
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Sistem manajemen antrean cerdas untuk pengalaman servis yang lebih baik. Cek status pengerjaan dan riwayat servis dalam satu genggaman.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#cek-antrean" class="group relative px-8 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 overflow-hidden">
                            <span class="relative z-10 flex items-center justify-center">
                                Cek Status Sekarang
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </a>
                        <a href="#antrean-saat-ini" class="px-8 py-3.5 bg-white text-slate-700 hover:text-blue-600 font-semibold rounded-xl border border-slate-200 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm hover:shadow-md flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Antrean Aktif
                        </a>
                    </div>
                </div>
                
                <div class="lg:w-1/2 relative lg:h-[500px] w-full flex items-center justify-center">
                    <div class="relative w-full max-w-lg aspect-square lg:aspect-auto h-full">
                        <!-- Abstract Dashboard UI Mockup -->
                        <div class="absolute top-[10%] left-[5%] w-[90%] bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl p-6 border border-white/40 animate-float z-20">
                            <!-- Header Mockup -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <div class="h-2.5 w-24 bg-slate-200 rounded mb-1.5"></div>
                                        <div class="h-2 w-16 bg-slate-100 rounded"></div>
                                    </div>
                                </div>
                                <div class="px-3 py-1 rounded-full bg-green-100 text-green-600 text-xs font-bold">Active</div>
                            </div>
                            <!-- Progres Bar Mockup -->
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <div class="h-2 w-12 bg-slate-200 rounded"></div>
                                        <div class="h-2 w-8 bg-blue-200 rounded"></div>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full w-3/4"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                        <div class="h-2 w-12 bg-slate-200 rounded mb-2"></div>
                                        <div class="h-4 w-8 bg-slate-300 rounded"></div>
                                    </div>
                                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                        <div class="h-2 w-12 bg-slate-200 rounded mb-2"></div>
                                        <div class="h-4 w-16 bg-slate-300 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Floating Card -->
                        <div class="absolute bottom-[15%] -right-[5%] w-[60%] bg-white/95 backdrop-blur-md rounded-xl shadow-xl p-4 border border-white/50 animate-float-delayed z-30 hidden sm:block">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Servis Selesai</div>
                                    <div class="text-xs text-slate-500">Baru saja</div>
                                </div>
                            </div>
                        </div>

                        <!-- Decoration Circle -->
                        <div class="absolute top-[20%] right-[10%] w-24 h-24 rounded-full border-4 border-dashed border-cyan-300 animate-spin-slow opacity-50 z-10"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Session Feedback -->
    @if(session('antrean_lacak') || session('error_lacak'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        <div class="glass rounded-xl p-1 shadow-lg border border-white/50 animate-fade-in-up">
            @if(session('antrean_lacak'))
            <div class="bg-green-50/50 rounded-lg p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-full shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Antrean Ditemukan: {{ session('antrean_lacak')->nomor_antrean }}</h3>
                        <p class="text-slate-600">
                            Status: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ session('antrean_lacak')->status == 'Dikerjakan' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ session('antrean_lacak')->status }}
                            </span>
                             • Plat: {{ session('antrean_lacak')->kendaraan->nomor_plat ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <button class="flex-1 md:flex-none px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm hover:shadow">
                        Aktifkan Notifikasi
                    </button>
                    <a href="{{ url('/') }}" class="px-4 py-2 bg-white text-slate-600 border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition">
                        Tutup
                    </a>
                </div>
            </div>
            @endif

            @if(session('error_lacak'))
            <div class="bg-red-50/50 rounded-lg p-4 flex items-center gap-4">
                <div class="bg-red-100 p-2 rounded-full text-red-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-800">Pencarian Gagal</h3>
                    <p class="text-sm text-red-600">{{ session('error_lacak') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Cek Status & Info (lg: 4 cols) -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Cek Status Card -->
                <section id="cek-antrean" class="relative">
                    <div class="glass rounded-2xl p-6 md:p-8 shadow-xl border-t border-white">
                        <div class="absolute -top-6 -left-6 bg-blue-600 rounded-2xl w-12 h-12 flex items-center justify-center text-white shadow-lg shadow-blue-500/40 transform rotate-12">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-slate-800 mb-2 mt-2">Cek Status</h2>
                        <p class="text-slate-500 text-sm mb-6">Masukkan nomor antrean atau scan QR code.</p>

                        <form action="{{ route('lacak.submit') }}" method="POST" class="space-y-4" id="form-lacak">
                            @csrf
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Antrean</label>
                                <div class="relative">
                                    <input type="text" name="nomor_antrean" id="nomor_antrean" 
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-medium placeholder:text-slate-300"
                                        placeholder="Contoh: A001"
                                        value="{{ old('nomor_antrean') }}">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Toggle Hari Ini / Kemarin --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cari Antrean</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 relative">
                                        <input type="radio" name="tanggal_filter" value="hari_ini" class="peer sr-only" checked>
                                        <div class="w-full py-2.5 px-3 text-center border-2 border-slate-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all text-sm font-medium text-slate-600">
                                            📅 Hari Ini
                                        </div>
                                    </label>
                                    <label class="flex-1 relative">
                                        <input type="radio" name="tanggal_filter" value="kemarin" class="peer sr-only">
                                        <div class="w-full py-2.5 px-3 text-center border-2 border-slate-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all text-sm font-medium text-slate-600">
                                            📆 Kemarin
                                        </div>
                                    </label>
                                </div>
                                <p class="text-xs text-slate-500 mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2">
                                    💡 <span class="text-slate-600">Mode "Kemarin" hanya berlaku untuk 1 hari sebelumnya (H-1).</span><br>
                                    <span class="text-slate-600">Untuk antrean lebih lama, gunakan</span> <strong class="text-blue-600">Scan QR Code Struk</strong>.
                                </p>
                            </div>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 group">
                                <span>Lacak Sekarang</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </button>
                        </form>
                        
                        <div class="my-6 flex items-center gap-3">
                            <div class="h-px bg-slate-200 flex-1"></div>
                            <span class="text-xs text-slate-400 font-medium uppercase">Atau</span>
                            <div class="h-px bg-slate-200 flex-1"></div>
                        </div>

                        <button id="startScanner" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-3 rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Scan QR Code Struk</span>
                        </button>
                    </div>
                </section>

                <!-- Small Info Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600 mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-xs text-slate-500 font-medium uppercase">Jam Operasional</p>
                        <p class="text-slate-800 font-bold text-sm mt-1">08:00 - 17:00</p>

                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition">
                         <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600 mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <p class="text-xs text-slate-500 font-medium uppercase">Customer Service</p>
                        <p class="text-slate-800 font-bold text-sm mt-1">{{ config('bengkel.telepon', '0812-3456-7890') }}</p>
                        <p class="text-xs text-blue-500 mt-1 underline cursor-pointer">Chat WhatsApp</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Queue (lg: 8 cols) -->
            <div class="lg:col-span-8">
                <section id="antrean-saat-ini" class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col h-full">
                    <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-4 bg-slate-50/50">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                Antrean Aktif
                            </h2>
                            <p class="text-sm text-slate-500">Update otomatis setiap 30 detik</p>
                        </div>
                        
                        <!-- Filter Chips -->
                        <div class="flex p-1 bg-slate-200/60 rounded-lg">
                            <button class="px-4 py-1.5 rounded-md text-sm font-medium transition-all shadow bg-white text-blue-700" data-filter="all">Semua</button>
                            <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-200/50 transition-all" data-filter="menunggu">Menunggu</button>
                            <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-200/50 transition-all" data-filter="dikerjakan">Diproses</button>
                        </div>
                    </div>

                    <!-- Modern Table with Internal Scroll -->
                    <div class="overflow-x-auto grow max-h-[500px] overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse relative">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/95 text-xs uppercase text-slate-500 font-semibold tracking-wider sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                                    <th class="px-6 py-4">Nomor & Waktu</th>
                                    <th class="px-6 py-4">Kendaraan</th>
                                    <th class="px-6 py-4">Status & Mekanik</th>
                                    <th class="px-6 py-4">Layanan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="antrean-list">
                                @forelse($antreanAktif as $antrean)
                                <tr class="hover:bg-blue-50/30 transition-colors group cursor-default antrean-item" data-status="{{ strtolower($antrean->status) }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 font-bold text-lg flex items-center justify-center border border-blue-200">
                                                {{ $antrean->nomor_antrean }}
                                            </div>
                                            <div>
                                                <div class="text-xs text-slate-500 mb-0.5">Check-in</div>
                                                <div class="font-medium text-slate-700">{{ $antrean->created_at->format('H:i') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">{{ $antrean->kendaraan->nomor_plat ?? 'N/A' }}</div>
                                        <div class="text-xs text-slate-500 capitalize">{{ $antrean->kendaraan->merk ?? 'Umum' }} • {{ $antrean->kendaraan->tipe ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            @if($antrean->status == 'Dikerjakan')
                                                <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                                    Sedang Dikerjakan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></span>
                                                    Menunggu
                                                </span>
                                            @endif
                                            <div class="flex items-center text-xs text-slate-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $antrean->karyawan->nama_karyawan ?? 'Menunggu Mekanik' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $services = $antrean->layanan->pluck('jenis_layanan')->unique()->take(2);
                                                $count = $antrean->layanan->pluck('jenis_layanan')->unique()->count();
                                            @endphp
                                            @foreach($services as $service)
                                                 <span class="inline-block px-2 py-0.5 text-[10px] font-medium text-slate-600 bg-slate-100 rounded border border-slate-200">
                                                    {{ ucfirst($service) }}
                                                </span>
                                            @endforeach
                                            @if($count > 2)
                                                <span class="inline-block px-2 py-0.5 text-[10px] font-medium text-slate-500 bg-slate-50 rounded border border-slate-100">+{{ $count - 2 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium">Belum ada antrean hari ini</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Footer Pagination/Stats -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
                        <span>Menampilkan {{ $antreanAktif->count() }} antrean aktif</span>
                        <div class="flex gap-4">
                            <span class="flex items-center"><div class="w-2 h-2 rounded-full bg-blue-500 mr-1.5"></div> Total: {{ $antreanAktif->count() }}</span>
                            <span class="flex items-center"><div class="w-2 h-2 rounded-full bg-green-500 mr-1.5"></div> Proses: {{ $antreanAktif->where('status', 'Dikerjakan')->count() }}</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        
        <!-- Bottom Info Section -->
        <section id="informasi" class="mt-20 border-t border-slate-200 pt-12">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                 <div class="col-span-1 md:col-span-1">
                     <div class="flex items-center gap-2 mb-4">
                         <div class="bg-blue-600 text-white p-1 rounded">
                             <span class="font-bold text-lg">RK</span>
                         </div>
                         <h3 class="font-bold text-xl text-slate-800">{{ config('bengkel.nama', 'Rajawali Bengkel') }}</h3>
                     </div>
                     <p class="text-slate-500 text-sm leading-relaxed mb-4">
                         Bengkel profesional dengan layanan terbaik dan transparan. Kami mengutamakan kepuasan pelanggan dengan hasil kerja yang presisi.
                     </p>
                     <div class="flex space-x-3">
                         <!-- Social Icons Here -->
                     </div>
                 </div>
                 
                 <!-- Kolom Lokasi & Kontak -->
                 <div class="col-span-1 md:col-span-1">
                     <h4 class="font-bold text-slate-800 mb-4">Lokasi &amp; Kontak</h4>
                     <ul class="space-y-3 text-sm text-slate-600">
                         <li class="flex items-start">
                             <svg class="w-5 h-5 text-blue-500 mr-3 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                             <span>{{ config('bengkel.alamat', 'Jl. Raya Utama No. 123, Kota Sejahtera, Indonesia') }}</span>
                         </li>
                         <li class="flex items-center">
                             <svg class="w-5 h-5 text-blue-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                             <span>{{ config('bengkel.telepon', '0812-3456-7890') }}</span>
                         </li>
                     </ul>
                 </div>
             </div>
        </section>
        
        <footer class="mt-12 pt-8 border-t border-slate-200 text-center text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ config('bengkel.nama', 'Bengkel Kita') }}. All rights reserved. Powered by Laravel & Tailwind.</p>
        </footer>
    </div>

    <!-- Logic Script -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        // Tab Filtering Logic
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const tableRows = document.querySelectorAll('.antrean-item');
            
            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update Active State
                    filterButtons.forEach(b => {
                        b.classList.remove('bg-white', 'text-blue-700', 'shadow');
                        b.classList.add('text-slate-600', 'hover:bg-slate-200/50');
                    });
                    btn.classList.remove('text-slate-600', 'hover:bg-slate-200/50');
                    btn.classList.add('bg-white', 'text-blue-700', 'shadow');
                    
                    const filter = btn.getAttribute('data-filter');
                    
                    tableRows.forEach(row => {
                        if (filter === 'all') {
                            row.style.display = 'table-row';
                        } else {
                            const status = row.getAttribute('data-status');
                            if (status === filter) {
                                row.style.display = 'table-row';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            });
        });

        // Robust QR Scanner Logic using html5-qrcode
        const startBtn = document.getElementById('startScanner');
        const closeBtn = document.getElementById('closeScanner');
        const overlay = document.getElementById('qrScannerOverlay');
        const statusElem = document.getElementById('scannerStatus');
        const inputElem = document.getElementById('nomor_antrean');
        const formElem = document.getElementById('form-lacak');
        
        let html5QrcodeScanner = null;

        startBtn.addEventListener('click', () => {
            overlay.style.display = 'flex';
            setTimeout(() => overlay.classList.add('active'), 10);
            startScanning();
        });

        const startScanning = () => {
            statusElem.textContent = "Memulai kamera...";
            statusElem.className = "text-sm text-gray-500";
            
            // Initialize scanner if not already done
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5Qrcode("qr-reader");
            }

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                statusElem.textContent = "Kamera aktif. Arahkan ke QR Code.";
                statusElem.className = "text-sm text-green-600 font-medium";
            }).catch(err => {
                console.error("Error starting scanner", err);
                statusElem.textContent = "Gagal akses kamera: " + (err.message || err);
                statusElem.className = "text-sm text-red-500";
            });
        };

        const onScanSuccess = (decodedText, decodedResult) => {
            console.log(`Scan result: ${decodedText}`, decodedResult);
            
            // Prevent multiple reads
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            // Check if scanned text is a URL (starts with http:// or https://)
            if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                statusElem.textContent = "URL Terdeteksi! Mengalihkan...";
                statusElem.className = "text-sm text-green-600 font-bold";
                
                // Direct redirect to the URL
                setTimeout(() => {
                    stopScanner();
                    window.location.href = decodedText;
                }, 500);
            } else {
                // It's a queue number, extract and submit form
                const matches = decodedText.match(/([A-Z0-9-]+)/i);
                
                if (matches) {
                    const code = matches[0];
                    inputElem.value = code;
                    
                    statusElem.textContent = "QR Code Terdeteksi! Mengalihkan...";
                    statusElem.className = "text-sm text-blue-600 font-bold";
                    
                    // Visual feedback
                    inputElem.classList.add('ring-2', 'ring-green-500', 'bg-green-50');
                    
                    // Stop and submit
                    setTimeout(() => {
                        stopScanner();
                        formElem.submit();
                    }, 800);
                } else {
                    // If nothing matches, resume scanning
                    statusElem.textContent = "QR Code tidak valid, coba lagi...";
                    statusElem.className = "text-sm text-yellow-600";
                    if (html5QrcodeScanner) html5QrcodeScanner.resume();
                }
            }
        };

        const onScanFailure = (error) => {
            // handle scan failure, usually better to ignore and keep scanning.
            // console.warn(`Code scan error = ${error}`);
        };

        const stopScanner = () => {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().then(() => {
                        html5QrcodeScanner.clear();
                        html5QrcodeScanner = null;
                    }).catch(err => {
                        console.error("Failed to stop scanner", err);
                    });
                }
            }, 300);
        };

        closeBtn.addEventListener('click', stopScanner);
        
        // Close on clicking outside (overlay)
        overlay.addEventListener('click', (e) => {
            if(e.target === overlay) stopScanner();
        });

        // File Upload Handler for QR Code
        const fileInput = document.getElementById('qrImageUpload');
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            statusElem.textContent = "Memproses gambar...";
            statusElem.className = "text-sm text-blue-600 font-medium text-center mb-3";

            try {
                // Stop camera if running
                if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                    await html5QrcodeScanner.stop();
                }

                // Initialize scanner if needed
                if (!html5QrcodeScanner) {
                    html5QrcodeScanner = new Html5Qrcode("qr-reader");
                }

                // Scan the file
                const result = await html5QrcodeScanner.scanFile(file, true);
                
                // Process result
                console.log("File scan result:", result);
                
                // Check if result is a URL
                if (result.startsWith('http://') || result.startsWith('https://')) {
                    statusElem.textContent = "URL Terdeteksi! Mengalihkan...";
                    statusElem.className = "text-sm text-green-600 font-bold text-center mb-3";
                    
                    setTimeout(() => {
                        stopScanner();
                        window.location.href = result;
                    }, 500);
                } else {
                    // It's a queue number
                    const matches = result.match(/([A-Z0-9-]+)/i);
                    if (matches) {
                        const code = matches[0];
                        inputElem.value = code;
                        
                        statusElem.textContent = "QR Code Terdeteksi! Mengalihkan...";
                        statusElem.className = "text-sm text-green-600 font-bold text-center mb-3";
                        
                        inputElem.classList.add('ring-2', 'ring-green-500', 'bg-green-50');
                        
                        setTimeout(() => {
                            stopScanner();
                            formElem.submit();
                        }, 800);
                    } else {
                        statusElem.textContent = "QR Code tidak valid";
                        statusElem.className = "text-sm text-red-500 text-center mb-3";
                    }
                }
            } catch (error) {
                console.error("Error scanning file:", error);
                statusElem.textContent = "Gagal membaca QR dari gambar: " + (error.message || "Format tidak didukung");
                statusElem.className = "text-sm text-red-500 text-center mb-3";
            }

            // Reset file input
            fileInput.value = '';
        });
    </script>
</body>
</html>