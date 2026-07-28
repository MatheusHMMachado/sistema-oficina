<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicos_pecas', function (Blueprint $table) {
            $table->unsignedInteger('quantidade_estoque')->default(0)->after('ativo');
            $table->unsignedInteger('estoque_minimo')->default(0)->after('quantidade_estoque');
        });

        Schema::table('itens_ordem_servico', function (Blueprint $table) {
            $table->boolean('estoque_baixado')->default(false)->after('valor_total');
        });

        Schema::table('atribuicoes_servico', function (Blueprint $table) {
            $table->string('reagendamento_status', 20)->nullable()->after('novo_prazo');
            $table->timestamp('alerta_prazo_em')->nullable()->after('reagendamento_status');
            $table->timestamp('reagendamento_respondido_em')->nullable()->after('alerta_prazo_em');
        });

        // prazo passa a ser data/hora (SLA)
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE atribuicoes_servico ALTER COLUMN prazo TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING prazo::timestamp');
            DB::statement('ALTER TABLE atribuicoes_servico ALTER COLUMN novo_prazo TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING novo_prazo::timestamp');
        } else {
            Schema::table('atribuicoes_servico', function (Blueprint $table) {
                $table->dateTime('prazo')->change();
                $table->dateTime('novo_prazo')->nullable()->change();
            });
        }

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('titulo');
            $table->text('mensagem');
            $table->string('link')->nullable();
            $table->boolean('lida')->default(false);
            $table->nullableMorphs('relacionado');
            $table->timestamps();

            $table->index(['user_id', 'lida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');

        Schema::table('atribuicoes_servico', function (Blueprint $table) {
            $table->dropColumn([
                'reagendamento_status',
                'alerta_prazo_em',
                'reagendamento_respondido_em',
            ]);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE atribuicoes_servico ALTER COLUMN prazo TYPE DATE USING prazo::date');
            DB::statement('ALTER TABLE atribuicoes_servico ALTER COLUMN novo_prazo TYPE DATE USING novo_prazo::date');
        }

        Schema::table('itens_ordem_servico', function (Blueprint $table) {
            $table->dropColumn('estoque_baixado');
        });

        Schema::table('servicos_pecas', function (Blueprint $table) {
            $table->dropColumn(['quantidade_estoque', 'estoque_minimo']);
        });
    }
};
