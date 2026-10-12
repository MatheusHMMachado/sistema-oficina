<?php
/**
 * Insere/atualiza contas de teste sem depender do artisan (útil se PHP/Composer bloquear).
 * Uso: php database/scripts/seed_contas_teste.php
 */

$root = dirname(__DIR__, 2);
$envFile = $root . DIRECTORY_SEPARATOR . '.env';
if (! is_readable($envFile)) {
    fwrite(STDERR, "Arquivo .env não encontrado em {$root}\n");
    exit(1);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\"'");
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '5432';
$db = $env['DB_DATABASE'] ?? 'sistema_oficina';
$user = $env['DB_USERNAME'] ?? 'postgres';
$pass = $env['DB_PASSWORD'] ?? '';

$dsn = "pgsql:host={$host};port={$port};dbname={$db}";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro ao conectar no PostgreSQL: ' . $e->getMessage() . "\n");
    exit(1);
}

$now = date('Y-m-d H:i:s');
$hashAdmin = password_hash('admin123', PASSWORD_BCRYPT);
$hashMec = password_hash('mecanico123', PASSWORD_BCRYPT);
$hashCliente = password_hash('cliente123', PASSWORD_BCRYPT);

$pdo->beginTransaction();

// Admin
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute(['admin@oficina.com']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $pdo->prepare('UPDATE users SET name = ?, password = ?, role = ?, ativo = true, updated_at = ? WHERE id = ?')
        ->execute(['Administrador Teste', $hashAdmin, 'admin', $now, $row['id']]);
} else {
    $pdo->prepare('INSERT INTO users (name, email, password, role, ativo, email_verified_at, created_at, updated_at) VALUES (?,?,?,?,true,?,?,?)')
        ->execute(['Administrador Teste', 'admin@oficina.com', $hashAdmin, 'admin', $now, $now, $now]);
}

// Mecânico
$stmt->execute(['mecanico@oficina.com']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $pdo->prepare('UPDATE users SET name = ?, password = ?, role = ?, ativo = true, updated_at = ? WHERE id = ?')
        ->execute(['Mecânico Teste', $hashMec, 'funcionario', $now, $row['id']]);
} else {
    $pdo->prepare('INSERT INTO users (name, email, password, role, ativo, email_verified_at, created_at, updated_at) VALUES (?,?,?,?,true,?,?,?)')
        ->execute(['Mecânico Teste', 'mecanico@oficina.com', $hashMec, 'funcionario', $now, $now, $now]);
}

// Cliente portal
$cpf = '52998224725';
$stmt = $pdo->prepare('SELECT id FROM clientes WHERE cpf = ? LIMIT 1');
$stmt->execute([$cpf]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $clienteId = (int) $row['id'];
    $pdo->prepare('UPDATE clientes SET nome = ?, email = ?, senha_portal = ?, acesso_portal = true, updated_at = ? WHERE id = ?')
        ->execute(['Cliente Portal Teste', 'cliente.teste@oficina.com', $hashCliente, $now, $clienteId]);
} else {
    $pdo->prepare('INSERT INTO clientes (nome, cpf, email, celular, endereco, senha_portal, acesso_portal, notificar_email, created_at, updated_at) VALUES (?,?,?,?,?,?,true,true,?,?)')
        ->execute(['Cliente Portal Teste', $cpf, 'cliente.teste@oficina.com', '(62) 99999-0000', 'Rua Teste, 100 - Centro', $hashCliente, $now, $now]);
    $clienteId = (int) $pdo->lastInsertId('clientes_id_seq');
    if ($clienteId === 0) {
        $stmt->execute([$cpf]);
        $clienteId = (int) $stmt->fetchColumn();
    }
}

$placa = 'ABC1D23';
$stmt = $pdo->prepare('SELECT id FROM veiculos WHERE cliente_id = ? AND placa = ? LIMIT 1');
$stmt->execute([$clienteId, $placa]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $pdo->prepare('UPDATE veiculos SET marca = ?, modelo = ?, cor = ?, ano = ?, km = ?, updated_at = ? WHERE id = ?')
        ->execute(['Volkswagen', 'Gol', 'Prata', 2020, 45000, $now, $row['id']]);
} else {
    $pdo->prepare('INSERT INTO veiculos (cliente_id, marca, modelo, cor, ano, placa, km, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$clienteId, 'Volkswagen', 'Gol', 'Prata', 2020, $placa, 45000, $now, $now]);
}

$pdo->commit();

echo "Contas de teste OK.\n";
echo "ADMIN:    admin@oficina.com / admin123\n";
echo "MECANICO: mecanico@oficina.com / mecanico123\n";
echo "PORTAL:   CPF 52998224725 ou cliente.teste@oficina.com / cliente123 (placa ABC1D23)\n";
