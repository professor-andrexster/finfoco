<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps  = false;
    protected $fillable = ['user_id', 'nome', 'cor', 'icone', 'tipo'];

    public function transactions() { return $this->hasMany(Transaction::class, 'categoria_id'); }
    public function alerts()       { return $this->hasMany(Alert::class, 'categoria_id'); }
    public function user()         { return $this->belongsTo(User::class); }

    // Categorias disponíveis = globais (null) + do usuário atual
    public static function disponiveis()
    {
        return static::where(function ($q) {
            $q->whereNull('user_id');
            if (auth()->check()) {
                $q->orWhere('user_id', auth()->id());
            }
        });
    }
}
