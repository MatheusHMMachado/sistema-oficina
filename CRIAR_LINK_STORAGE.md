# 🔗 Como Criar o Link Simbólico do Storage

## ❌ PROBLEMA

As imagens do checklist não estão sendo acessíveis porque o link simbólico do storage não foi criado.

---

## ✅ SOLUÇÃO

### Passo 1: Criar o Link Simbólico

Execute o comando no terminal dentro da pasta `sistema-oficina`:

```bash
php artisan storage:link
```

Este comando cria um link simbólico de `storage/app/public` para `public/storage`, permitindo que as imagens sejam acessíveis via web.

---

### Passo 2: Verificar se Funcionou

Após executar o comando, verifique se a pasta `public/storage` foi criada:

1. Navegue até: `sistema-oficina/public/`
2. Deve existir uma pasta chamada `storage` (é um link simbólico)
3. Se não existir, veja a seção "Problemas Comuns" abaixo

---

## ⚠️ PROBLEMAS COMUNS

### Problema 1: Comando não funciona no Windows

**Solução Manual:**

1. Abra o PowerShell como **Administrador**
2. Navegue até a pasta do projeto:
   ```powershell
   cd "C:\xampp\htdocs\Projeto Oficina\sistema-oficina"
   ```
3. Execute:
   ```powershell
   php artisan storage:link
   ```

### Problema 2: Permissões Insuficientes

**Solução:**

1. Feche o XAMPP
2. Execute o PowerShell como **Administrador**
3. Execute o comando novamente

### Problema 3: Link já existe mas não funciona

**Solução:**

1. Delete o link existente:
   ```powershell
   Remove-Item "public\storage" -Force
   ```
2. Crie novamente:
   ```powershell
   php artisan storage:link
   ```

---

## 📁 ESTRUTURA ESPERADA

Após criar o link, a estrutura deve ser:

```
sistema-oficina/
├── storage/
│   └── app/
│       └── public/
│           └── checklist/
│               └── [numero_ordem]/
│                   └── [fotos].jpg
├── public/
│   └── storage/  ← Link simbólico (aponta para storage/app/public)
│       └── checklist/
│           └── [numero_ordem]/
│               └── [fotos].jpg
```

---

## ✅ TESTE

Após criar o link:

1. Faça upload de uma foto no checklist
2. A foto deve aparecer na lista de fotos
3. Ao clicar na foto, deve abrir em tamanho maior

---

**Se ainda não funcionar, verifique as permissões da pasta `storage/app/public`!**

