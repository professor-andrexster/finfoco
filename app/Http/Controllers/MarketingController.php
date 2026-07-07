<?php

namespace App\Http\Controllers;

class MarketingController extends Controller
{
    /**
     * Landing pública na raiz. Usuário autenticado vai direto ao painel.
     */
    public function home()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('marketing.home');
    }
}
