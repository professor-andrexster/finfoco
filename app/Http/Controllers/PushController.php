<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushController extends Controller
{
    /** Registra a assinatura de push do navegador (chamado via fetch, sem UI). */
    public function assinar(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required|url|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth'   => 'required|string|max:255',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $request->input('endpoint'))],
            [
                'user_id'  => auth()->id(),
                'endpoint' => $request->input('endpoint'),
                'p256dh'   => $request->input('keys.p256dh'),
                'auth'     => $request->input('keys.auth'),
            ]
        );

        return response()->noContent();
    }

    public function desassinar(Request $request)
    {
        $request->validate(['endpoint' => 'required|url|max:2000']);

        PushSubscription::where('user_id', auth()->id())
            ->where('endpoint_hash', hash('sha256', $request->input('endpoint')))
            ->delete();

        return response()->noContent();
    }
}
