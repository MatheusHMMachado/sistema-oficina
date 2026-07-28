<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Sem ->after(): modificador exclusivo do MySQL; PostgreSQL não suporta
            $table->string('role')->default('funcionario');
            $table->boolean('ativo')->default(true);
        });

        // Tornar o primeiro usuário admin (se existir)
        DB::table('users')->where('id', 1)->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'ativo']);
        });
    }
};
