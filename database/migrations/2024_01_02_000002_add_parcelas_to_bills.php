<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->unsignedInteger('parcelas_total')->nullable()->after('recorrencia');
            $table->unsignedInteger('parcela_atual')->default(1)->after('parcelas_total');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['parcelas_total', 'parcela_atual']);
        });
    }
};
