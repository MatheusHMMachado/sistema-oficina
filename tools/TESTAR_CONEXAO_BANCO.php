<?php
/**
 * Script para testar a conexão com PostgreSQL
 * Execute: php TESTAR_CONEXAO_BANCO.php
 */

echo "=== TESTE DE CONEXÃO COM POSTGRESQL ===\n\n";

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "❌ ERRO: Arquivo .env não encontrado!\n";
    exit(1);
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\"'");
}

$connection = $env['DB_CONNECTION'] ?? 'pgsql';
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '5432';
$database = $env['DB_DATABASE'] ?? 'sistema_oficina';
$username = $env['DB_USERNAME'] ?? 'postgres';
$password = $env['DB_PASSWORD'] ?? '';

echo "Configurações:\n";
echo "  Driver: {$connection}\n";
echo "  Host: {$host}\n";
echo "  Porta: {$port}\n";
echo "  Banco: {$database}\n";
echo "  Usuário: {$username}\n";
echo "  Senha: " . ($password === '' ? '(vazia)' : '***') . "\n\n";

if ($connection !== 'pgsql') {
    echo "⚠ DB_CONNECTION={$connection}. Este script espera pgsql.\n\n";
}

if (!extension_loaded('pdo_pgsql')) {
    echo "❌ Extensão PHP pdo_pgsql NÃO está habilitada.\n";
    echo "   Habilite extension=pdo_pgsql no php.ini e reinicie o Apache/PHP.\n";
    exit(1);
}

echo "Testando conexão...\n";

try {
    $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ Conexão com o servidor PostgreSQL OK!\n\n";

    $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :db');
    $stmt->execute(['db' => $database]);
    $exists = (bool) $stmt->fetchColumn();

    if (!$exists) {
        echo "❌ Banco '{$database}' NÃO existe.\n";
        echo "Crie com:\n";
        echo "  CREATE DATABASE {$database} WITH ENCODING 'UTF8' TEMPLATE template0;\n";
        echo "Ou via psql:\n";
        echo "  psql -U postgres -c \"CREATE DATABASE {$database};\"\n";
        exit(1);
    }

    echo "✅ Banco '{$database}' existe!\n\n";

    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")
        ->fetchAll(PDO::FETCH_COLUMN);

    echo 'Tabelas encontradas (' . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  ✓ {$table}\n";
    }

    $essential = ['users', 'clientes', 'veiculos', 'ordens_servico'];
    $missing = array_values(array_diff($essential, $tables));

    if (!empty($missing)) {
        echo "\n⚠️  Tabelas essenciais faltando:\n";
        foreach ($missing as $table) {
            echo "  ✗ {$table}\n";
        }
        echo "\nExecute: php artisan migrate --seed\n";
    } else {
        echo "\n✅ Tabelas essenciais presentes!\n";
    }
} catch (PDOException $e) {
    echo "❌ ERRO na conexão:\n";
    echo '   ' . $e->getMessage() . "\n\n";
    echo "💡 Verifique:\n";
    echo "   - Serviço PostgreSQL em execução\n";
    echo "   - Usuário/senha no .env\n";
    echo "   - Porta 5432 (padrão)\n";
    echo "   - Extensão pdo_pgsql no PHP\n";
    exit(1);
}

echo "\n✅ Teste concluído com sucesso!\n";
