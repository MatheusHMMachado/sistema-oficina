<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->string('placa_veiculo', 10)->nullable()->after('veiculo_info');
            $table->string('marca_veiculo')->nullable()->after('placa_veiculo');
            $table->string('modelo_veiculo')->nullable()->after('marca_veiculo');
            $table->unsignedSmallInteger('ano_veiculo')->nullable()->after('modelo_veiculo');
            $table->string('cor_veiculo')->nullable()->after('ano_veiculo');
            $table->unsignedInteger('km_veiculo')->nullable()->after('cor_veiculo');
            $table->string('chassi_veiculo')->nullable()->after('km_veiculo');
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn([
                'placa_veiculo',
                'marca_veiculo',
                'modelo_veiculo',
                'ano_veiculo',
                'cor_veiculo',
                'km_veiculo',
                'chassi_veiculo',
            ]);
        });
    }
};
