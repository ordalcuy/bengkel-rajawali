<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    // 🔴 PERBAIKAN: Hapus 'remember' dari credentials
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    // Ubah form untuk menampilkan field 'email'
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->required()
                    ->email()
                    ->autofocus()
                    ->autocomplete('email'),
                $this->getPasswordFormComponent()->label('Kata Sandi'),
                $this->getRememberFormComponent()->label('Ingat saya'),
            ]);
    }

    public function getTitle(): string
    {
        return 'Rajawali Bengkel';
    }

    public function getHeading(): string
    {
        return 'Masuk';
    }
}