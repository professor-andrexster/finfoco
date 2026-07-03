<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // categories: nullable (null = categoria global/template do sistema)
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained()->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained()->onDelete('cascade');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained()->onDelete('cascade');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained()->onDelete('cascade');
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained()->onDelete('cascade');
        });

        // settings: trocar PK para (user_id, chave)
        // Linhas legadas da era single-user (sem user_id) impedem a nova PK NOT NULL
        // em ambientes criados do zero — em produção a tabela já foi migrada com drift.
        \Illuminate\Support\Facades\DB::table('settings')->delete();

        Schema::table('settings', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unsignedBigInteger('user_id')->nullable()->after('chave');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['user_id', 'chave']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropPrimary();
            $table->primary('chave');
        });

        foreach (['reminders', 'bills', 'alerts', 'transactions', 'categories'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
