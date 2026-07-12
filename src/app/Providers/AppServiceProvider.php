<?php

namespace App\Providers;

use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Page::formActionsAlignment(Alignment::Right);
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };
        MountableAction::configureUsing(function (MountableAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Right);
        });

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Password - Peta Wisata')
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Seseorang meminta reset password untuk akun yang terhubung dengan alamat e-mail ini. Jika ini memang Anda, klik tombol di bawah:')
                ->action('Reset Password', url(config('app.url').route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
                ->line('Link ini berlaku selama ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' menit. Setelah itu, Anda perlu meminta link baru.')
                ->line('Jika Anda tidak meminta reset password, abaikan e-mail ini.');
        });
    }
}
