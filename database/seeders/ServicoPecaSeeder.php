<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicoPeca;

class ServicoPecaSeeder extends Seeder
{
    public function run()
    {
        $items = [
            // Serviços
            ['descricao' => 'Balanceamento', 'valor_unitario' => 25.00, 'tipo' => 'servico'],
            ['descricao' => 'Alinhamento', 'valor_unitario' => 40.00, 'tipo' => 'servico'],
            ['descricao' => 'Troca de óleo', 'valor_unitario' => 35.00, 'tipo' => 'servico'],
            ['descricao' => 'Revisão completa', 'valor_unitario' => 150.00, 'tipo' => 'servico'],
            ['descricao' => 'Troca de pastilhas de freio', 'valor_unitario' => 80.00, 'tipo' => 'servico'],
            
            // Peças
            ['descricao' => 'Óleo motor 5W30 - 1L', 'valor_unitario' => 35.00, 'tipo' => 'peca'],
            ['descricao' => 'Filtro de óleo', 'valor_unitario' => 15.00, 'tipo' => 'peca'],
            ['descricao' => 'Filtro de ar', 'valor_unitario' => 25.00, 'tipo' => 'peca'],
            ['descricao' => 'Pastilhas de freio dianteira', 'valor_unitario' => 120.00, 'tipo' => 'peca'],
            ['descricao' => 'Bateria 60Ah', 'valor_unitario' => 280.00, 'tipo' => 'peca'],
        ];

        foreach ($items as $item) {
            ServicoPeca::create($item);
        }
    }
}