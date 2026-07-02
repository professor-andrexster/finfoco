<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $assinante = $user->subscribed('default');
        $emTrial   = ! $assinante && $user->onTrial();

        return view('billing.index', [
            'assinante'     => $assinante,
            'emTrial'       => $emTrial,
            'diasRestantes' => $emTrial ? now()->diffInDays($user->trial_ends_at, false) : null,
        ]);
    }

    public function checkout(Request $request)
    {
        return $request->user()
            ->newSubscription('default', config('services.stripe.price_mensal'))
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('billing.index'),
            ]);
    }

    public function success(Request $request)
    {
        session(['billing_grace_until' => now()->addMinutes(5)]);

        return redirect()->route('dashboard')
            ->with('sucesso', 'Pagamento recebido! Sua assinatura está sendo confirmada.');
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('billing.index'));
    }
}
