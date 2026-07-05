<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /** Roda um comando artisan no máximo 1x por dia, com lock atômico contra concorrência. */
    private function rodarRotinaDiaria(string $nome, string $comando): void
    {
        if (Cache::get("{$nome}_em") === today()->toDateString()) {
            return;
        }

        $lock = Cache::lock("{$nome}_lock", 300);
        if (!$lock->get()) {
            return;
        }

        try {
            if (Artisan::call($comando) === 0) {
                Cache::put("{$nome}_em", today()->toDateString());
            }
        } catch (\Throwable $e) {
            Log::error("Rotina diária {$nome} falhou: " . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // E-mail de redefinição de senha em pt-BR (o padrão do Laravel é em inglês)
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('FinFoco — Redefinir sua senha')
                ->greeting('Olá!')
                ->line('Recebemos um pedido pra redefinir a senha da sua conta no FinFoco.')
                ->action('Criar nova senha', $url)
                ->line('O link vale por 60 minutos. Se não foi você, ignore este e-mail — nada muda.')
                ->salutation('Equipe FinFoco');
        });

        // Backup diário disparado pelo tráfego: a Hostinger compartilhada não dá
        // crontab via SSH, então após responder uma requisição (terminating = não
        // atrasa o usuário) rodamos o dump se o último foi em outro dia. Lock
        // atômico no cache (driver file) evita dois backups simultâneos.
        $this->app->terminating(function () {
            if (!$this->app->isProduction() || $this->app->runningInConsole()) {
                return;
            }

            $this->rodarRotinaDiaria('backup_diario', 'finfoco:backup');
            $this->rodarRotinaDiaria('avisos_vencimento', 'finfoco:avisar-vencimentos');
        });
    }
}
