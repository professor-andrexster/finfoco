<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categories')->cascadeOnDelete();
            $table->decimal('limite_valor', 10, 2);
            $table->enum('periodo', ['dia', 'semana', 'mes'])->default('mes');
            $table->tinyInteger('ativo')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
