<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class, // Criar usuário admin primeiro
            ServicoPecaSeeder::class,
            ClienteSeeder::class,
            VeiculoSeeder::class,
            // OrdemServicoSeeder::class, // Descomente se necessário
        ]);
    }
}