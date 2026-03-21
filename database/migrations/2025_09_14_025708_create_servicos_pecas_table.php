<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servicos_pecas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor_unitario', 10, 2);
            $table->enum('tipo', ['servico', 'peca']);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servicos_pecas');
    }
};