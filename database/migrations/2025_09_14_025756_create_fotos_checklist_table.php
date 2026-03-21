<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fotos_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists');
            $table->string('caminho_foto');
            $table->string('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fotos_checklist');
    }
};