<?php

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "ERRO: Arquivo .env não existe!\n";
    echo "Crie o arquivo .env na pasta sistema-oficina com as configurações do banco.\n";
    exit(1);
}

$envContent = file_get_contents($envPath);

echo "=== VERIFICAÇÃO DO ARQUIVO .env ===\n\n";

// Verificar configurações do banco
$required = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'sistema_oficina',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '', // Pode ser vazio
];

$missing = [];
$incorrect = [];

foreach ($required as $key => $default) {
    if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
        $value = trim($matches[1]);
        echo "✓ {$key} = " . ($key === 'DB_PASSWORD' && empty($value) ? '(vazio)' : $value) . "\n";
        
        if ($key === 'DB_DATABASE' && empty($value)) {
            $incorrect[] = $key;
        }
    } else {
        $missing[] = $key;
        echo "✗ {$key} = FALTANDO\n";
    }
}

echo "\n";

if (!empty($missing) || !empty($incorrect)) {
    echo "=== PROBLEMAS ENCONTRADOS ===\n\n";
    
    if (!empty($missing)) {
        echo "Configurações faltando:\n";
        foreach ($missing as $key) {
            echo "  - {$key}\n";
        }
        echo "\n";
    }
    
    if (!empty($incorrect)) {
        echo "Configurações incorretas:\n";
        foreach ($incorrect as $key) {
            echo "  - {$key} está vazio\n";
        }
        echo "\n";
    }
    
    echo "=== CONFIGURAÇÃO RECOMENDADA ===\n\n";
    echo "Adicione ou corrija estas linhas no arquivo .env:\n\n";
    
    foreach ($required as $key => $default) {
        if (in_array($key, $missing) || in_array($key, $incorrect)) {
            if ($key === 'DB_PASSWORD') {
                echo "{$key}=\n";
            } else {
                echo "{$key}={$default}\n";
            }
        }
    }
    
    echo "\n";
    echo "IMPORTANTE: Se você configurou uma senha para o MySQL, altere DB_PASSWORD=\n";
    echo "para DB_PASSWORD=sua_senha_aqui\n";
    
} else {
    echo "✓ Todas as configurações do banco estão presentes!\n";
    echo "\n";
    echo "Se ainda estiver dando erro, verifique:\n";
    echo "1. MySQL está rodando no XAMPP?\n";
    echo "2. O banco 'sistema_oficina' existe?\n";
    echo "3. A senha do MySQL está correta?\n";
}

echo "\n";

