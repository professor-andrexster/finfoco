<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['pagar', 'receber'])->notNull();
            $table->string('descricao', 60);
            $table->decimal('valor', 10, 2);
            $table->foreignId('categoria_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->date('vencimento');
            $table->enum('status', ['pendente', 'pago', 'recebido', 'atrasado'])->default('pendente');
            $table->tinyInteger('recorrente')->default(0);
            $table->enum('recorrencia', ['mensal', 'semanal', 'anual'])->nullable();
            $table->date('pago_em')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
