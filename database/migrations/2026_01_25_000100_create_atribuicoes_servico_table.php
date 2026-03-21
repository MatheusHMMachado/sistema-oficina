<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atribuicoes_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('servico_peca_id')->nullable()->constrained('servicos_pecas')->nullOnDelete();
            $table->foreignId('mecanico_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('atribuido_por')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('prazo');
            $table->enum('status', ['pendente', 'em_andamento', 'concluido', 'nao_concluido'])->default('pendente');
            $table->unsignedTinyInteger('progresso')->default(0);
            $table->text('observacao_mecanico')->nullable();
            $table->text('motivo_nao_conclusao')->nullable();
            $table->date('novo_prazo')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atribuicoes_servico');
    }
};
