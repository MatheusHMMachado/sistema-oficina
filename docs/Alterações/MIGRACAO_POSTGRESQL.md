# Migração MySQL → PostgreSQL

Este documento resume as alterações feitas no código e os passos para colocar o sistema no PostgreSQL.

## O que foi alterado no código

### Configuração
- `.env` e `.env.example`: `DB_CONNECTION=pgsql`, porta `5432`, usuário `postgres`, banco `sistema_oficina`
- `config/database.php`: conexão padrão `pgsql`
- `config/queue.php`: fallback do failed jobs para `pgsql`

### SQL incompatível
- `RelatorioController`: `DATE_FORMAT(...)` → `to_char(...)` via `App\Support\DatabaseCompat`
- `AtribuicaoServicoController`: `FIELD(...)` (MySQL) → `CASE WHEN ...` portável

### Migrations
- `enum(...)` substituído por `string(...)` (compatível com PostgreSQL; valores continuam validados na aplicação)
- Removido `->after(...)` (modificador exclusivo do MySQL)
- Índices úteis adicionados em status/tipo/placa onde fazia sentido

### Scripts auxiliares
- `verificar_env.php` e `TESTAR_CONEXAO_BANCO.php` atualizados para PostgreSQL / `pdo_pgsql`

## Passo a passo para subir o banco

### 1. Instalar / iniciar PostgreSQL
- Windows: instale o PostgreSQL e confirme o serviço em execução.
- Confirme a senha do usuário `postgres`.

### 2. Habilitar PHP `pdo_pgsql`
No `php.ini` do XAMPP (ou do PHP CLI), descomente:

```ini
extension=pdo_pgsql
extension=pgsql
```

Reinicie o Apache (se usar) e valide:

```bash
php -m | findstr pgsql
```

### 3. Criar o banco

```bash
psql -U postgres -c "CREATE DATABASE sistema_oficina WITH ENCODING 'UTF8' TEMPLATE template0;"
```

### 4. Ajustar `.env`
Confirme:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_oficina
DB_USERNAME=postgres
DB_PASSWORD=SUA_SENHA
```

### 5. Migrar e popular

Na pasta `sistema-oficina`:

```bash
php artisan config:clear
php artisan migrate:fresh --seed
php artisan storage:link
```

> `migrate:fresh` apaga e recria as tabelas. Use em ambiente novo/local.  
> Se for preservar dados de um MySQL antigo, exporte/importe os dados depois das migrations (não use `fresh` em produção com dados).

### 6. Validar

```bash
php verificar_env.php
php TESTAR_CONEXAO_BANCO.php
php artisan migrate:status
```

Acesse o sistema e teste login, clientes, OS e portal.

## Migração de dados existentes (MySQL → PostgreSQL)

Se já houver dados no MySQL que precisam ser mantidos:

1. Rode as migrations no PostgreSQL **vazio** (`php artisan migrate`).
2. Exporte os dados do MySQL (CSV ou ferramenta como `pgloader` / DBeaver).
3. Importe respeitando a ordem das FKs, por exemplo:
   - `users` → `clientes` → `veiculos` → `servicos_pecas` → `ordens_servico` → demais tabelas
4. Ajuste sequências no PostgreSQL após importar IDs manuais:

```sql
SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1));
-- Repita para cada tabela com serial/bigserial
```

Ferramenta recomendada para carga automática: [pgloader](https://pgloader.readthedocs.io/).

## Comportamento preservado

- Regras de negócio, models e validações permanecem as mesmas.
- Valores de status/tipo/condição continuam sendo os mesmos strings (`aberta`, `servico`, `otimo`, etc.).
- Booleanos passam a usar o tipo nativo do PostgreSQL (Laravel já faz o cast).
