<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alertas = Alert::with('categoria')
            ->where('user_id', auth()->id())
            ->get();

        $alertasComGasto = $alertas->map(function ($alerta) {
            $inicio = match ($alerta->periodo) {
                'dia'    => Carbon::today(),
                'semana' => Carbon::now()->startOfWeek(),
                'mes'    => Carbon::now()->startOfMonth(),
            };
            $alerta->gasto_atual = Transaction::where('tipo', 'saida')
                ->where('user_id', auth()->id())
                ->where('categoria_id', $alerta->categoria_id)
                ->whereDate('data', '>=', $inicio)
                ->sum('valor');
            return $alerta;
        });

        return view('alerts.index', ['alertas' => $alertasComGasto]);
    }

    public function create()
    {
        $categorias = Category::disponiveis()->orderBy('nome')->get();
        return view('alerts.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria_id' => ['required', $this->categoriaDisponivel()],
            'limite_valor' => 'required|numeric|min:1',
            'periodo'      => 'required|in:dia,semana,mes',
        ], [
            'categoria_id.required' => 'Escolha uma categoria.',
            'limite_valor.required' => 'Informe o limite.',
        ]);

        $jaExiste = Alert::where('user_id', auth()->id())
            ->where('categoria_id', $data['categoria_id'])
            ->where('periodo', $data['periodo'])
            ->exists();

        if ($jaExiste) {
            return back()->withInput()
                ->withErrors(['categoria_id' => 'Já existe um alerta para esta categoria neste período.']);
        }

        Alert::create($data + ['user_id' => auth()->id(), 'ativo' => true]);
        return redirect()->route('alerts.index')->with('sucesso', 'Alerta criado!');
    }

    public function toggle(Alert $alert)
    {
        abort_unless($alert->user_id === auth()->id(), 403);
        $alert->update(['ativo' => !$alert->ativo]);
        return redirect()->route('alerts.index');
    }

    public function destroy(Alert $alert)
    {
        abort_unless($alert->user_id === auth()->id(), 403);
        $alert->delete();
        return redirect()->route('alerts.index')->with('sucesso', 'Alerta excluído!');
    }
}
