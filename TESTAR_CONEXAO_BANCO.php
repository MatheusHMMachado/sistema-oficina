<?php
/**
 * Script para testar a conexão com o banco de dados
 * Execute: php TESTAR_CONEXAO_BANCO.php
 */

echo "=== TESTE DE CONEXÃO COM BANCO DE DADOS ===\n\n";

// Carregar configurações do .env
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "❌ ERRO: Arquivo .env não encontrado!\n";
    exit(1);
}

$env = parse_ini_file($envPath);

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'sistema_oficina';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

echo "Configurações:\n";
echo "  Host: {$host}\n";
echo "  Porta: {$port}\n";
echo "  Banco: {$database}\n";
echo "  Usuário: {$username}\n";
echo "  Senha: " . (empty($password) ? '(vazia)' : '***') . "\n\n";

// Testar conexão
echo "Testando conexão...\n";

try {
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com MySQL estabelecida com sucesso!\n\n";
    
    // Verificar se o banco existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✅ Banco de dados '{$database}' existe!\n\n";
        
        // Conectar ao banco específico
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar tabelas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Tabelas encontradas (" . count($tables) . "):\n";
        foreach ($tables as $table) {
            echo "  ✓ {$table}\n";
        }
        
        // Verificar tabelas essenciais
        $essential = ['users', 'clientes', 'veiculos', 'ordens_servico'];
        $missing = [];
        
        foreach ($essential as $table) {
            if (!in_array($table, $tables)) {
                $missing[] = $table;
            }
        }
        
        if (!empty($missing)) {
            echo "\n⚠️  ATENÇÃO: Tabelas faltando:\n";
            foreach ($missing as $table) {
                echo "  ✗ {$table}\n";
            }
            echo "\nExecute: php artisan migrate\n";
        } else {
            echo "\n✅ Todas as tabelas essenciais estão presentes!\n";
        }
        
    } else {
        echo "❌ Banco de dados '{$database}' NÃO existe!\n";
        echo "\nCrie o banco no phpMyAdmin:\n";
        echo "1. Acesse: http://localhost/phpmyadmin\n";
        echo "2. Clique em 'Novo'\n";
        echo "3. Nome: {$database}\n";
        echo "4. Collation: utf8mb4_unicode_ci\n";
        echo "5. Clique em 'Criar'\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERRO na conexão:\n";
    echo "   " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "💡 SOLUÇÃO:\n";
        echo "   - Verifique se o usuário e senha estão corretos no .env\n";
        echo "   - Se não tem senha, deixe DB_PASSWORD= vazio\n";
        echo "   - Se tem senha, configure DB_PASSWORD=sua_senha no .env\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false || 
              strpos($e->getMessage(), 'No connection') !== false) {
        echo "💡 SOLUÇÃO:\n";
        echo "   - Verifique se o MySQL está rodando no XAMPP\n";
        echo "   - Abra o XAMPP Control Panel\n";
        echo "   - Clique em 'Start' ao lado de MySQL\n";
        echo "   - Aguarde até ficar verde (Running)\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "💡 SOLUÇÃO:\n";
        echo "   - Crie o banco de dados no phpMyAdmin\n";
        echo "   - Ou altere DB_DATABASE no .env para um banco existente\n";
    }
    
    exit(1);
}

echo "\n✅ Teste concluído com sucesso!\n";

