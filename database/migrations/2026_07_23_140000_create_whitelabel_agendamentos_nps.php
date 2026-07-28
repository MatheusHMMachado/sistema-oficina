<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('nome_empresa')->default('Sistema de Oficina');
            $table->string('cor_primaria', 7)->default('#FF6B35');
            $table->string('cor_secundaria', 7)->default('#FF8C42');
            $table->string('tema', 10)->default('light'); // light | dark
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        DB::table('configuracoes_sistema')->insert([
            'nome_empresa' => 'Sistema de Oficina',
            'cor_primaria' => '#FF6B35',
            'cor_secundaria' => '#FF8C42',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->string('servico');
            $table->date('data_agendada');
            $table->string('hora', 5);
            $table->text('observacoes')->nullable();
            $table->string('status', 20)->default('pendente'); // pendente|aprovado|recusado|concluido
            $table->text('resposta_admin')->nullable();
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('respondido_em')->nullable();
            $table->timestamps();

            $table->index(['status', 'data_agendada']);
        });

        Schema::create('avaliacoes_nps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota'); // 0-10
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique('ordem_servico_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes_nps');
        Schema::dropIfExists('agendamentos');
        Schema::dropIfExists('configuracoes_sistema');
    }
};
