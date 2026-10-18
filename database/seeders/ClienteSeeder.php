<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        Cliente::firstOrCreate(
            ['cpf' => '12345678901'],
            [
                'nome' => 'João Silva Santos',
                'rg' => '1234567',
                'endereco' => 'Rua das Flores, 123 - Centro - Inhumas-GO',
                'telefone' => '(62) 3514-1234',
                'celular' => '(62) 99999-1234',
            ]
        );

        Cliente::firstOrCreate(
            ['cpf' => '98765432109'],
            [
                'nome' => 'Maria Oliveira Costa',
                'rg' => '9876543',
                'endereco' => 'Av. Brasil, 456 - Setor Norte - Inhumas-GO',
                'telefone' => '(62) 3514-5678',
                'celular' => '(62) 99999-5678',
            ]
        );
    }
}