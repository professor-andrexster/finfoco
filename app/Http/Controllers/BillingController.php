<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $assinante = $user->subscribed('default');
        $emTrial   = ! $assinante && ! $user->lifetime_access && $user->onTrial();

        return view('billing.index', [
            'assinante'      => $assinante,
            'vitalicio'      => $user->lifetime_access,
            'emTrial'        => $emTrial,
            'diasRestantes'  => $emTrial ? now()->diffInDays($user->trial_ends_at, false) : null,
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string',
        ], [
            'codigo.required' => 'Informe o código.',
        ]);

        $codigoValido = config('services.stripe.lifetime_access_code');

        if (! $codigoValido || ! hash_equals($codigoValido, trim($data['codigo']))) {
            return redirect()->route('billing.index')
                ->with('erro', 'Código inválido.');
        }

        $request->user()->lifetime_access = true;
        $request->user()->save();

        return redirect()->route('billing.index')
            ->with('sucesso', 'Acesso vitalício liberado! Obrigado.');
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
