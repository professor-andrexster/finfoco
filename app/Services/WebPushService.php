<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function configurado(): bool
    {
        return (bool) (config('services.webpush.public_key') && config('services.webpush.private_key'));
    }

    /** Envia uma notificação para todos os dispositivos do usuário. */
    public function enviarParaUsuario(int $userId, string $titulo, string $corpo, string $url = '/agenda'): void
    {
        if (!$this->configurado()) {
            return;
        }

        $assinaturas = PushSubscription::where('user_id', $userId)->get();
        if ($assinaturas->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('services.webpush.subject'),
                    'publicKey'  => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ]);

            $payload = json_encode(['title' => $titulo, 'body' => $corpo, 'url' => $url]);

            foreach ($assinaturas as $a) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $a->endpoint,
                    'keys'     => ['p256dh' => $a->p256dh, 'auth' => $a->auth],
                ]), $payload);
            }

            foreach ($webPush->flush() as $relatorio) {
                // Assinatura morta (navegador desinstalou/expirou): limpa do banco
                if (!$relatorio->isSuccess() && in_array($relatorio->getResponse()?->getStatusCode(), [404, 410], true)) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $relatorio->getEndpoint()))->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning("WebPush: falha ao enviar para user {$userId}: {$e->getMessage()}");
        }
    }
}
