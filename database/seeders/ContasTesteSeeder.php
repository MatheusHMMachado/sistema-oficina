<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Contas padronizadas para testes (painel admin, mecânico e portal do cliente).
 * Senha de todas as contas de teste: ver CREDENCIAIS_TESTE.md
 */
class ContasTesteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@oficina.com'],
            [
                'name' => 'Administrador Teste',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'ativo' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($admin->id !== 1) {
            // Garante que o primeiro admin continue com id 1 quando possível (regra do sistema)
            $existenteId1 = User::find(1);
            if ($existenteId1 && $existenteId1->email !== 'admin@oficina.com') {
                $this->command?->warn('Usuário id=1 já existe com outro e-mail; admin de teste ficou no id '.$admin->id);
            }
        }

        User::updateOrCreate(
            ['email' => 'mecanico@oficina.com'],
            [
                'name' => 'Mecânico Teste',
                'password' => Hash::make('mecanico123'),
                'role' => 'funcionario',
                'ativo' => true,
                'email_verified_at' => now(),
            ]
        );

        $cliente = Cliente::updateOrCreate(
            ['cpf' => '52998224725'],
            [
                'nome' => 'Cliente Portal Teste',
                'email' => 'cliente.teste@oficina.com',
                'celular' => '(62) 99999-0000',
                'endereco' => 'Rua Teste, 100 - Centro',
                'senha_portal' => Hash::make('cliente123'),
                'acesso_portal' => true,
                'notificar_email' => true,
            ]
        );

        Veiculo::updateOrCreate(
            [
                'cliente_id' => $cliente->id,
                'placa' => 'ABC1D23',
            ],
            [
                'marca' => 'Volkswagen',
                'modelo' => 'Gol',
                'cor' => 'Prata',
                'ano' => 2020,
                'km' => 45000,
            ]
        );

        $this->command?->info('');
        $this->command?->info('=== Contas de teste criadas/atualizadas ===');
        $this->command?->info('Painel ADMIN:     admin@oficina.com     / admin123');
        $this->command?->info('Painel MECÂNICO:  mecanico@oficina.com  / mecanico123');
        $this->command?->info('Portal CLIENTE:   CPF 529.982.247-25 ou e-mail cliente.teste@oficina.com / cliente123');
        $this->command?->info('Consulta pública: CPF 529.982.247-25 + placa ABC1D23');
        $this->command?->info('');
    }
}
