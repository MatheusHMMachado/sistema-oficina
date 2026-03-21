<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('nome');
            $table->string('senha_portal')->nullable()->after('email');
            $table->boolean('acesso_portal')->default(false)->after('senha_portal');
            $table->string('token_acesso')->nullable()->after('acesso_portal');
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

