<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function configurado(): bool
    {
        return (bool) config('services.telegram.bot_token');
    }

    public function enviar(string $chatId, string $texto): bool
    {
        if (!$this->configurado()) {
            return false;
        }

        try {
            $resposta = Http::timeout(6)->post(
                'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage',
                ['chat_id' => $chatId, 'text' => $texto, 'parse_mode' => 'HTML']
            );
            return $resposta->successful();
        } catch (\Throwable $e) {
            Log::warning("Telegram: falha ao enviar para {$chatId}: {$e->getMessage()}");
            return false;
        }
    }
}
