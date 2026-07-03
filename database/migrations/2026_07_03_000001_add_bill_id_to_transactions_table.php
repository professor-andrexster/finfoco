<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Liga o pagamento à conta que o gerou — permite separar nos relatórios
        // o que é custo fixo (conta recorrente), conta avulsa/parcela e gasto
        // manual do dia a dia. nullOnDelete: excluir a conta não apaga o histórico.
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'bill_id')) {
                $table->foreignId('bill_id')->nullable()->after('categoria_id')
                      ->constrained('bills')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['bill_id']);
            $table->dropColumn('bill_id');
        });
    }
};
