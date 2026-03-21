<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico');
            $table->enum('ponto_veiculo', [
                'frente_esquerda',
                'frente_direita',
                'lateral_esquerda',
                'lateral_direita',
                'traseira_esquerda',
                'traseira_direita',
                'capot',
                'teto',
                'para_brisa',
                'vidro_traseiro',
                'interior',
                'motor'
            ]);
            $table->text('observacao')->nullable();
            $table->enum('condicao', ['otimo', 'bom', 'regular', 'ruim', 'pessimo']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('checklists');
    }
};