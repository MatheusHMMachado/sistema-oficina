# 🗄️ COMO EXECUTAR AS MIGRATIONS

## ❌ ERRO ATUAL

```
Table 'laravel.clientes' doesn't exist
```

**Causa:** As tabelas do banco de dados ainda não foram criadas. É necessário executar as migrations.

---

## ✅ SOLUÇÃO - EXECUTAR MIGRATIONS

### Passo 1: Verificar se o MySQL está rodando

1. Abra o **XAMPP Control Panel**
2. Verifique se o **MySQL** está com status "Running" (verde)
3. Se não estiver, clique em **Start** ao lado do MySQL

### Passo 2: Verificar se o banco de dados existe

1. Abra o **phpMyAdmin**: http://localhost/phpmyadmin
2. Verifique se o banco `sistema_oficina` existe
3. Se não existir:
   - Clique em **Novo** (New)
   - Nome: `sistema_oficina`
   - Collation: `utf8mb4_unicode_ci`
   - Clique em **Criar**

### Passo 3: Executar as Migrations

Abra o terminal na pasta `sistema-oficina` e execute:

```bash
php artisan migrate
```

Isso criará todas as tabelas necessárias:
- ✅ `users` - Usuários do sistema
- ✅ `clientes` - Clientes
- ✅ `veiculos` - Veículos
- ✅ `servicos_pecas` - Serviços e peças
- ✅ `ordens_servico` - Ordens de serviço
- ✅ `itens_ordem_servico` - Itens das ordens
- ✅ `checklists` - Checklists
- ✅ `fotos_checklist` - Fotos dos checklists
- ✅ E outras tabelas do sistema

### Passo 4: (Opcional) Popular com dados de teste

Execute os seeders para criar dados de exemplo:

```bash
php artisan db:seed
```

Ou apenas o usuário admin:

```bash
php artisan db:seed --class=UserSeeder
```

---

## 🔍 VERIFICAR SE FUNCIONOU

Após executar `php artisan migrate`, você deve ver uma saída como:

```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (XX.XXms)
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table (XX.XXms)
...
```

Se aparecer algum erro, me avise qual foi!

---

## ⚠️ PROBLEMAS COMUNS

### Erro: "Access denied"
- Verifique o arquivo `.env` e as credenciais do banco
- Veja o arquivo `COMO_RESOLVER_ERRO_BANCO.md`

### Erro: "Unknown database"
- Crie o banco `sistema_oficina` no phpMyAdmin primeiro

### Erro: "Table already exists"
- Execute: `php artisan migrate:fresh` (⚠️ Isso apagará todos os dados!)
- Ou: `php artisan migrate:refresh` (recria as tabelas)

---

## 📋 ORDEM DAS MIGRATIONS

As migrations serão executadas nesta ordem:

1. `2014_10_12_000000_create_users_table` - Tabela de usuários
2. `2014_10_12_100000_create_password_resets_table` - Recuperação de senha
3. `2019_08_19_000000_create_failed_jobs_table` - Jobs falhados
4. `2019_12_14_000001_create_personal_access_tokens_table` - Tokens API
5. `2025_01_27_000001_add_role_and_ativo_to_users_table` - Campos role e ativo
6. `2025_09_14_025644_create_clientes_table` - Tabela de clientes
7. `2025_09_14_025656_create_veiculos_table` - Tabela de veículos
8. `2025_09_14_025708_create_servicos_pecas_table` - Serviços e peças
9. `2025_09_14_025720_create_ordens_servico_table` - Ordens de serviço
10. `2025_09_14_025733_create_itens_ordem_servico_table` - Itens das ordens
11. `2025_09_14_025744_create_checklists_table` - Checklists
12. `2025_09_14_025756_create_fotos_checklist_table` - Fotos dos checklists
13. `2025_09_14_025800_add_portal_fields_to_clientes_table` - Campos do portal

---

**Após executar as migrations, o erro será resolvido!** ✅

