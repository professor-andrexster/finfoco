<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimentos;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAvisosVencimento extends Command
{
    protected $signature = 'finfoco:avisar-vencimentos';

    protected $description = 'Envia e-mail diário para usuários com contas atrasadas ou vencendo hoje/amanhã';

    public function handle(): int
    {
        $hoje   = today();
        $amanha = today()->addDay();
        $enviados = 0;

        User::query()->each(function (User $user) use ($hoje, $amanha, &$enviados) {
            $pendentes = Bill::where('user_id', $user->id)
                ->whereIn('status', ['pendente', 'atrasado'])
                ->whereDate('vencimento', '<=', $amanha)
                ->orderBy('vencimento')
                ->get();

            if ($pendentes->isEmpty()) {
                return;
            }

            $atrasadas    = $pendentes->filter(fn($b) => $b->vencimento->lt($hoje))->values();
            $vencemHoje   = $pendentes->filter(fn($b) => $b->vencimento->isSameDay($hoje))->values();
            $vencemAmanha = $pendentes->filter(fn($b) => $b->vencimento->isSameDay($amanha))->values();

            try {
                Mail::to($user->email)->send(new AvisoVencimentos($user, $atrasadas, $vencemHoje, $vencemAmanha));
                $enviados++;
            } catch (\Throwable $e) {
                // Um endereço com problema não pode impedir os avisos dos demais
                $this->error("Falha ao enviar para {$user->email}: {$e->getMessage()}");
            }
        });

        $this->info("Avisos de vencimento enviados: {$enviados}");
        return self::SUCCESS;
    }
}
