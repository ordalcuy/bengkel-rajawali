<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntreanPrintController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\AntreanLacakController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WaitingListController;

Route::get('/test-session', function() {
    session(['test' => 'works']);
    return response()->json([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'user' => Auth::check() ? Auth::user()->email : 'Not logged in'
    ]);
});

// ==================== ROUTE UTAMA ====================
// Halaman utama dengan fitur lacak antrean
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Form lacak antrean dari halaman utama
Route::post('/lacak', [WelcomeController::class, 'lacak'])->name('lacak.submit');

// Menampilkan hasil lacak antrean via QR Code atau direct link
Route::get('/lacak/{nomor_antrean}', [WelcomeController::class, 'showLacak'])->name('lacak.show');

// Lacak via ID (untuk QR Code - UNIK, tidak ada duplikasi)
Route::get('/lacak-id/{id}', [WelcomeController::class, 'showLacakById'])->name('lacak.show.id');

// ==================== API UNTUK REAL-TIME DATA ====================
Route::get('/api/antrean-aktif', [WelcomeController::class, 'getAntreanAktif']);
Route::get('/api/statistik', [WelcomeController::class, 'getStatistik']);

// ==================== FITUR LAINNYA ====================
// Cetak antrean
Route::get('/antrean/{antrean}/cetak', [AntreanPrintController::class, '__invoke'])
    ->name('antrean.cetak');

// Waiting list
Route::get('/waiting-list', [WaitingListController::class, 'index'])->name('waiting-list');

// Display antrean
Route::get('/display', [DisplayController::class, 'index'])->name('display');

// ==================== ROUTE LAMA (BACKUP - bisa dihapus nanti) ====================
// Route lacak lama (untuk kompatibilitas)
Route::get('/lacak-old', [AntreanLacakController::class, 'index'])->name('lacak.index-old');
Route::post('/lacak-search', [AntreanLacakController::class, 'search'])->name('lacak.search-old');


Route::get('/api/antrean-aktif', [WelcomeController::class, 'getAntreanAktif']);
Route::get('/api/statistik', [WelcomeController::class, 'getStatistik']);