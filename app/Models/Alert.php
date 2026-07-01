<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public $timestamps  = false;
    protected $fillable = ['user_id', 'categoria_id', 'limite_valor', 'periodo', 'ativo'];
    protected $casts    = ['ativo' => 'boolean', 'limite_valor' => 'decimal:2'];

    public function categoria() { return $this->belongsTo(Category::class, 'categoria_id'); }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}
