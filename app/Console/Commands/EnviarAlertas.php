<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\PushSubscription;
use App\Models\Routine;
use App\Models\Setting;
use App\Services\TelegramService;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnviarAlertas extends Command
{
    protected $signature = 'finfoco:alertas';

    protected $description = 'Envia alertas de compromissos e rotinas via Web Push e Telegram (janela de lembrete)';

    public function handle(WebPushService $push, TelegramService $telegram): int
    {
        $agora    = now();
        $hoje     = today()->toDateString();
        $enviados = 0;

        // Só usuários que têm algum canal fora do navegador aberto
        $userIdsPush     = PushSubscription::query()->distinct()->pluck('user_id');
        $chatsTelegram   = Setting::where('chave', 'telegram_chat_id')->pluck('valor', 'user_id');
        $userIds         = $userIdsPush->concat($chatsTelegram->keys())->unique()->values();

        foreach ($userIds as $userId) {
            $mensagens = [];

            // Compromissos com hora, não concluídos, dentro da janela de lembrete
            $compromissos = Appointment::where('user_id', $userId)
                ->whereDate('data', $hoje)
                ->whereNotNull('hora')
                ->where('concluido', false)
                ->get();

            foreach ($compromissos as $c) {
                $inicio = today()->setTimeFromTimeString($c->hora);
                $avisa  = $inicio->copy()->subMinutes($c->lembrete_min);
                if ($agora->between($avisa, $inicio) && Cache::add("alerta_c{$c->id}_{$hoje}", 1, 86400)) {
                    $falta = max(1, (int) $agora->diffInMinutes($inicio));
                    $mensagens[] = "⏰ " . substr($c->hora, 0, 5) . " — {$c->titulo} (em {$falta} min)";
                }
            }

            // Rotinas com hora, do dia, não feitas — aviso 10 min antes
            $rotinas = Routine::where('user_id', $userId)
                ->doDia(today())
                ->whereNotNull('hora')
                ->with(['checks' => fn($q) => $q->whereDate('data', $hoje)])
                ->get();

            foreach ($rotinas as $r) {
                if ($r->checks->isNotEmpty()) {
                    continue;
                }
                $inicio = today()->setTimeFromTimeString($r->hora);
                if ($agora->between($inicio->copy()->subMinutes(10), $inicio) && Cache::add("alerta_r{$r->id}_{$hoje}", 1, 86400)) {
                    $mensagens[] = "🔁 " . substr($r->hora, 0, 5) . " — {$r->titulo}";
                }
            }

            foreach ($mensagens as $m) {
                $push->enviarParaUsuario($userId, 'FinFoco — chegou a hora', $m, '/agenda');
                if ($chat = $chatsTelegram->get($userId)) {
                    $telegram->enviar($chat, "<b>FinFoco</b>\n{$m}\nUm passo de cada vez. 💜");
                }
                $enviados++;
            }
        }

        $this->info("Alertas enviados: {$enviados}");
        return self::SUCCESS;
    }
}
