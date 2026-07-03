<?php

namespace App\Providers;

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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Backup diário disparado pelo tráfego: a Hostinger compartilhada não dá
        // crontab via SSH, então após responder uma requisição (terminating = não
        // atrasa o usuário) rodamos o dump se o último foi em outro dia. Lock
        // atômico no cache (driver file) evita dois backups simultâneos.
        $this->app->terminating(function () {
            if (!$this->app->isProduction() || $this->app->runningInConsole()) {
                return;
            }

            if (Cache::get('backup_diario_em') === today()->toDateString()) {
                return;
            }

            $lock = Cache::lock('backup_diario_lock', 300);
            if (!$lock->get()) {
                return;
            }

            try {
                if (Artisan::call('finfoco:backup') === 0) {
                    Cache::put('backup_diario_em', today()->toDateString());
                }
            } catch (\Throwable $e) {
                Log::error('Backup diário automático falhou: ' . $e->getMessage());
            } finally {
                $lock->release();
            }
        });
    }
}
