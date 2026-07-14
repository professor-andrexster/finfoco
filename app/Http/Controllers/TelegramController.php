<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    /** Gera o deep link t.me/<bot>?start=<token> e manda o usuário pro Telegram. */
    public function conectar()
    {
        abort_unless(config('services.telegram.bot_username'), 404);

        $token = Str::random(32);
        Setting::set('telegram_connect_token', $token);

        return redirect()->away(
            'https://t.me/' . config('services.telegram.bot_username') . '?start=' . $token
        );
    }

    public function desconectar()
    {
        Setting::set('telegram_chat_id', null);

        return redirect()->route('settings.show')->with('sucesso', 'Telegram desconectado.');
    }

    /**
     * Webhook do bot (público, protegido pelo segredo na URL).
     * Espera "/start <token>" e vincula o chat ao usuário dono do token.
     */
    public function webhook(Request $request, string $segredo)
    {
        abort_unless(
            config('services.telegram.webhook_secret')
            && hash_equals(config('services.telegram.webhook_secret'), $segredo),
            403
        );

        $mensagem = $request->input('message', []);
        $texto    = trim($mensagem['text'] ?? '');
        $chatId   = $mensagem['chat']['id'] ?? null;

        if ($chatId && preg_match('/^\/start\s+(\S+)/', $texto, $m)) {
            $userId = Setting::where('chave', 'telegram_connect_token')
                ->where('valor', $m[1])
                ->value('user_id');

            $telegram = app(TelegramService::class);

            if ($userId) {
                // Setting::set usa auth(); aqui é webhook sem sessão — upsert direto
                Setting::query()->toBase()->upsert(
                    [['user_id' => $userId, 'chave' => 'telegram_chat_id', 'valor' => (string) $chatId,
                      'created_at' => now(), 'updated_at' => now()]],
                    ['user_id', 'chave'],
                    ['valor', 'updated_at']
                );
                Setting::where('user_id', $userId)->where('chave', 'telegram_connect_token')->delete();

                $telegram->enviar((string) $chatId,
                    "✅ <b>Telegram conectado ao Norte!</b>\nSeus alertas de compromissos e rotinas vão chegar aqui.");
            } else {
                $telegram->enviar((string) $chatId,
                    'Não achei esse código. Abra o Norte → Configurações → "Conectar Telegram" e tente de novo.');
            }
        }

        return response()->noContent();
    }
}
