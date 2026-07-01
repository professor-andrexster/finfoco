<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = null;
    public    $incrementing = false;
    public    $timestamps   = true;
    protected $fillable     = ['user_id', 'chave', 'valor'];

    public static function get(string $chave, mixed $default = null): mixed
    {
        $userId = auth()->id();
        return static::where('user_id', $userId)->where('chave', $chave)->value('valor') ?? $default;
    }

    public static function set(string $chave, mixed $valor): void
    {
        $userId = auth()->id();
        if ($valor === null) {
            static::where('user_id', $userId)->where('chave', $chave)->delete();
            return;
        }
        static::updateOrCreate(
            ['user_id' => $userId, 'chave' => $chave],
            ['valor' => $valor]
        );
    }
}
