<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 60);
            $table->date('data_lembrete');
            $table->tinyInteger('concluido')->default(0);
            // timestamps() completo: produção tem updated_at (drift de import manual)
            // e o model Reminder preenche created_at/updated_at — sem as duas colunas,
            // criar lembrete quebra em qualquer ambiente montado do zero
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
