# 🔧 COMO RESOLVER O ERRO DE BANCO DE DADOS

## ❌ ERRO ATUAL

```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)
```

**Causa:** O arquivo `.env` não existe ou não está configurado corretamente.

---

## ✅ SOLUÇÃO RÁPIDA

### Passo 1: Criar o arquivo .env

1. Na pasta `sistema-oficina`, crie um arquivo chamado `.env`
2. Copie o conteúdo abaixo e cole no arquivo:

```env
APP_NAME="Sistema de Oficina"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_oficina
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Passo 2: Ajustar a Senha (se necessário)

Se você configurou uma senha para o MySQL no XAMPP, altere esta linha:

```env
DB_PASSWORD=sua_senha_aqui
```

Se não tem senha, deixe vazio:

```env
DB_PASSWORD=
```

### Passo 3: Criar o Banco de Dados

1. Abra o **phpMyAdmin**: http://localhost/phpmyadmin
2. Clique em **Novo** (New) no menu lateral esquerdo
3. Nome do banco: `sistema_oficina`
4. Collation: `utf8mb4_unicode_ci`
5. Clique em **Criar**

### Passo 4: Gerar Chave da Aplicação

Abra o terminal na pasta `sistema-oficina` e execute:

```bash
php artisan key:generate
```

### Passo 5: Executar Migrations

Execute no terminal:

```bash
php artisan migrate
```

Isso criará todas as tabelas no banco de dados.

### Passo 6: (Opcional) Criar Usuário Admin

Execute:

```bash
php artisan db:seed --class=UserSeeder
```

---

## 🔍 VERIFICAÇÕES

### ✅ MySQL está rodando?
- Abra o **XAMPP Control Panel**
- Verifique se o **MySQL** está com status "Running" (verde)

### ✅ Credenciais corretas?
- **Sem senha:** `DB_PASSWORD=` (vazio)
- **Com senha:** `DB_PASSWORD=sua_senha`

### ✅ Banco de dados existe?
- Verifique no phpMyAdmin se o banco `sistema_oficina` existe
- Se não existir, crie seguindo o Passo 3 acima

---

## 📝 CONFIGURAÇÕES XAMPP PADRÃO

- **Host:** `127.0.0.1` ou `localhost`
- **Porta:** `3306`
- **Usuário:** `root`
- **Senha:** (geralmente vazio)

---

## ⚠️ SE AINDA DER ERRO

### Erro: "Access denied" mesmo com senha vazia

Tente estas configurações no `.env`:

```env
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
```

Ou se tiver senha:

```env
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=root
```

### Erro: "Unknown database"

Certifique-se de criar o banco `sistema_oficina` no phpMyAdmin primeiro!

### Erro: "Connection refused"

1. Verifique se o MySQL está rodando no XAMPP
2. Verifique se a porta 3306 está livre
3. Tente reiniciar o MySQL no XAMPP

---

**Após seguir estes passos, o erro deve ser resolvido!** ✅

