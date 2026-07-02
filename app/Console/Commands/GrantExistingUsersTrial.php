<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantExistingUsersTrial extends Command
{
    protected $signature = 'users:grant-trial {--days=14}';

    protected $description = 'Concede dias de acesso gratuito aos usuários existentes que ainda não têm trial nem assinatura (rodar uma vez, após o deploy da cobrança via Stripe)';

    public function handle(): int
    {
        $dias = (int) $this->option('days');

        $afetados = User::whereNull('trial_ends_at')
            ->whereNull('stripe_id')
            ->update(['trial_ends_at' => now()->addDays($dias)]);

        $this->info("{$afetados} usuário(s) receberam {$dias} dias de acesso.");

        return self::SUCCESS;
    }
}
