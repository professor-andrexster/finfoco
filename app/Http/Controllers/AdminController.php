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

        $totalEmTrial = User::where('trial_ends_at', '>', now())
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->count();

        return view('admin.vendas', compact(
            'totalAssinantesAtivos',
            'ultimasAssinaturas',
            'totalEmTrial'
        ));
    }
}
