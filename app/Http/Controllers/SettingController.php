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
        $metaDiaADia   = Setting::get('meta_dia_a_dia');

        return view('settings.index', compact('valorHora', 'limiteImpulso', 'metaDiaADia'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'valor_hora'     => 'nullable|numeric|min:0',
            'limite_impulso' => 'nullable|numeric|min:0',
            'meta_dia_a_dia' => 'nullable|numeric|min:0',
        ], [
            'valor_hora.numeric'     => 'Valor/hora deve ser numérico.',
            'valor_hora.min'         => 'Valor/hora deve ser positivo.',
            'limite_impulso.numeric' => 'Limite deve ser numérico.',
            'limite_impulso.min'     => 'Limite deve ser positivo.',
            'meta_dia_a_dia.numeric' => 'A meta deve ser numérica.',
            'meta_dia_a_dia.min'     => 'A meta deve ser positiva.',
        ]);

        Setting::set('valor_hora',     $request->valor_hora ?: null);
        Setting::set('limite_impulso', $request->limite_impulso ?: '150.00');
        Setting::set('meta_dia_a_dia', $request->meta_dia_a_dia ?: null);

        return redirect()->route('settings.show')
            ->with('sucesso', 'Configurações salvas!');
    }
}
