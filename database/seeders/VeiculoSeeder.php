<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Veiculo;
use App\Models\Cliente;

class VeiculoSeeder extends Seeder
{
    public function run()
    {
        $cliente1 = Cliente::where('cpf', '12345678901')->first() ?? Cliente::first();
        $cliente2 = Cliente::where('cpf', '98765432109')->first() ?? Cliente::skip(1)->first() ?? $cliente1;

        if ($cliente1) {
            Veiculo::firstOrCreate(
                ['placa' => 'ABC1234'],
                [
                    'cliente_id' => $cliente1->id,
                    'marca' => 'Volkswagen',
                    'modelo' => 'Gol',
                    'cor' => 'Prata',
                    'ano' => 2018,
                    'chassi' => '9BWZZZ377VT004251',
                    'km' => 68231,
                ]
            );
        }

        if ($cliente2) {
            Veiculo::firstOrCreate(
                ['placa' => 'DEF5678'],
                [
                    'cliente_id' => $cliente2->id,
                    'marca' => 'Chevrolet',
                    'modelo' => 'Onix',
                    'cor' => 'Branco',
                    'ano' => 2020,
                    'chassi' => '9BGKS08A0LG123456',
                    'km' => 45000,
                ]
            );
        }
    }
}