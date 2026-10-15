<?php

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "ERRO: Arquivo .env não existe!\n";
    echo "Crie o arquivo .env na pasta sistema-oficina com as configurações do banco.\n";
    exit(1);
}

$envContent = file_get_contents($envPath);

echo "=== VERIFICAÇÃO DO ARQUIVO .env (PostgreSQL) ===\n\n";

$required = [
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '5432',
    'DB_DATABASE' => 'sistema_oficina',
    'DB_USERNAME' => 'postgres',
    'DB_PASSWORD' => '',
];

$missing = [];
$incorrect = [];

foreach ($required as $key => $default) {
    if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
        $value = trim($matches[1]);
        echo "✓ {$key} = " . ($key === 'DB_PASSWORD' && $value === '' ? '(vazio)' : $value) . "\n";

        if ($key === 'DB_DATABASE' && $value === '') {
            $incorrect[] = $key;
        }

        if ($key === 'DB_CONNECTION' && $value !== 'pgsql') {
            $incorrect[] = $key;
            echo "  ⚠ Esperado: pgsql\n";
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
            echo "  - {$key}\n";
        }
        echo "\n";
    }

    echo "=== CONFIGURAÇÃO RECOMENDADA (PostgreSQL) ===\n\n";
    foreach ($required as $key => $default) {
        echo "{$key}={$default}\n";
    }
    echo "\nDefina DB_PASSWORD com a senha do usuário postgres, se houver.\n";
} else {
    echo "✓ Configurações do banco presentes!\n\n";
    echo "Verifique também:\n";
    echo "1. Serviço PostgreSQL em execução\n";
    echo "2. Banco 'sistema_oficina' criado\n";
    echo "3. Extensão PHP pdo_pgsql habilitada\n";
    echo "4. Senha do usuário postgres correta no .env\n";
}

echo "\n";
