<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Espelha o schema real de produção (chave 60, valor TEXT, timestamps),
        // que divergiu por import SQL manual — ver decisão de schema drift no ESTADO.md.
        Schema::create('settings', function (Blueprint $table) {
            $table->string('chave', 60)->primary();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
