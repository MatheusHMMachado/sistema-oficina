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
            // string em vez de enum nativo MySQL — compatível com PostgreSQL
            $table->string('ponto_veiculo', 40);
            $table->text('observacao')->nullable();
            $table->string('condicao', 20);
            $table->timestamps();

            $table->index(['ordem_servico_id', 'ponto_veiculo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('checklists');
    }
};
