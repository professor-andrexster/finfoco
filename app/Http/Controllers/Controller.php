<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

abstract class Controller
{
    /**
     * Regra de validação para categoria_id: só aceita categorias globais
     * (user_id NULL) ou do próprio usuário — nunca de outro usuário.
     */
    protected function categoriaDisponivel(): Exists
    {
        return Rule::exists('categories', 'id')->where(function ($q) {
            $q->where(function ($q2) {
                $q2->whereNull('user_id')->orWhere('user_id', auth()->id());
            });
        });
    }
}
