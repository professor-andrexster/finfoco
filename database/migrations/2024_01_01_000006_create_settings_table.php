<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('chave', 50)->primary();
            $table->string('valor', 255)->nullable();
        });

        DB::table('settings')->insert([
            ['chave' => 'valor_hora',     'valor' => null],
            ['chave' => 'limite_impulso', 'valor' => '150.00'],
            ['chave' => 'visao_padrao',   'valor' => 'mensal'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
