<?php

namespace App\Console\Commands;

use App\Mail\AgendaDoDia;
use App\Models\Appointment;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAgendaDoDia extends Command
{
    protected $signature = 'finfoco:agenda-do-dia';

    protected $description = 'Envia e-mail matinal "Seu dia hoje" para usuários com compromissos ou rotinas no dia';

    public function handle(): int
    {
        $hoje     = today();
        $enviados = 0;

        User::query()->each(function (User $user) use ($hoje, &$enviados) {
            $compromissos = Appointment::where('user_id', $user->id)
                ->doDia($hoje)
                ->where('concluido', false)
                ->get();

            $rotinas = Routine::where('user_id', $user->id)
                ->doDia($hoje)
                ->with(['checks' => fn($q) => $q->whereDate('data', '>=', today()->subDays(400))])
                ->get();

            if ($compromissos->isEmpty() && $rotinas->isEmpty()) {
                return;
            }

            try {
                Mail::to($user->email)->send(new AgendaDoDia($user, $compromissos, $rotinas));
                $enviados++;
            } catch (\Throwable $e) {
                // Um endereço com problema não pode impedir os avisos dos demais
                $this->error("Falha ao enviar para {$user->email}: {$e->getMessage()}");
            }
        });

        $this->info("Resumos do dia enviados: {$enviados}");
        return self::SUCCESS;
    }
}
