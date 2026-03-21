# 🔧 CONFIGURAÇÃO DO BANCO DE DADOS

## ❌ PROBLEMA IDENTIFICADO

O erro `Access denied for user 'root'@'localhost' (using password: NO)` indica que:

1. O arquivo `.env` não existe ou não está configurado corretamente
2. O Laravel está tentando conectar sem senha
3. As credenciais do banco de dados não estão definidas

---

## ✅ SOLUÇÃO

### 1. Arquivo .env Criado

O arquivo `.env` foi criado com as configurações padrão para XAMPP.

### 2. Configurações Padrão do XAMPP

No XAMPP, as configurações padrão são:
- **Host:** `127.0.0.1` ou `localhost`
- **Porta:** `3306`
- **Usuário:** `root`
- **Senha:** (geralmente vazio, mas pode ter senha)

### 3. Ajustar Configurações

Se você alterou a senha do MySQL no XAMPP, edite o arquivo `.env`:

```env
DB_PASSWORD=sua_senha_aqui
```

---

## 📋 PRÓXIMOS PASSOS

### Passo 1: Criar o Banco de Dados

1. Abra o **phpMyAdmin** (http://localhost/phpmyadmin)
2. Clique em **Novo** (New) no menu lateral
3. Digite o nome: `sistema_oficina`
4. Escolha o collation: `utf8mb4_unicode_ci`
5. Clique em **Criar**

### Passo 2: Gerar a Chave da Aplicação

Execute no terminal (na pasta `sistema-oficina`):

```bash
php artisan key:generate
```

### Passo 3: Executar as Migrations

Execute no terminal:

```bash
php artisan migrate
```

Isso criará todas as tabelas necessárias no banco de dados.

### Passo 4: (Opcional) Popular com Dados de Teste

Execute os seeders:

```bash
php artisan db:seed
```

Ou apenas o seeder de usuário:

```bash
php artisan db:seed --class=UserSeeder
```

---

## 🔍 VERIFICAR CONFIGURAÇÃO

### Se a senha do MySQL estiver configurada:

Edite o arquivo `.env` e altere:

```env
DB_PASSWORD=sua_senha
```

### Se o nome do banco for diferente:

Edite o arquivo `.env` e altere:

```env
DB_DATABASE=nome_do_seu_banco
```

### Se a porta for diferente:

Edite o arquivo `.env` e altere:

```env
DB_PORT=3307
```

---

## ⚠️ PROBLEMAS COMUNS

### Erro: "Access denied"
- Verifique se a senha está correta no `.env`
- Verifique se o MySQL está rodando no XAMPP
- Tente deixar `DB_PASSWORD=` vazio se não configurou senha

### Erro: "Unknown database"
- Crie o banco de dados no phpMyAdmin primeiro
- Verifique se o nome no `.env` está correto

### Erro: "Connection refused"
- Verifique se o MySQL está rodando no XAMPP
- Verifique se a porta está correta (padrão: 3306)

---

## 📝 ESTRUTURA DO ARQUIVO .env

O arquivo `.env` contém:

```env
APP_NAME="Sistema de Oficina"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_oficina
DB_USERNAME=root
DB_PASSWORD=
```

**Importante:** Não compartilhe o arquivo `.env` publicamente! Ele contém informações sensíveis.

---

**Última atualização:** 2025-01-27

