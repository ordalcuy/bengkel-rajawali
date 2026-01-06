<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Antrean; // <-- TAMBAHKAN INI
use App\Policies\AntreanPolicy; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\View; // <-- Tambahkan ini
use Filament\Facades\Filament; // <-- Tambahkan ini
use Carbon\Carbon; // <-- Tambahkan ini
use Illuminate\Support\Facades\Blade; // <-- TAMBAHKAN INI
use Illuminate\Routing\UrlGenerator; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
     protected $policies = [
        Antrean::class => AntreanPolicy::class, // <-- Pastikan baris ini ada
    ];

    public function register(): void
    {
        //
    }

    

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void // <-- Tambahkan parameter $url
    {
        // Kode untuk deploy di Render (paksa HTTPS)
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }

        // Kode Anda yang sudah ada sebelumnya
        Filament::registerRenderHook(
            'panels::body.end',
            fn (): string => View::make('scripts.tts-player')->render(),
        );

        Filament::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render("@vite(['resources/js/app.js'])"),
        );

        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8');
    }

    
}
