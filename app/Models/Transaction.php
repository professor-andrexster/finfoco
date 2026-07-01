<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'tipo', 'valor', 'descricao', 'categoria_id', 'data'];
    protected $casts    = ['data' => 'date', 'valor' => 'decimal:2'];

    public function categoria() { return $this->belongsTo(Category::class, 'categoria_id'); }
    public function user()      { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}
