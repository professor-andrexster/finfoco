<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\RoutineCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index()
    {
        $rotinas = Routine::where('user_id', auth()->id())
            ->with(['checks' => fn($q) => $q->whereDate('data', '>=', today()->subDays(400))])
            ->orderByRaw('hora IS NULL DESC')
            ->orderBy('hora')
            ->get();

        return view('routines.index', ['rotinas' => $rotinas]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|max:80',
            'hora'   => 'nullable|date_format:H:i',
            'dias'   => 'required|array|min:1',
            'dias.*' => 'integer|between:1,7',
        ], [
            'titulo.required' => 'Diga qual é a rotina.',
            'hora.date_format'=> 'Hora inválida.',
            'dias.required'   => 'Escolha pelo menos um dia da semana.',
            'dias.min'        => 'Escolha pelo menos um dia da semana.',
        ]);

        $dias = '';
        for ($d = 1; $d <= 7; $d++) {
            $dias .= in_array($d, array_map('intval', $request->dias)) ? '1' : '0';
        }

        Routine::create([
            'user_id' => auth()->id(),
            'titulo'  => $request->titulo,
            'hora'    => $request->hora,
            'dias'    => $dias,
        ]);

        return redirect()->route('routines.index')->with('sucesso', 'Rotina criada!');
    }

    public function destroy(Routine $routine)
    {
        abort_unless($routine->user_id === auth()->id(), 403);
        $routine->delete();

        return redirect()->route('routines.index')->with('sucesso', 'Rotina removida.');
    }

    /** Marca/desmarca a rotina como feita numa data (padrão: hoje). */
    public function check(Request $request, Routine $routine)
    {
        abort_unless($routine->user_id === auth()->id(), 403);

        try {
            $data = $request->filled('data') ? Carbon::parse($request->input('data'))->startOfDay() : today();
        } catch (\Throwable) {
            $data = today();
        }

        $existente = RoutineCheck::where('routine_id', $routine->id)
            ->whereDate('data', $data)
            ->first();

        $existente
            ? $existente->delete()
            : RoutineCheck::create(['routine_id' => $routine->id, 'data' => $data->toDateString()]);

        return redirect($request->input('voltar') === 'rotinas'
            ? route('routines.index')
            : route('agenda.index', ['data' => $data->toDateString()]));
    }
}
