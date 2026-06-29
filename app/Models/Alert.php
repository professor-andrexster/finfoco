<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public $timestamps = false;

    protected $fillable = ['categoria_id', 'limite_valor', 'periodo', 'ativo'];

    protected $casts = [
        'limite_valor' => 'decimal:2',
        'ativo'        => 'boolean',
        'created_at'   => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }
}
