<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Acumulador de pagamentos parciais: restante = valor - valor_pago.
        // Guarda hasColumn por causa do histórico de drift do banco de produção.
        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'valor_pago')) {
                $table->decimal('valor_pago', 10, 2)->default(0)->after('valor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('valor_pago');
        });
    }
};
