# Requisitos Funcionais e Não Funcionais
## Sistema de Oficina Mecânica

**Projeto:** Sistema de Oficina  
**Versão do documento:** 1.0  
**Data:** 28/08/2026  

---

## 1. Introdução

Este documento lista os requisitos funcionais (RF) e não funcionais (RNF) do Sistema de Oficina, organizados por **prioridade**:

| Prioridade | Significado |
|------------|-------------|
| **Alta** | Essencial para operação do sistema; bloqueia uso principal se ausente |
| **Média** | Importante para gestão, experiência e diferenciação; não impede operação básica |
| **Baixa** | Complementar, parcialmente implementado ou planejado para evolução futura |

---

## 2. Requisitos Funcionais

### 2.1 Prioridade ALTA — Core operacional

| ID | Requisito | Descrição | Módulo |
|----|-----------|-----------|--------|
| RF-A01 | Cadastro de clientes | CRUD completo de clientes com CPF único, dados de contato e endereço | Clientes |
| RF-A02 | Cadastro de veículos | CRUD de veículos vinculados a clientes, com placa única e validação de ano | Veículos |
| RF-A03 | Catálogo de serviços e peças | CRUD de itens do catálogo com tipo (serviço/peça), preço e controle de estoque | Serviços/Peças |
| RF-A04 | Criação de Ordens de Serviço | Abertura de OS com cliente, veículo, data de entrada, km e observações | Ordens de Serviço |
| RF-A05 | Numeração automática de OS | Geração de número sequencial anual (formato AAAA000001) | Ordens de Serviço |
| RF-A06 | Itens na OS | Adicionar e remover serviços/peças na OS com cálculo automático de totais | Ordens de Serviço |
| RF-A07 | Workflow de status da OS | Transição entre status: aberta → em andamento → concluída/cancelada | Ordens de Serviço |
| RF-A08 | Controle de estoque integrado | Baixa automática ao incluir peça; restauração ao remover item ou cancelar OS | Estoque |
| RF-A09 | Autenticação do painel interno | Login/logout de administradores e mecânicos com verificação de conta ativa | Autenticação |
| RF-A10 | Controle de acesso (RBAC) | Separação de permissões entre admin e mecânico | Autenticação |
| RF-A11 | Isolamento de OS por mecânico | Mecânico visualiza apenas OS com atribuição vinculada a ele | Atribuições |
| RF-A12 | Atribuição de tarefas | Admin atribui tarefas a mecânicos com título, descrição e prazo SLA | Atribuições |
| RF-A13 | Checklist de inspeção | Registro de condição em 12 pontos do veículo por OS | Checklist |
| RF-A14 | Fotos no checklist | Upload de fotos por ponto de inspeção (JPEG/PNG, max 5MB) | Checklist |
| RF-A15 | Portal do cliente — consulta pública | Consulta de status de OS por CPF + placa do veículo | Portal |
| RF-A16 | Portal do cliente — login | Autenticação de cliente com CPF/e-mail e senha de portal | Portal |
| RF-A17 | Portal do cliente — acompanhamento | Visualização de OS, histórico e detalhes para cliente autenticado | Portal |
| RF-A18 | Agendamento pelo portal (cliente logado) | Cliente cadastrado agenda serviço selecionando veículo, data e horário | Agendamentos |
| RF-A19 | Agendamento pelo portal (visitante) | Visitante sem cadastro agenda informando dados pessoais e do veículo | Agendamentos |
| RF-A20 | Aprovação de agendamento com geração de OS | Admin aprova agendamento, sistema cria OS, cliente/veículo e atribuição | Agendamentos |
| RF-A21 | Dashboard operacional | Painel com indicadores principais (clientes, veículos, OS, faturamento) | Dashboard |
| RF-A22 | Histórico de status da OS | Registro de atualizações visíveis ao cliente no portal | Ordens de Serviço |

---

### 2.2 Prioridade MÉDIA — Gestão e experiência

| ID | Requisito | Descrição | Módulo |
|----|-----------|-----------|--------|
| RF-M01 | Módulo financeiro | Visão de faturamento, despesas e resultado líquido por competência mensal | Financeiro |
| RF-M02 | Lançamento de despesas | CRUD de despesas categorizadas (contas, salários, impostos, prejuízos) | Financeiro |
| RF-M03 | Status de pagamento da OS | Marcar OS como paga ou pendente | Financeiro |
| RF-M04 | PDF da Ordem de Serviço | Exportação da OS em PDF com identidade visual configurável | Ordens de Serviço |
| RF-M05 | PDF do relatório financeiro mensal | Exportação do relatório financeiro em PDF | Financeiro |
| RF-M06 | Notificações in-app | Alertas para usuários internos (agendamentos, SLA, estoque, atribuições) | Notificações |
| RF-M07 | Avaliação NPS | Cliente avalia OS concluída com nota 0–10 (uma por OS) | Portal / NPS |
| RF-M08 | Whitelabel / Tema | Personalização de nome, cores, tema claro/escuro e logo | Configurações |
| RF-M09 | Relatório financeiro gerencial | Receita, ticket médio, OS pagas e indicadores | Relatórios |
| RF-M10 | Relatório de serviços | Serviços mais executados, peças mais vendidas, status de OS | Relatórios |
| RF-M11 | Relatório de clientes | Top clientes por faturamento, frequência e novos cadastros | Relatórios |
| RF-M12 | Painel de carga de mecânicos | Visualização de ocupação da equipe na aprovação de agendamento | Agendamentos |
| RF-M13 | Reagendamento de SLA | Mecânico solicita prorrogação; admin aprova ou recusa | Atribuições |
| RF-M14 | SLA automático (prazo excedido) | Sistema marca atribuições vencidas e notifica envolvidos | Atribuições |
| RF-M15 | Primeiro acesso ao portal | Cliente cria senha de portal validando CPF e e-mail | Portal |
| RF-M16 | Acesso via QR Code | Cliente acessa consulta via token QR sem informar placa | Portal |
| RF-M17 | Ativação do portal pelo admin | Admin habilita acesso ao portal para cliente | Clientes |
| RF-M18 | Solicitação de orçamento | Cliente solicita orçamento; admin é notificado | Portal |
| RF-M19 | Lembretes de revisão | Orientação ao cliente para agendar nova revisão | Portal |
| RF-M20 | Gestão de funcionários | CRUD de usuários internos (admin/mecânico), ativar/desativar | Funcionários |
| RF-M21 | Recuperação de senha (admin) | Fluxo esqueci minha senha com token por e-mail | Autenticação |
| RF-M22 | Copiloto de IA | Assistente integrado para usuários internos (local ou LLM) | Copiloto |
| RF-M23 | Alerta de estoque baixo | Notificação a admins quando peça atinge estoque mínimo | Estoque |
| RF-M24 | Contadores de status na listagem de OS | Totais por status em toda a base, não apenas na página atual | Ordens de Serviço |
| RF-M25 | Criação automática de item na OS (agendamento) | Ao aprovar agendamento, cria item se serviço existir no catálogo | Agendamentos |
| RF-M26 | Comunicação de status ao cliente | Mensagens de atualização visíveis no portal (max 500 chars) | Ordens de Serviço |

---

### 2.3 Prioridade BAIXA — Complementares / parciais

| ID | Requisito | Descrição | Módulo | Status |
|----|-----------|-----------|--------|--------|
| RF-B01 | Envio de e-mail ao cliente | Notificar cliente sobre OS e enviar credenciais do portal | Comunicação | Parcial — retorna erro se SMTP não configurado |
| RF-B02 | Notificação WhatsApp | Enviar alertas via WhatsApp conforme preferência do cliente | Comunicação | Não implementado (campo existe) |
| RF-B03 | Recuperação de senha do portal | Fluxo esqueci minha senha para clientes do portal | Portal | Não implementado |
| RF-B04 | API REST completa | Endpoints REST para integração externa | API | Parcial — apenas veículos por cliente e check-auth |
| RF-B05 | Registro público após bootstrap | Permitir auto-cadastro de funcionários após instalação | Autenticação | Bloqueado após primeiro usuário |
| RF-B06 | Lembretes persistentes | Salvar preferências de lembrete do cliente | Portal | Redireciona para agendamento |
| RF-B07 | Status online de funcionários | Indicador de funcionários online no sistema | Funcionários | Não implementado |
| RF-B08 | Fila assíncrona para notificações | Processar e-mails e alertas em background | Infraestrutura | Não implementado (sync) |
| RF-B09 | Scheduler para SLA | Job cron para verificação periódica de prazos | Atribuições | Parcial — verificação on-demand |
| RF-B10 | Testes automatizados abrangentes | Cobertura de testes unitários e de integração | Qualidade | PHPUnit presente, cobertura não mapeada |

---

## 3. Requisitos Não Funcionais

### 3.1 Prioridade ALTA — Infraestrutura e segurança

| ID | Requisito | Descrição | Implementação |
|----|-----------|-----------|---------------|
| RNF-A01 | Linguagem PHP 8.2+ | Runtime mínimo compatível com Laravel 12 | `composer.json`: `"php": "^8.2"` |
| RNF-A02 | Banco PostgreSQL | SGBD relacional principal | Configuração `.env`, migrations compatíveis |
| RNF-A03 | Framework Laravel 12 | Base da aplicação web | `laravel/framework: ^12.0` |
| RNF-A04 | Proteção CSRF | Token em todos os formulários POST | Middleware Laravel padrão |
| RNF-A05 | Hash de senhas (bcrypt) | Senhas nunca armazenadas em texto plano | User e Cliente (senha_portal) |
| RNF-A06 | Sessões web | Autenticação baseada em sessão | `SESSION_DRIVER=file` |
| RNF-A07 | Validação de uploads | Restrição de tipo e tamanho de arquivos | Checklist (5MB), Logo (2MB) |
| RNF-A08 | Transações de estoque | Consistência em operações de baixa/restauração | `DB::transaction` + `lockForUpdate` |
| RNF-A09 | Health check | Endpoint para monitoramento de disponibilidade | `GET /up` |
| RNF-A10 | Middleware de conta ativa | Bloqueio de usuários desativados | `checkActive` middleware |
| RNF-A11 | Separação de ambientes de auth | Sessões independentes para painel e portal | Middleware `portal.auth` |
| RNF-A12 | Integridade referencial | FKs e proteções contra exclusão indevida | Migrations + validação nos controllers |

---

### 3.2 Prioridade MÉDIA — Performance, usabilidade e integração

| ID | Requisito | Descrição | Implementação |
|----|-----------|-----------|---------------|
| RNF-M01 | Geração de PDF | Documentos PDF para OS e relatórios | DomPDF (`barryvdh/laravel-dompdf ^3.0`) |
| RNF-M02 | Compatibilidade SQL multi-driver | Funções SQL adaptadas ao driver | `DatabaseCompat` (ex.: `to_char` PostgreSQL) |
| RNF-M03 | Cache de configuração | Reduzir consultas repetidas de whitelabel | Cache file, TTL 60 segundos |
| RNF-M04 | Storage público | Arquivos acessíveis via URL pública | `storage:link` + disco `public` |
| RNF-M05 | API token (Sanctum) | Autenticação para endpoints API | Laravel Sanctum instalado |
| RNF-M06 | Paginação | Listagens com paginação para performance | 10–20 registros por página |
| RNF-M07 | Interface responsiva | Layout adaptável a desktop e mobile | Tailwind CSS + Alpine.js |
| RNF-M08 | Tema dinâmico | Cores e logo aplicados globalmente | Partial `brand-theme` |
| RNF-M09 | Suporte a subpasta (XAMPP) | URLs corretas em instalação local | Helper `asset()` para storage |
| RNF-M10 | Copiloto IA configurável | Modo local ou LLM externa via API key | Variável `AI_API_KEY` |
| RNF-M11 | PDF com identidade visual | Cores do whitelabel nos documentos PDF | `ConfiguracaoSistema` + views PDF |
| RNF-M12 | Feedback honesto de e-mail | Não simular sucesso quando SMTP indisponível | Controllers de notificação |

---

### 3.3 Prioridade BAIXA — Evolução e operação

| ID | Requisito | Descrição | Status |
|----|-----------|-----------|--------|
| RNF-B01 | Servidor SMTP configurável | Envio real de e-mails transacionais | Configurável (.env); fluxos retornam erro |
| RNF-B02 | Filas assíncronas | Processamento em background (Redis/database) | `QUEUE_CONNECTION=sync` |
| RNF-B03 | Cache distribuído (Redis) | Cache e sessões em ambiente escalável | Não utilizado |
| RNF-B04 | Scheduler (cron) | Jobs periódicos (SLA, lembretes, limpeza) | Não configurado |
| RNF-B05 | Logs estruturados | Rastreamento de erros e auditoria | Log Laravel padrão |
| RNF-B06 | Testes automatizados | PHPUnit com cobertura significativa | Framework presente |
| RNF-B07 | CI/CD | Pipeline de build, teste e deploy | Não configurado |
| RNF-B08 | Backup automatizado | Rotina de backup do PostgreSQL | Responsabilidade do ambiente |
| RNF-B09 | HTTPS obrigatório | Redirecionamento e cookies seguros | Depende do ambiente de deploy |
| RNF-B10 | Rate limiting | Proteção contra abuso em login e API | Parcial (Laravel throttle padrão) |

---

## 4. Matriz de rastreabilidade (módulos × prioridade)

| Módulo | RF Alta | RF Média | RF Baixa | RNF Alta | RNF Média | RNF Baixa |
|--------|---------|----------|----------|----------|-----------|-----------|
| Autenticação | 2 | 1 | 1 | 4 | 0 | 1 |
| Clientes | 1 | 2 | 0 | 1 | 0 | 0 |
| Veículos | 1 | 0 | 0 | 1 | 0 | 0 |
| Serviços/Peças | 1 | 1 | 0 | 1 | 0 | 0 |
| Ordens de Serviço | 6 | 4 | 0 | 2 | 2 | 0 |
| Estoque | 1 | 1 | 0 | 1 | 0 | 0 |
| Checklist | 2 | 0 | 0 | 1 | 0 | 0 |
| Atribuições | 2 | 3 | 1 | 1 | 0 | 1 |
| Agendamentos | 3 | 2 | 0 | 0 | 0 | 0 |
| Portal | 3 | 6 | 2 | 1 | 0 | 0 |
| Financeiro | 0 | 3 | 0 | 0 | 1 | 0 |
| Relatórios | 0 | 3 | 0 | 0 | 0 | 0 |
| Notificações | 0 | 1 | 1 | 0 | 1 | 0 |
| Configurações | 0 | 1 | 0 | 0 | 2 | 0 |
| Funcionários | 0 | 1 | 1 | 1 | 0 | 0 |
| Copiloto IA | 0 | 1 | 0 | 0 | 1 | 0 |
| Infraestrutura | 0 | 0 | 2 | 4 | 4 | 6 |

---

## 5. Resumo quantitativo

| Categoria | Alta | Média | Baixa | Total |
|-----------|------|-------|-------|-------|
| Requisitos Funcionais | 22 | 26 | 10 | **58** |
| Requisitos Não Funcionais | 12 | 12 | 10 | **34** |
| **Total geral** | **34** | **38** | **20** | **92** |

---

## 6. Dependências entre requisitos de alta prioridade

```
RF-A09 (Autenticação) ──► RF-A10 (RBAC) ──► RF-A11 (Isolamento mecânico)
        │
        └──► RF-A04 (Criar OS) ──► RF-A06 (Itens) ──► RF-A08 (Estoque)
                    │
                    ├──► RF-A07 (Workflow status)
                    ├──► RF-A13 (Checklist)
                    └──► RF-A12 (Atribuições)

RF-A15 (Consulta portal) ──► RF-A16 (Login portal) ──► RF-A17 (Acompanhamento)
        │
        └──► RF-A18/A19 (Agendamento) ──► RF-A20 (Aprovação → OS)
```

---

## 7. Critérios de aceite gerais

### 7.1 Requisitos funcionais de alta prioridade
- [ ] Usuário admin consegue criar cliente, veículo e abrir OS com itens
- [ ] Estoque de peças é debitado/restaurado corretamente em todas as operações
- [ ] Mecânico visualiza apenas OS atribuídas a ele
- [ ] Cliente consulta OS por CPF + placa no portal público
- [ ] Visitante consegue agendar serviço sem cadastro prévio
- [ ] Admin aprova agendamento e sistema gera OS + atribuição automaticamente
- [ ] Checklist registra 12 pontos com fotos por OS

### 7.2 Requisitos não funcionais de alta prioridade
- [ ] Sistema roda em PHP 8.2+ com PostgreSQL
- [ ] Formulários rejeitam requisições sem token CSRF
- [ ] Senhas armazenadas com bcrypt
- [ ] Usuário desativado não consegue acessar o sistema
- [ ] Endpoint `/up` retorna status OK

---

## 8. Glossário

| Termo | Definição |
|-------|-----------|
| **OS** | Ordem de Serviço — documento central de atendimento |
| **SLA** | Service Level Agreement — prazo acordado para conclusão de tarefa |
| **NPS** | Net Promoter Score — métrica de satisfação (nota 0–10) |
| **RBAC** | Role-Based Access Control — controle de acesso por perfil |
| **Whitelabel** | Personalização da identidade visual da aplicação |
| **Portal** | Ambiente web destinado ao cliente final |
| **Painel interno** | Ambiente web para administradores e mecânicos |

---

*Documento gerado com base na análise de rotas, controllers, views, migrations e configurações do sistema, sem alteração de código.*
