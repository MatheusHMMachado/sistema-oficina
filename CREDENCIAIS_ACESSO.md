# 🔐 CREDENCIAIS DE ACESSO - SISTEMA DE OFICINA

## 👤 USUÁRIO ADMINISTRADOR PADRÃO

Após executar o seeder, você terá um usuário admin criado automaticamente:

### 📧 Credenciais Padrão:
- **Email:** `admin@oficina.com`
- **Senha:** `admin123`

### ⚠️ IMPORTANTE:
**Altere a senha após o primeiro login por questões de segurança!**

---

## 🚀 COMO CRIAR O USUÁRIO ADMIN

### Opção 1: Usando Seeder (Recomendado)

Execute o comando para criar o usuário admin:

```bash
php artisan db:seed --class=UserSeeder
```

Ou execute todos os seeders:

```bash
php artisan db:seed
```

### Opção 2: Usando Tinker (Manual)

Execute no terminal:

```bash
php artisan tinker
```

Depois execute:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrador',
    'email' => 'admin@oficina.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
    'ativo' => true,
    'email_verified_at' => now(),
]);
```

### Opção 3: Via Interface Web

1. Acesse: `http://seu-dominio/register`
2. Crie uma conta com qualquer email
3. O primeiro usuário criado (ID=1) será automaticamente admin
4. Ou edite manualmente no banco de dados para adicionar `role = 'admin'`

---

## 📝 CRIAR OUTROS USUÁRIOS

### Via Interface Web (Recomendado)
1. Faça login como admin
2. Acesse: **Funcionários** (menu lateral)
3. Clique em **Novo Funcionário**
4. Preencha os dados

### Via Tinker

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Criar funcionário comum
User::create([
    'name' => 'Nome do Funcionário',
    'email' => 'funcionario@oficina.com',
    'password' => Hash::make('senha123'),
    'role' => 'funcionario',
    'ativo' => true,
]);

// Criar outro admin
User::create([
    'name' => 'Outro Admin',
    'email' => 'admin2@oficina.com',
    'password' => Hash::make('senha123'),
    'role' => 'admin',
    'ativo' => true,
]);
```

---

## 🔑 PERMISSÕES

### Admin (`role = 'admin'`)
- ✅ Acesso total ao sistema
- ✅ Gerenciar funcionários
- ✅ Acessar relatórios
- ✅ Todas as funcionalidades

### Funcionário (`role = 'funcionario'`)
- ✅ Acesso às funcionalidades principais
- ❌ Não pode gerenciar funcionários
- ❌ Não pode acessar relatórios

---

## 🔄 ALTERAR SENHA

### Via Interface Web
1. Faça login
2. Acesse: **Funcionários** → Seu usuário → **Editar**
3. Digite a nova senha
4. Salve

### Via Tinker

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@oficina.com')->first();
$user->password = Hash::make('nova_senha_aqui');
$user->save();
```

---

## 🛡️ SEGURANÇA

1. **Sempre altere a senha padrão** após o primeiro login
2. **Use senhas fortes** (mínimo 8 caracteres, com letras, números e símbolos)
3. **Não compartilhe credenciais** de admin
4. **Desative usuários** que não estão mais em uso
5. **Monitore acessos** regularmente

---

## 📞 PROBLEMAS COM LOGIN?

### Erro: "Credenciais não correspondem"
- Verifique se o email está correto
- Verifique se a senha está correta
- Verifique se o usuário está ativo (`ativo = true`)

### Erro: "Acesso negado"
- Verifique se o usuário tem `role = 'admin'` para acessar áreas administrativas
- Verifique se `ativo = true`

### Esqueci a senha
- Use a opção "Esqueceu a senha?" na tela de login
- Ou altere via Tinker (veja seção acima)

---

**Última atualização:** 2025-01-27


