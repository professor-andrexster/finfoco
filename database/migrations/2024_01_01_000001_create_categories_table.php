<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60);
            $table->string('cor', 7)->default('#6366F1');
            $table->string('icone', 50)->default('tag');
            $table->enum('tipo', ['entrada', 'saida', 'ambos'])->default('ambos');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
