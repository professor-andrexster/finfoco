<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Category;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alertas = Alert::with('categoria')->orderBy('id', 'desc')->get();
        return view('alerts.index', compact('alertas'));
    }

    public function create()
    {
        $categorias = Category::where('tipo', '!=', 'entrada')->orderBy('nome')->get();
        return view('alerts.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categories,id'],
            'limite_valor' => ['required', 'numeric', 'min:0.01'],
            'periodo'      => ['required', 'in:dia,semana,mes'],
        ], [
            'categoria_id.required' => 'Escolha uma categoria.',
            'categoria_id.exists'   => 'Categoria inválida.',
            'limite_valor.required' => 'Informe o limite.',
            'limite_valor.min'      => 'O limite deve ser maior que zero.',
            'periodo.required'      => 'Escolha o período.',
        ]);

        $data['ativo'] = 1;
        Alert::create($data);

        return redirect()->route('alerts.index')->with('sucesso', 'Alerta criado!');
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();
        return redirect()->route('alerts.index')->with('sucesso', 'Alerta excluído!');
    }

    public function toggle(Alert $alert)
    {
        $alert->update(['ativo' => !$alert->ativo]);
        $msg = $alert->ativo ? 'Alerta ativado!' : 'Alerta pausado!';
        return redirect()->route('alerts.index')->with('sucesso', $msg);
    }
}
