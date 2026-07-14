<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\FocusSession;
use Illuminate\Http\Request;

class FocoController extends Controller
{
    /** Registra a sessão de hiperfoco concluída (chamado via fetch ao zerar o timer). */
    public function registrarSessao(Request $request)
    {
        $request->validate([
            'titulo'  => 'nullable|string|max:80',
            'minutos' => 'required|integer|between:1,240',
        ]);

        FocusSession::create([
            'user_id' => auth()->id(),
            'titulo'  => $request->input('titulo'),
            'minutos' => $request->integer('minutos'),
        ]);

        return response()->noContent();
    }

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
