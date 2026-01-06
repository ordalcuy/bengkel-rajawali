<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    protected static ?string $title = 'Edit Pengguna';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->id === auth()->id()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load current role
        $data['role'] = $this->record->roles->first()?->name ?? 'kasir';
        return $data;
    }

    protected function afterSave(): void
    {
        // Sync role
        $role = $this->data['role'] ?? 'kasir';
        $this->record->syncRoles([$role]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengguna berhasil diperbarui!');
    }
}
