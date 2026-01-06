<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected static ?string $title = 'Tambah Pengguna';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Assign role to user
        $role = $this->data['role'] ?? 'kasir';
        $this->record->assignRole($role);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengguna berhasil dibuat!')
            ->body('Pengguna baru telah ditambahkan ke sistem.');
    }
}
