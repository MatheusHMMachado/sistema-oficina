# Roteiro de Apresentação — Sistema de Oficina

**Tempo sugerido:** 10 a 15 minutos (+ 3–5 min para perguntas)  
**Público:** professor, banca ou colegas de curso  
**Objetivo:** explicar o projeto, termos técnicos e o que o código mais importante faz

---

## 1. Abertura (≈ 1 min)

**O que falar:**

> “Boa noite/tarde. Vou apresentar o **Sistema de Oficina Mecânica**, uma aplicação web para gerenciar clientes, veículos, ordens de serviço, agendamentos e o portal do cliente. O foco é mostrar a arquitetura, as tecnologias e os trechos de código que sustentam as funcionalidades principais.”

**Slide sugerido:** nome do projeto, integrantes, disciplina, data.

---

## 2. Problema e solução (≈ 1 min)

**O que falar:**

> “Uma oficina precisa controlar quem entrou, qual carro, o que foi feito, quem é o mecânico responsável e como o cliente acompanha o serviço. O sistema centraliza isso em um **painel interno** para admin e mecânicos, e um **portal** para o cliente.”

**Termos para citar:**

| Termo | Explicação simples |
|--------|-------------------|
| **CRUD** | Criar, ler, atualizar e excluir registros (clientes, veículos, OS). |
| **Portal** | Área separada onde o **cliente** consulta status e agenda serviço. |
| **Painel administrativo** | Área onde **funcionários** operam o dia a dia da oficina. |

---

## 3. Stack tecnológica (≈ 2 min)

**O que falar:**

> “O backend é **Laravel 12** em **PHP 8.2+**, com banco **PostgreSQL**. A interface usa **Blade** (templates HTML do Laravel), **Tailwind CSS** e **Alpine.js** para interatividade leve no navegador. PDFs saem com **DomPDF**; senhas usam **bcrypt**.”

**Termos técnicos (fale em uma frase cada):**

| Termo | O que dizer na apresentação |
|--------|----------------------------|
| **Laravel** | Framework PHP que organiza rotas, controllers, models e banco. |
| **MVC** | **Model** (dados), **View** (tela), **Controller** (regras e fluxo). |
| **PostgreSQL** | Banco relacional; tabelas ligadas por chaves estrangeiras (FK). |
| **Migration** | Arquivo que define/altera a estrutura das tabelas no banco. |
| **Eloquent ORM** | Camada do Laravel que acessa o banco usando classes **Model** (ex.: `Cliente`, `OrdemServico`). |
| **Middleware** | Filtro que roda **antes** da rota (ex.: só usuário logado passa). |
| **Blade** | Motor de templates; junta HTML com dados do PHP. |
| **API / JSON** | Resposta em formato de dados (ex.: carga dos mecânicos no modal de agendamento). |

**Arquivo para mencionar:** `composer.json` — lista dependências (Laravel, DomPDF, Sanctum).

---

## 4. Arquitetura do código (≈ 2 min)

**O que falar:**

> “O fluxo típico é: **rota → middleware → controller → model/banco → view**. As rotas ficam em `routes/web.php`; a regra de negócio nos **Controllers** e **Services**; os dados nos **Models** em `app/Models`; as telas em `resources/views`.”

**Pastas importantes (mostrar no VS Code):**

```
sistema-oficina/
├── routes/web.php          → mapa de URLs do sistema
├── app/Http/Controllers/   → lógica de cada módulo
├── app/Models/             → entidades (Cliente, OrdemServico…)
├── app/Services/           → regras reutilizáveis (estoque, SLA, notificações)
├── app/Http/Middleware/    → auth, admin, portal
├── database/migrations/    → estrutura do banco
└── resources/views/        → telas Blade
```

**Termo:** **Separation of concerns** — cada camada com uma responsabilidade.

---

## 5. Dois “mundos” de login (≈ 1 min)

**O que falar:**

> “Existem **dois tipos de usuário** com autenticação **separada**: funcionários na tabela `users` (admin/mecânico) e clientes com `senha_portal` na tabela `clientes`. Isso evita misturar sessão do painel com sessão do portal.”

**Código / arquivos para citar:**

| Peça | Função |
|------|--------|
| `AuthController` | Login, registro e reset de senha do **painel**. |
| `PortalClienteController` | Login, primeiro acesso e consulta do **cliente**. |
| `CheckActiveMiddleware` | Impede login de funcionário **inativo**. |
| `AdminMiddleware` | Restringe financeiro, agendamentos, funcionários, tema. |
| `PortalAuthMiddleware` | Protege rotas `/portal/*` que exigem cliente logado. |

**Frase técnica:** **RBAC** (controle por papel) — `admin` vê tudo; `funcionario` (mecânico) vê só o que foi atribuído a ele.

---

## 6. Módulos principais — o que cada um faz (≈ 4 min)

### 6.1 Clientes e veículos

**Falar:** cadastro base; toda OS precisa de cliente + veículo.

| Controller | Importante |
|------------|------------|
| `ClienteController` | CRUD, ativar portal, token QR. |
| `VeiculoController` | CRUD; placa única; veículo pertence a um cliente. |

**Models:** `Cliente.php`, `Veiculo.php` — relação **1 cliente : N veículos**.

---

### 6.2 Ordem de Serviço (OS) — núcleo do sistema

**Falar:** “A **Ordem de Serviço** é o coração: número automático, status, itens, valor total, PDF.”

| Arquivo | O que faz |
|---------|-----------|
| `OrdemServicoController` | Lista, cria, altera status, adiciona/remove itens, PDF, notifica cliente. |
| `OrdemServico` (model) | Relacionamentos: cliente, veículo, itens, checklist, atribuições. |
| `ItemOrdemServico` | Linha de serviço/peça na OS; soma o `valor_total`. |
| `EstoqueService` | Ao incluir **peça**, baixa estoque; ao cancelar/remover, **restaura**. |

**Status da OS (memorizar):** `aberta` → `em_andamento` → `concluida` (ou `cancelada`).

**Trecho para mostrar (conceito):** `OrdemServicoController::index` — listagem paginada + contadores por status.

---

### 6.3 Agendamento → OS automática

**Falar:** “Cliente agenda no portal; admin **aprova** e o sistema pode criar cliente/veículo, gerar OS e **atribuir mecânico** com prazo.”

| Arquivo | O que faz |
|---------|-----------|
| `PortalClienteController` | Agendamento logado ou visitante (sem cadastro). |
| `AgendamentoController` | Lista pedidos; `cargaMecanicos` (ocupação da equipe); `atualizarStatus` na aprovação. |

**Termos:** **Transação** (`DB::transaction`) — várias gravações no banco “tudo ou nada”.  
**SLA:** prazo datetime para conclusão da tarefa.

---

### 6.4 Atribuições e mecânico

**Falar:** “Admin (ou fluxo de agendamento) **atribui** a OS a um mecânico. No Kanban ele atualiza progresso; se passar do prazo, vira **excedido**.”

| Arquivo | O que faz |
|---------|-----------|
| `AtribuicaoServicoController` | Kanban, progresso, reagendamento de prazo (admin aprova/recusa). |
| `AtribuicaoSlaService` | Marca tarefas vencidas como `excedido` e dispara alertas. |
| `NotificacaoService` | Notificações **in-app** para admin/mecânico. |

**Scope no model `OrdemServico`:** `visivelPara()` — mecânico só enxerga OS com atribuição dele.

---

### 6.5 Portal do cliente

**Falar:** “Consulta pública com **CPF + placa**; logado vê histórico, NPS e pode pedir orçamento.”

| Fluxo | Detalhe |
|-------|---------|
| Consulta pública | Segundo fator (placa) para não expor OS só com CPF. |
| QR Code | `token_acesso` do cliente → acesso rápido. |
| NPS | Uma avaliação 0–10 por OS **concluída**. |

**Controller:** `PortalClienteController` (vários métodos; foco em `processarConsulta`, `salvarAgendamento`, `verOrdem`).

---

### 6.6 Checklist, financeiro, relatórios, tema

**Falar rápido (1 frase cada):**

| Módulo | Código | Função |
|--------|--------|--------|
| Checklist | `ChecklistController` | 12 pontos do carro + fotos na entrada da OS. |
| Financeiro | `FinanceiroController` | Despesas, OS pagas/pendentes, PDF mensal. |
| Relatórios | `RelatorioController` | Financeiro, serviços, clientes (admin). |
| Whitelabel | `TemaController` + `ConfiguracaoSistema` | Logo, cores, tema claro/escuro. |
| Copiloto IA | `AiCopilotoController` | Assistente no painel (local ou API externa). |

---

## 7. Banco de dados — visão rápida (≈ 1 min)

**O que falar:**

> “As **migrations** criam tabelas como `clientes`, `veiculos`, `ordens_servico`, `itens_ordem_servico`, `agendamentos`, `atribuicoes_servico`. A OS conecta cliente, veículo, itens e histórico.”

**Termos:** **PK** (chave primária), **FK** (liga tabelas), **UNIQUE** (CPF, placa, número da OS).

**Documento de apoio:** `docs/01-modelo-entidade-relacionamento.md` (se perguntarem de MER).

---

## 8. Segurança — pontos para a banca (≈ 1 min)

**O que falar:**

> “Usamos **CSRF** nos formulários, senhas com **hash**, middleware de **auth** e **admin**, portal com regras de **primeiro acesso** para não permitir troca indevida de senha, e consulta pública reforçada com placa.”

Não precisa mostrar código de segurança inteiro — cite `VerifyCsrfToken`, bcrypt, `PortalAuthMiddleware`.

---

## 9. Demonstração ao vivo — roteiro (≈ 3 min)

**Ordem sugerida:**

1. Login **admin** → Dashboard.  
2. Abrir uma **OS** → status, item, PDF (cores do tema).  
3. **Agendamentos** → modal **carga de mecânicos** → aprovar (mencionar criação de OS).  
4. Login **portal** (ou consulta CPF+placa) → status da OS.  
5. (Opcional) **Configurações de tema** — whitelabel.

**Se algo falhar:** “Ambiente local; e-mail pode não estar configurado — o sistema avisa em vez de fingir sucesso.”

---

## 10. Encerramento (≈ 1 min)

**O que falar:**

> “Em resumo: Laravel + PostgreSQL, arquitetura MVC, OS como núcleo, portal para o cliente, agendamento com geração de OS e atribuição com SLA. Documentação MER, regras de negócio e requisitos estão em `docs/`; a análise de complexidade na pasta `analise-complexidade`. Obrigado — perguntas?”

---

## 11. Glossário rápido (cola para perguntas)

| Pergunta possível | Resposta curta |
|-------------------|----------------|
| O que é uma **rota**? | URL que chama um método de **Controller**. |
| O que é **paginate**? | Divide listas em páginas (ex.: 15 OS por página). |
| O que é **Eager Loading** (`with()`)? | Carrega relações em menos queries (evita N+1). |
| O que é **DomPDF**? | Biblioteca que gera PDF a partir de HTML/Blade. |
| Diferença **Service** vs **Controller**? | Controller recebe HTTP; Service concentra regra reutilizável (estoque, SLA). |
| O que é **Sanctum**? | Pacote Laravel para tokens de API (uso mínimo no projeto). |

---

## 12. Arquivos “estrela” para abrir no editor durante a fala

1. `routes/web.php` — visão geral do sistema.  
2. `app/Http/Controllers/OrdemServicoController.php` — OS.  
3. `app/Http/Controllers/AgendamentoController.php` — `cargaMecanicos` e aprovação.  
4. `app/Models/OrdemServico.php` — relacionamentos e scopes.  
5. `app/Services/EstoqueService.php` — integração estoque.  
6. `app/Http/Controllers/PortalClienteController.php` — portal.

---

## 13. Dicas de postura na apresentação

- Fale **problema → solução → tecnologia → demo**, não linha por linha de código.  
- Mostre **no máximo 2–3 trechos** de código; explique **o que entra e o que sai**.  
- Use os termos em inglês (**Controller**, **Model**, **Middleware**) e traduza na hora.  
- Se a pergunta for de complexidade, use a pasta `analise-complexidade` (T(n), Big O, gargalo `cargaMecanicos`).

---

*Roteiro alinhado ao código atual do Sistema de Oficina — use como guia; adapte nomes da equipe e tempo da banca.*
