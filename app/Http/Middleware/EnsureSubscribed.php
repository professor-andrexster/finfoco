<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->subscribed('default') || $user->onTrial()) {
            return $next($request);
        }

        if (session('billing_grace_until') && now()->lt(session('billing_grace_until'))) {
            return $next($request);
        }

        return redirect()->route('billing.index')
            ->with('erro', 'Seu período gratuito acabou. Assine para continuar usando o FinFoco.');
    }
}
