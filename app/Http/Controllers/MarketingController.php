<?php

namespace App\Http\Controllers;

class MarketingController extends Controller
{
    /**
     * Landing pública na raiz, visível também para usuários autenticados.
     */
    public function home()
    {
        return view('marketing.home');
    }
}
