<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'cor', 'icone', 'tipo'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'categoria_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'categoria_id');
    }
}
