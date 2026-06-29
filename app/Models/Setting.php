<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'chave';
    public    $incrementing = false;
    protected $keyType      = 'string';
    public    $timestamps   = false;

    protected $fillable = [
        'chave',
        'valor',
    ];

    public static function get(string $chave, mixed $default = null): mixed
    {
        $setting = static::find($chave);
        if ($setting === null || $setting->valor === null) {
            return $default;
        }
        return $setting->valor;
    }

    public static function set(string $chave, mixed $valor): void
    {
        static::updateOrCreate(
            ['chave' => $chave],
            ['valor' => $valor]
        );
    }
}
