<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criar usuário admin padrão
        User::firstOrCreate(
            ['email' => 'admin@oficina.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@oficina.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'ativo' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuário admin criado com sucesso!');
        $this->command->info('Email: admin@oficina.com');
        $this->command->info('Senha: admin123');
        $this->command->warn('⚠️  IMPORTANTE: Altere a senha após o primeiro login!');
    }
}


