# Configuração do banco — PostgreSQL

O Projeto Oficina utiliza **PostgreSQL** como banco padrão.

## Variáveis no `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_oficina
DB_USERNAME=postgres
DB_PASSWORD=sua_senha_aqui
```

## Pré-requisitos

1. PostgreSQL instalado e em execução (porta 5432).
2. Extensão PHP `pdo_pgsql` habilitada (`php.ini`).
3. Banco criado:

```sql
CREATE DATABASE sistema_oficina WITH ENCODING 'UTF8' TEMPLATE template0;
```

Ou via linha de comando:

```bash
psql -U postgres -c "CREATE DATABASE sistema_oficina;"
```

## Comandos Laravel

Na pasta `sistema-oficina`:

```bash
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
```

## Testes rápidos

```bash
php verificar_env.php
php TESTAR_CONEXAO_BANCO.php
```

## Observações

- Booleanos (`ativo`, `acesso_portal`, etc.) usam o tipo nativo `boolean` do PostgreSQL.
- Campos que antes eram `ENUM` no MySQL passaram a `string` (varchar), com validação na aplicação.
- Relatórios usam `to_char(...)` no PostgreSQL (em vez de `DATE_FORMAT` do MySQL).
- Ordenação de status nas atribuições usa `CASE ... WHEN` (em vez de `FIELD()` do MySQL).

Guia completo da migração: veja `MIGRACAO_POSTGRESQL.md`.
