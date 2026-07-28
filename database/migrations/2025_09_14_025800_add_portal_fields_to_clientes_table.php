<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Sem ->after(): modificador exclusivo do MySQL; PostgreSQL não suporta
            $table->string('email')->unique()->nullable();
            $table->string('senha_portal')->nullable();
            $table->boolean('acesso_portal')->default(false);
            $table->string('token_acesso')->nullable();
            $table->timestamp('ultimo_acesso')->nullable();
            $table->boolean('notificar_email')->default(true);
            $table->boolean('notificar_whatsapp')->default(false);
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['email', 'senha_portal', 'acesso_portal', 'token_acesso', 'ultimo_acesso', 'notificar_email', 'notificar_whatsapp']);
        });
    }
};
