# Resolver erros de banco (PostgreSQL)

## Configuração esperada no `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_oficina
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

## Checklist rápido

1. **PostgreSQL está rodando?** (serviço Windows / `pg_ctl status`)
2. **`pdo_pgsql` habilitado?** → `php -m | findstr pgsql`
3. **Banco existe?** → `psql -U postgres -c "\l"`
4. **Senha correta** no `.env`
5. Cache limpo: `php artisan config:clear`

## Erros comuns

### `fe_sendauth: no password supplied`
O `.env` está com `DB_PASSWORD=` vazio. No PostgreSQL a senha **é obrigatória** (não é como o MySQL do XAMPP).

1. Abra `.env` e preencha a senha do usuário `postgres`:
   ```env
   DB_PASSWORD=sua_senha_do_postgres
   ```
2. Limpe o cache:
   ```bash
   php artisan config:clear
   ```
3. Teste de novo o login / `php TESTAR_CONEXAO_BANCO.php`

### Connection refused
- PostgreSQL não está iniciado ou porta diferente de 5432

### password authentication failed
- Ajuste `DB_PASSWORD` no `.env` (senha incorreta)

### database "sistema_oficina" does not exist
```bash
psql -U postgres -c "CREATE DATABASE sistema_oficina;"
```

### could not find driver
- Habilite `extension=pdo_pgsql` e `extension=pgsql` no `php.ini`
- Reinicie Apache/PHP

### Migrations falharam
```bash
php artisan migrate:status
php artisan migrate:fresh --seed
```
(Use `fresh` só se puder apagar os dados.)

## Testes

```bash
php verificar_env.php
php TESTAR_CONEXAO_BANCO.php
```

Mais detalhes: `MIGRACAO_POSTGRESQL.md` e `CONFIGURACAO_BANCO_DADOS.md`
