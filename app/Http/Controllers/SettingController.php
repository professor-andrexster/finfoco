<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        $valorHora     = Setting::get('valor_hora');
        $limiteImpulso = Setting::get('limite_impulso', '150.00');

        return view('settings.index', compact('valorHora', 'limiteImpulso'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'valor_hora'     => 'nullable|numeric|min:0',
            'limite_impulso' => 'nullable|numeric|min:0',
        ], [
            'valor_hora.numeric'     => 'Valor/hora deve ser numérico.',
            'valor_hora.min'         => 'Valor/hora deve ser positivo.',
            'limite_impulso.numeric' => 'Limite deve ser numérico.',
            'limite_impulso.min'     => 'Limite deve ser positivo.',
        ]);

        Setting::set('valor_hora',     $request->valor_hora ?: null);
        Setting::set('limite_impulso', $request->limite_impulso ?: '150.00');

        return redirect()->route('settings.show')
            ->with('sucesso', 'Configurações salvas!');
    }
}
