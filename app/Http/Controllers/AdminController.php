<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Cashier\Subscription;

class AdminController extends Controller
{
    public function vendas()
    {
        $totalAssinantesAtivos = User::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->count();

        $ultimasAssinaturas = Subscription::with('user')
            ->latest('created_at')
            ->take(20)
            ->get();

        $usuariosEmTrial = User::where('trial_ends_at', '>', now())
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->orderBy('created_at')
            ->get(['name', 'email', 'created_at', 'trial_ends_at']);

        $totalEmTrial = $usuariosEmTrial->count();

        return view('admin.vendas', compact(
            'totalAssinantesAtivos',
            'ultimasAssinaturas',
            'totalEmTrial',
            'usuariosEmTrial'
        ));
    }
}
