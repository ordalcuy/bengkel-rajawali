<?php

namespace App\Policies;

use App\Models\Antrean;
use App\Models\User;

class AntreanPolicy
{
    /**
     * Izinkan Owner untuk melakukan semuanya.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('owner')) {
            return true;
        }

        return null;
    }

    /**
     * Siapa yang bisa melihat daftar antrean.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'kasir']);
    }

    /**
     * Siapa yang bisa melihat detail satu antrean.
     */
    public function view(User $user, Antrean $antrean): bool
    {
        return $user->hasAnyRole(['owner', 'kasir']);
    }

    /**
     * Siapa yang bisa membuat antrean baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('kasir');
    }

    /**
     * Siapa yang bisa mengedit/mengubah data antrean.
     * INI ADALAH BAGIAN TERPENTING UNTUK MASALAH ANDA.
     */
    public function update(User $user, Antrean $antrean): bool
    {
        // Izinkan Owner dan Kasir untuk mengubah data
        return $user->hasAnyRole(['owner', 'kasir']);
    }

    /**
     * Siapa yang bisa menghapus antrean.
     */
    public function delete(User $user, Antrean $antrean): bool
    {
        // Hanya owner yang boleh menghapus
        return $user->hasRole('owner');
    }
}