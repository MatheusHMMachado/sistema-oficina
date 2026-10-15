<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Migrations padrão do Laravel que podem já existir
$defaultMigrations = [
    '2014_10_12_000000_create_users_table',
    '2014_10_12_100000_create_password_resets_table',
    '2019_08_19_000000_create_failed_jobs_table',
    '2019_12_14_000001_create_personal_access_tokens_table',
];

echo "Verificando migrations existentes...\n\n";

$batch = DB::table('migrations')->max('batch') ?? 0;
$batch++;

foreach ($defaultMigrations as $migration) {
    if (!DB::table('migrations')->where('migration', $migration)->exists()) {
        // Verificar se a tabela existe
        $tableName = str_replace(['create_', '_table'], '', $migration);
        $tableName = str_replace('_', '', $tableName);
        
        // Mapear nomes de tabelas
        $tableMap = [
            '2014_10_12_000000_create_users_table' => 'users',
            '2014_10_12_100000_create_password_resets_table' => 'password_resets',
            '2019_08_19_000000_create_failed_jobs_table' => 'failed_jobs',
            '2019_12_14_000001_create_personal_access_tokens_table' => 'personal_access_tokens',
        ];
        
        $tableName = $tableMap[$migration] ?? $tableName;
        
        try {
            // Verificar se a tabela existe
            $exists = DB::select("SHOW TABLES LIKE '{$tableName}'");
            if (!empty($exists)) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch
                ]);
                echo "✓ Migration {$migration} marcada como executada (tabela {$tableName} existe)\n";
            }
        } catch (\Exception $e) {
            echo "✗ Erro ao verificar {$migration}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Migration {$migration} já está registrada\n";
    }
}

echo "\nAgora execute: php artisan migrate\n";

