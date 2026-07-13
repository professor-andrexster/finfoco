<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('routines')) {
            Schema::create('routines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('titulo', 80);
                $table->time('hora')->nullable();          // null = qualquer hora do dia
                $table->char('dias', 7)->default('1111111'); // seg..dom, '1' = ativo no dia
                $table->timestamps();

                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('routine_checks')) {
            Schema::create('routine_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
                $table->date('data');
                $table->timestamps();

                $table->unique(['routine_id', 'data']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_checks');
        Schema::dropIfExists('routines');
    }
};
