# ✅ CHECKLIST DE CORREÇÕES

Use este checklist para acompanhar o progresso das correções do sistema.

---

## 🔴 PRIORIDADE ALTA - CRÍTICO

### Controllers Faltando

- [ ] **AuthController**
  - [ ] Método `showLoginForm()`
  - [ ] Método `login()`
  - [ ] Método `showRegistrationForm()`
  - [ ] Método `register()`
  - [ ] Método `showForgotPasswordForm()`
  - [ ] Método `forgotPassword()`
  - [ ] Método `logout()`
  - [ ] Método `checkAuth()`

- [ ] **FuncionarioController**
  - [ ] Método `index()`
  - [ ] Método `store()`
  - [ ] Método `edit()`
  - [ ] Método `update()`
  - [ ] Método `toggleStatus()`
  - [ ] Método `destroy()`

- [ ] **RelatorioController**
  - [ ] Método `index()`
  - [ ] Método `financeiro()`
  - [ ] Método `servicos()`
  - [ ] Método `clientes()`

- [ ] **NotificacaoController**
  - [ ] Método `index()`
  - [ ] Método `marcarLida()`

### Middlewares Faltando

- [ ] **CheckActiveMiddleware**
  - [ ] Criar arquivo `app/Http/Middleware/CheckActiveMiddleware.php`
  - [ ] Registrar no `Kernel.php`
  - [ ] Implementar lógica de verificação

- [ ] **AdminMiddleware**
  - [ ] Criar arquivo `app/Http/Middleware/AdminMiddleware.php`
  - [ ] Registrar no `Kernel.php`
  - [ ] Implementar verificação de role admin

- [ ] **PortalAuthMiddleware**
  - [ ] Criar arquivo `app/Http/Middleware/PortalAuthMiddleware.php`
  - [ ] Registrar no `Kernel.php`
  - [ ] Implementar verificação de sessão do portal

### Métodos Faltando nos Controllers Existentes

- [ ] **ClienteController**
  - [ ] Método `ativarPortal()`
  - [ ] Método `enviarAcessoPortal()`
  - [ ] Método `veiculos()` (API)

- [ ] **OrdemServicoController**
  - [ ] Renomear `gerarPdf()` para `pdf()` OU atualizar rota
  - [ ] Método `notificarCliente()`

### Model Cliente

- [ ] Adicionar campos ao `$fillable`:
  - [ ] `email`
  - [ ] `senha_portal`
  - [ ] `acesso_portal`
  - [ ] `token_acesso`
  - [ ] `ultimo_acesso`
  - [ ] `notificar_email`
  - [ ] `notificar_whatsapp`

---

## 🟡 PRIORIDADE MÉDIA

### Segurança

- [ ] Melhorar autenticação do portal
  - [ ] Implementar sistema de autenticação adequado
  - [ ] Adicionar rate limiting
  - [ ] Validar CSRF em todas as rotas

- [ ] Validações
  - [ ] Validador de CPF (algoritmo)
  - [ ] Validador de Placa (Mercosul vs antigo)
  - [ ] Validação de email único

### Código

- [ ] Remover try-catch genéricos
  - [ ] `DashboardController::index()`
  - [ ] `ClienteController::destroy()`
  - [ ] `VeiculoController::destroy()`
  - [ ] `ServicoPecaController::destroy()`

- [ ] Melhorar tratamento de erros
  - [ ] Adicionar logging adequado
  - [ ] Mensagens de erro mais específicas

---

## 🟢 PRIORIDADE BAIXA

### Funcionalidades

- [ ] Implementar `salvarAgendamento()` no PortalClienteController
- [ ] Melhorar `calcularProximaRevisao()` no PortalClienteController
- [ ] Adicionar validação de email no primeiro acesso

### Documentação

- [ ] Atualizar README.md com informações do sistema
- [ ] Adicionar comentários em métodos complexos
- [ ] Documentar API endpoints

### Testes

- [ ] Testes unitários para Models
- [ ] Testes de integração para Controllers
- [ ] Testes de autenticação

---

## 📝 NOTAS

- Execute `php artisan migrate` após adicionar campos ao Model Cliente
- Registre middlewares no `app/Http/Kernel.php` no array `$routeMiddleware`
- Teste todas as rotas após criar controllers
- Verifique se as views existem para os controllers criados

---

**Última atualização:** 2025-01-27


