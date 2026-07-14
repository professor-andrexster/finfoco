<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

class FocoController extends Controller
{
    public function index()
    {
        // Sugestões: compromissos de hoje ainda não concluídos (com passos pendentes)
        $sugestoes = Appointment::where('user_id', auth()->id())
            ->doDia(today())
            ->where('concluido', false)
            ->with(['steps' => fn($q) => $q->where('concluido', false)])
            ->get();

        return view('foco.index', ['sugestoes' => $sugestoes]);
    }
}
