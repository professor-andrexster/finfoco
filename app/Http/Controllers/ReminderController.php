<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'titulo'        => 'required|max:60',
            'data_lembrete' => 'required|date',
        ], [
            'titulo.required'        => 'O título é obrigatório.',
            'titulo.max'             => 'Máximo 60 caracteres.',
            'data_lembrete.required' => 'A data é obrigatória.',
            'data_lembrete.date'     => 'Data inválida.',
        ]);

        Reminder::create([
            'titulo'        => $request->titulo,
            'data_lembrete' => $request->data_lembrete,
            'concluido'     => false,
        ]);

        return redirect()->route('dashboard')
            ->with('sucesso', 'Lembrete adicionado!');
    }

    public function toggle(Reminder $reminder)
    {
        $reminder->update(['concluido' => ! $reminder->concluido]);
        return redirect()->route('dashboard');
    }

    public function destroy(Reminder $reminder)
    {
        $reminder->delete();
        return redirect()->route('dashboard')
            ->with('sucesso', 'Lembrete removido.');
    }
}
