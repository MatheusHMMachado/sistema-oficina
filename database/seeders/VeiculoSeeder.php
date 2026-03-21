<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Veiculo;

class VeiculoSeeder extends Seeder
{
    public function run()
    {
        Veiculo::create([
            'cliente_id' => 1,
            'marca' => 'Volkswagen',
            'modelo' => 'Gol',
            'cor' => 'Prata',
            'ano' => 2018,
            'placa' => 'ABC1234',
            'chassi' => '9BWZZZ377VT004251',
            'km' => 68231,
        ]);

        Veiculo::create([
            'cliente_id' => 2,
            'marca' => 'Chevrolet',
            'modelo' => 'Onix',
            'cor' => 'Branco',
            'ano' => 2020,
            'placa' => 'DEF5678',
            'chassi' => '9BGKS08A0LG123456',
            'km' => 45000,
        ]);
    }
}