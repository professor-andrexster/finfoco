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
            'data_lembrete.required' => 'A data é obrigatória.',
        ]);

        Reminder::create([
            'user_id'       => auth()->id(),
            'titulo'        => $request->titulo,
            'data_lembrete' => $request->data_lembrete,
            'concluido'     => false,
        ]);

        return redirect()->route('dashboard')->with('sucesso', 'Lembrete adicionado!');
    }

    public function toggle(Reminder $reminder)
    {
        abort_unless($reminder->user_id === auth()->id(), 403);
        $reminder->update(['concluido' => !$reminder->concluido]);
        return redirect()->route('dashboard');
    }

    public function destroy(Reminder $reminder)
    {
        abort_unless($reminder->user_id === auth()->id(), 403);
        $reminder->delete();
        return redirect()->route('dashboard')->with('sucesso', 'Lembrete removido.');
    }
}
