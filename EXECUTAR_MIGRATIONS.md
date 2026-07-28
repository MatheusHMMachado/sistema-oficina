# Como executar as migrations (PostgreSQL)

## Pré-requisitos

1. PostgreSQL em execução (porta **5432**)
2. Extensão PHP `pdo_pgsql` habilitada
3. Banco `sistema_oficina` criado
4. Arquivo `.env` configurado para `pgsql`

## Criar o banco

```bash
psql -U postgres -c "CREATE DATABASE sistema_oficina WITH ENCODING 'UTF8' TEMPLATE template0;"
```

## Rodar migrations

Na pasta `sistema-oficina`:

```bash
php artisan config:clear
php artisan migrate
```

Com dados iniciais (admin, serviços, etc.):

```bash
php artisan migrate --seed
```

Ambiente novo (apaga e recria tudo):

```bash
php artisan migrate:fresh --seed
```

## Verificar

```bash
php artisan migrate:status
php TESTAR_CONEXAO_BANCO.php
```

Guia completo: `MIGRACAO_POSTGRESQL.md`
