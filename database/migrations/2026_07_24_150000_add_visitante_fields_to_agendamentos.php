<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->string('nome_contato')->nullable()->after('cliente_id');
            $table->string('cpf_contato', 14)->nullable()->after('nome_contato');
            $table->string('telefone_contato', 20)->nullable()->after('cpf_contato');
            $table->string('email_contato')->nullable()->after('telefone_contato');
            $table->string('veiculo_info')->nullable()->after('veiculo_id');
            $table->boolean('eh_visitante')->default(false)->after('veiculo_info');
        });

        // PostgreSQL: tornar cliente_id opcional (visitantes)
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE agendamentos ALTER COLUMN cliente_id DROP NOT NULL');
        } else {
            Schema::table('agendamentos', function (Blueprint $table) {
                $table->foreignId('cliente_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn([
                'nome_contato',
                'cpf_contato',
                'telefone_contato',
                'email_contato',
                'veiculo_info',
                'eh_visitante',
            ]);
        });
    }
};
