# Modelo Entidade-Relacionamento (MER)
## Sistema de Oficina Mecânica

**Projeto:** Sistema de Oficina  
**Versão do documento:** 1.0  
**Data:** 28/08/2026  
**Stack:** Laravel 12 · PHP 8.2+ · PostgreSQL  

---

## 1. Visão geral

O Sistema de Oficina é uma aplicação web para gestão operacional de oficinas mecânicas, composta por dois ambientes de autenticação distintos:

| Ambiente | Entidade de autenticação | Descrição |
|----------|--------------------------|-----------|
| **Painel interno** | `users` | Administradores e mecânicos (funcionários) |
| **Portal do cliente** | `clientes` (senha_portal) | Clientes finais que acompanham serviços |

O núcleo operacional gira em torno da entidade **Ordem de Serviço (OS)**, que conecta clientes, veículos, itens de serviço/peça, checklist, atribuições de mecânicos, histórico de status, avaliações NPS e, quando aplicável, agendamentos de origem.

---

## 2. Diagrama Entidade-Relacionamento

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role
        boolean ativo
    }

    CLIENTES {
        bigint id PK
        string nome
        string cpf UK
        string email UK
        string senha_portal
        boolean acesso_portal
        string token_acesso
    }

    VEICULOS {
        bigint id PK
        bigint cliente_id FK
        string marca
        string modelo
        string placa
        integer ano
    }

    SERVICOS_PECAS {
        bigint id PK
        string descricao
        decimal valor_unitario
        string tipo
        boolean ativo
        integer quantidade_estoque
        integer estoque_minimo
    }

    ORDENS_SERVICO {
        bigint id PK
        string numero_ordem UK
        bigint cliente_id FK
        bigint veiculo_id FK
        date data_entrada
        string status
        string status_pagamento
        decimal valor_total
    }

    ITENS_ORDEM_SERVICO {
        bigint id PK
        bigint ordem_servico_id FK
        bigint servico_peca_id FK
        integer quantidade
        decimal valor_unitario
        decimal valor_total
        boolean estoque_baixado
    }

    CHECKLISTS {
        bigint id PK
        bigint ordem_servico_id FK
        string ponto_veiculo
        string condicao
    }

    FOTOS_CHECKLIST {
        bigint id PK
        bigint checklist_id FK
        string caminho_foto
    }

    STATUS_ATUALIZACOES {
        bigint id PK
        bigint ordem_servico_id FK
        bigint user_id FK
        string status_anterior
        string status_novo
        timestamp data_atualizacao
    }

    ATRIBUICOES_SERVICO {
        bigint id PK
        bigint ordem_servico_id FK
        bigint mecanico_id FK
        bigint atribuido_por FK
        string titulo
        timestamp prazo
        string status
        tinyint progresso
    }

    AGENDAMENTOS {
        bigint id PK
        bigint cliente_id FK
        bigint veiculo_id FK
        bigint ordem_servico_id FK
        string servico
        date data_agendada
        string status
        boolean eh_visitante
    }

    DESPESAS {
        bigint id PK
        string descricao
        decimal valor
        string categoria
        date data_despesa
        bigint user_id FK
    }

    AVALIACOES_NPS {
        bigint id PK
        bigint cliente_id FK
        bigint ordem_servico_id FK_UK
        tinyint nota
    }

    NOTIFICACOES {
        bigint id PK
        bigint user_id FK
        string tipo
        string titulo
        boolean lida
    }

    CONFIGURACOES_SISTEMA {
        bigint id PK
        string nome_empresa
        string cor_primaria
        string cor_secundaria
        string tema
        string logo_path
    }

    CLIENTES ||--o{ VEICULOS : possui
    CLIENTES ||--o{ ORDENS_SERVICO : solicita
    CLIENTES ||--o{ AGENDAMENTOS : agenda
    CLIENTES ||--o{ AVALIACOES_NPS : avalia

    VEICULOS ||--o{ ORDENS_SERVICO : atende
    VEICULOS ||--o{ AGENDAMENTOS : referencia

    ORDENS_SERVICO ||--o{ ITENS_ORDEM_SERVICO : contem
    ORDENS_SERVICO ||--o{ CHECKLISTS : inspeciona
    ORDENS_SERVICO ||--o{ STATUS_ATUALIZACOES : historico
    ORDENS_SERVICO ||--o{ ATRIBUICOES_SERVICO : delega
    ORDENS_SERVICO ||--o| AVALIACOES_NPS : recebe
    ORDENS_SERVICO ||--o| AGENDAMENTOS : origina

    SERVICOS_PECAS ||--o{ ITENS_ORDEM_SERVICO : compoe
    SERVICOS_PECAS ||--o{ ATRIBUICOES_SERVICO : referencia

    CHECKLISTS ||--o{ FOTOS_CHECKLIST : anexa

    USERS ||--o{ ATRIBUICOES_SERVICO : executa
    USERS ||--o{ ATRIBUICOES_SERVICO : atribui
    USERS ||--o{ STATUS_ATUALIZACOES : registra
    USERS ||--o{ DESPESAS : lanca
    USERS ||--o{ NOTIFICACOES : recebe
    USERS ||--o{ AGENDAMENTOS : responde
```

---

## 3. Dicionário de dados

### 3.1 `users` — Usuários internos (admin e mecânicos)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK, auto incremento | Identificador único |
| name | VARCHAR | Sim | — | Nome completo |
| email | VARCHAR | Sim | UNIQUE | E-mail de login |
| email_verified_at | TIMESTAMP | Não | — | Verificação de e-mail |
| password | VARCHAR | Sim | Hash bcrypt | Senha de acesso |
| remember_token | VARCHAR | Não | — | Token "lembrar-me" |
| role | VARCHAR | Sim | Default: `funcionario` | Papel: `admin` ou `funcionario` |
| ativo | BOOLEAN | Sim | Default: `true` | Indica se o usuário pode acessar o sistema |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Regra especial:** o usuário com `id = 1` é sempre administrador.

---

### 3.2 `clientes` — Clientes da oficina

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| nome | VARCHAR | Sim | — | Nome ou razão social |
| cpf | VARCHAR(14) | Sim | UNIQUE | CPF normalizado (11 dígitos) |
| rg | VARCHAR(20) | Não | — | Documento de identidade |
| endereco | TEXT | Não | — | Endereço completo |
| telefone | VARCHAR(15) | Não | — | Telefone fixo |
| celular | VARCHAR(15) | Não | — | Celular |
| email | VARCHAR | Não | UNIQUE | E-mail (portal e comunicação) |
| senha_portal | VARCHAR | Não | Hash bcrypt | Senha do portal do cliente |
| acesso_portal | BOOLEAN | Sim | Default: `false` | Portal habilitado |
| token_acesso | VARCHAR | Não | — | Token para acesso via QR Code |
| ultimo_acesso | TIMESTAMP | Não | — | Último login no portal |
| notificar_email | BOOLEAN | Sim | Default: `true` | Preferência de notificação |
| notificar_whatsapp | BOOLEAN | Sim | Default: `false` | Preferência WhatsApp (reservado) |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.3 `veiculos` — Veículos dos clientes

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| cliente_id | BIGINT | Sim | FK → clientes.id | Proprietário |
| marca | VARCHAR | Sim | — | Fabricante |
| modelo | VARCHAR | Sim | — | Modelo |
| cor | VARCHAR | Sim | — | Cor do veículo |
| ano | INTEGER | Sim | 1900 até ano atual + 1 | Ano de fabricação |
| placa | VARCHAR(8) | Sim | INDEX, única global | Placa normalizada (7 caracteres) |
| chassi | VARCHAR | Não | — | Número do chassi |
| km | INTEGER | Sim | Default: 0 | Quilometragem atual |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Índices:** `placa`, `cliente_id`

---

### 3.4 `servicos_pecas` — Catálogo de serviços e peças

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| descricao | VARCHAR | Sim | — | Nome do serviço ou peça |
| valor_unitario | DECIMAL(10,2) | Sim | — | Preço unitário |
| tipo | VARCHAR(20) | Sim | INDEX | `servico` ou `peca` |
| ativo | BOOLEAN | Sim | Default: `true`, INDEX | Disponível para uso |
| quantidade_estoque | UNSIGNED INT | Sim | Default: 0 | Estoque (apenas peças) |
| estoque_minimo | UNSIGNED INT | Sim | Default: 0 | Limite para alerta |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.5 `ordens_servico` — Ordens de serviço (núcleo operacional)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| numero_ordem | VARCHAR | Sim | UNIQUE | Formato: AAAA + 6 dígitos sequenciais |
| cliente_id | BIGINT | Sim | FK → clientes.id | Cliente solicitante |
| veiculo_id | BIGINT | Sim | FK → veiculos.id | Veículo atendido |
| data_entrada | DATE | Sim | — | Data de recebimento |
| data_saida | DATE | Não | — | Data de entrega |
| km_entrada | INTEGER | Não | — | Quilometragem na entrada |
| status | VARCHAR(20) | Sim | Default: `aberta`, INDEX | `aberta`, `em_andamento`, `concluida`, `cancelada` |
| status_pagamento | VARCHAR(20) | Sim | Default: `pendente`, INDEX | `pago` ou `pendente` |
| observacoes | TEXT | Não | — | Observações gerais |
| valor_total | DECIMAL(10,2) | Sim | Default: 0 | Soma dos itens |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.6 `itens_ordem_servico` — Itens (linhas) da OS

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| ordem_servico_id | BIGINT | Sim | FK → ordens_servico.id | OS vinculada |
| servico_peca_id | BIGINT | Sim | FK → servicos_pecas.id | Item do catálogo |
| quantidade | INTEGER | Sim | ≥ 1 | Quantidade |
| valor_unitario | DECIMAL(10,2) | Sim | — | Preço no momento da inclusão |
| valor_total | DECIMAL(10,2) | Sim | — | quantidade × valor_unitario |
| estoque_baixado | BOOLEAN | Sim | Default: `false` | Indica se o estoque foi debitado |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.7 `checklists` — Inspeção visual do veículo

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| ordem_servico_id | BIGINT | Sim | FK → ordens_servico.id | OS inspecionada |
| ponto_veiculo | VARCHAR(40) | Sim | INDEX composto | Ponto inspecionado (12 pontos fixos) |
| observacao | TEXT | Não | — | Comentário do inspetor |
| condicao | VARCHAR(20) | Sim | — | `otimo`, `bom`, `regular`, `ruim`, `pessimo` |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Índice único lógico:** `(ordem_servico_id, ponto_veiculo)` — um registro por ponto por OS.

**Pontos do veículo:** frente esquerda/direita, lateral esquerda/direita, traseira esquerda/direita, capô, teto, para-brisa, vidro traseiro, interior, motor.

---

### 3.8 `fotos_checklist` — Fotos da inspeção

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| checklist_id | BIGINT | Sim | FK → checklists.id | Item do checklist |
| caminho_foto | VARCHAR | Sim | — | Caminho no storage público |
| descricao | VARCHAR | Não | — | Legenda da foto |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.9 `status_atualizacoes` — Histórico de comunicação com o cliente

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| ordem_servico_id | BIGINT | Sim | FK → ordens_servico.id, CASCADE | OS relacionada |
| user_id | BIGINT | Não | FK → users.id, SET NULL | Funcionário que registrou |
| status_anterior | VARCHAR | Não | — | Status antes da mudança |
| status_novo | VARCHAR | Sim | — | Novo status |
| mensagem | TEXT | Não | Max 500 caracteres | Mensagem visível no portal |
| observacoes | TEXT | Não | — | Observações internas |
| data_atualizacao | TIMESTAMP | Sim | Default: now() | Data/hora do registro |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.10 `atribuicoes_servico` — Tarefas atribuídas a mecânicos

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| ordem_servico_id | BIGINT | Sim | FK → ordens_servico.id, CASCADE | OS vinculada |
| servico_peca_id | BIGINT | Não | FK → servicos_pecas.id, SET NULL | Serviço de referência |
| mecanico_id | BIGINT | Sim | FK → users.id, CASCADE | Mecânico responsável |
| atribuido_por | BIGINT | Sim | FK → users.id, CASCADE | Admin que atribuiu |
| titulo | VARCHAR | Sim | — | Título da tarefa |
| descricao | TEXT | Não | — | Descrição detalhada |
| prazo | TIMESTAMP | Sim | — | SLA (data/hora limite) |
| status | VARCHAR(20) | Sim | Default: `pendente`, INDEX | `pendente`, `em_andamento`, `paralisado`, `excedido`, `concluido` |
| progresso | TINYINT | Sim | Default: 0, 0–100 | Percentual de conclusão |
| observacao_mecanico | TEXT | Não | — | Comentário do mecânico |
| motivo_nao_conclusao | TEXT | Não | — | Motivo de paralisação |
| novo_prazo | TIMESTAMP | Não | — | Prazo solicitado em reagendamento |
| reagendamento_status | VARCHAR(20) | Não | — | `pendente`, `aprovado`, `recusado` |
| alerta_prazo_em | TIMESTAMP | Não | — | Controle de alerta único de SLA |
| reagendamento_respondido_em | TIMESTAMP | Não | — | Data da resposta ao reagendamento |
| concluido_em | TIMESTAMP | Não | — | Data de conclusão |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Índices:** `status`, `mecanico_id`

---

### 3.11 `agendamentos` — Solicitações de agendamento (portal)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| cliente_id | BIGINT | Não | FK → clientes.id, CASCADE | Cliente cadastrado (nullable para visitantes) |
| nome_contato | VARCHAR | Não | — | Nome do visitante |
| cpf_contato | VARCHAR(14) | Não | — | CPF do visitante |
| telefone_contato | VARCHAR(20) | Não | — | Telefone do visitante |
| email_contato | VARCHAR | Não | — | E-mail do visitante |
| veiculo_id | BIGINT | Não | FK → veiculos.id, SET NULL | Veículo cadastrado |
| ordem_servico_id | BIGINT | Não | FK → ordens_servico.id, SET NULL | OS gerada na aprovação |
| veiculo_info | VARCHAR | Não | — | Descrição livre do veículo |
| placa_veiculo | VARCHAR(10) | Não | — | Placa informada |
| marca_veiculo | VARCHAR | Não | — | Marca informada |
| modelo_veiculo | VARCHAR | Não | — | Modelo informado |
| ano_veiculo | SMALLINT | Não | — | Ano informado |
| cor_veiculo | VARCHAR | Não | — | Cor informada |
| km_veiculo | UNSIGNED INT | Não | — | Quilometragem informada |
| chassi_veiculo | VARCHAR | Não | — | Chassi informado |
| eh_visitante | BOOLEAN | Sim | Default: `false` | Indica agendamento sem cadastro prévio |
| servico | VARCHAR | Sim | — | Serviço solicitado |
| data_agendada | DATE | Sim | — | Data desejada |
| hora | VARCHAR(5) | Sim | — | Horário desejado (HH:MM) |
| observacoes | TEXT | Não | — | Relato do cliente |
| status | VARCHAR(20) | Sim | Default: `pendente`, INDEX | `pendente`, `aprovado`, `recusado`, `concluido` |
| resposta_admin | TEXT | Não | — | Resposta do administrador |
| respondido_por | BIGINT | Não | FK → users.id, SET NULL | Admin que respondeu |
| respondido_em | TIMESTAMP | Não | — | Data/hora da resposta |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Índice:** `(status, data_agendada)`

---

### 3.12 `despesas` — Despesas operacionais

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| descricao | VARCHAR | Sim | — | Descrição da despesa |
| valor | DECIMAL(10,2) | Sim | — | Valor monetário |
| categoria | VARCHAR(50) | Sim | INDEX | Categoria contábil |
| data_despesa | DATE | Não | INDEX | Data de competência |
| user_id | BIGINT | Não | FK → users.id, SET NULL | Usuário que lançou |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Categorias:** `contas_a_pagar`, `salario_mecanicos`, `impostos_taxas`, `prejuizos_retrabalhos`

---

### 3.13 `avaliacoes_nps` — Avaliação de satisfação (NPS)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| cliente_id | BIGINT | Sim | FK → clientes.id, CASCADE | Cliente avaliador |
| ordem_servico_id | BIGINT | Sim | FK → ordens_servico.id, CASCADE, UNIQUE | OS avaliada (uma por OS) |
| nota | TINYINT | Sim | 0 a 10 | Nota NPS |
| comentario | TEXT | Não | — | Comentário opcional |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

---

### 3.14 `notificacoes` — Notificações in-app (usuários internos)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| user_id | BIGINT | Sim | FK → users.id, CASCADE | Destinatário |
| tipo | VARCHAR(40) | Sim | — | Tipo do evento |
| titulo | VARCHAR | Sim | — | Título da notificação |
| mensagem | TEXT | Sim | — | Conteúdo |
| link | VARCHAR | Não | — | URL de ação |
| lida | BOOLEAN | Sim | Default: `false` | Status de leitura |
| relacionado_type | VARCHAR | Não | Polimórfico | Tipo da entidade relacionada |
| relacionado_id | BIGINT | Não | Polimórfico | ID da entidade relacionada |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Índice:** `(user_id, lida)`

**Tipos comuns:** `agendamento`, `orcamento`, `nova_atribuicao`, `prazo_excedido`, `reagendamento`, `reagendamento_aprovado`, `reagendamento_recusado`, `estoque_baixo`

---

### 3.15 `configuracoes_sistema` — Identidade visual (whitelabel)

| Atributo | Tipo | Obrigatório | Restrições | Descrição |
|----------|------|-------------|------------|-----------|
| id | BIGINT | Sim | PK | Identificador único |
| nome_empresa | VARCHAR | Sim | Default: "Sistema de Oficina" | Nome exibido |
| cor_primaria | VARCHAR(7) | Sim | Default: #FF6B35 | Cor principal (#RRGGBB) |
| cor_secundaria | VARCHAR(7) | Sim | Default: #FF8C42 | Cor secundária |
| tema | VARCHAR(10) | Sim | Default: `light` | `light` ou `dark` |
| logo_path | VARCHAR | Não | — | Caminho da logo |
| created_at / updated_at | TIMESTAMP | Sim | — | Auditoria |

**Observação:** registro singleton (uma configuração ativa por instalação).

---

### 3.16 Tabelas de infraestrutura Laravel

| Tabela | Finalidade |
|--------|------------|
| `password_resets` | Tokens de recuperação de senha (painel admin) |
| `personal_access_tokens` | Tokens Sanctum para API |
| `failed_jobs` | Registro de jobs de fila com falha |

---

## 4. Relacionamentos detalhados

| Entidade origem | Cardinalidade | Entidade destino | Descrição |
|-----------------|---------------|------------------|-----------|
| Cliente | 1:N | Veículo | Um cliente possui um ou mais veículos |
| Cliente | 1:N | Ordem de Serviço | Um cliente pode ter várias OS |
| Cliente | 1:N | Agendamento | Cliente cadastrado pode agendar serviços |
| Cliente | 1:N | Avaliação NPS | Cliente pode avaliar OS concluídas |
| Veículo | N:1 | Cliente | Todo veículo pertence a um cliente |
| Veículo | 1:N | Ordem de Serviço | Veículo pode ter histórico de OS |
| Veículo | 1:N | Agendamento | Veículo pode ser referenciado em agendamentos |
| Ordem de Serviço | N:1 | Cliente | Toda OS tem um cliente |
| Ordem de Serviço | N:1 | Veículo | Toda OS tem um veículo |
| Ordem de Serviço | 1:N | Item OS | OS contém itens de serviço/peça |
| Ordem de Serviço | 1:N | Checklist | OS pode ter inspeção por pontos |
| Ordem de Serviço | 1:N | Status Atualização | OS possui histórico de comunicação |
| Ordem de Serviço | 1:N | Atribuição Serviço | OS pode ter tarefas para mecânicos |
| Ordem de Serviço | 1:1 | Avaliação NPS | Máximo uma avaliação por OS |
| Ordem de Serviço | 1:1 | Agendamento | OS pode originar-se de um agendamento |
| Serviço/Peça | 1:N | Item OS | Catálogo compõe linhas de OS |
| Checklist | 1:N | Foto Checklist | Cada ponto pode ter fotos |
| User | 1:N | Atribuição (mecânico) | Mecânico executa tarefas |
| User | 1:N | Atribuição (atribuidor) | Admin atribui tarefas |
| User | 1:N | Notificação | Usuário recebe alertas |
| User | 1:N | Despesa | Usuário pode lançar despesas |

---

## 5. Cardinalidades e integridade referencial

### 5.1 Regras de exclusão

| Relacionamento | Comportamento | Impacto |
|----------------|---------------|---------|
| OS → Status Atualizações | CASCADE | Excluir OS remove histórico |
| OS → Atribuições | CASCADE | Excluir OS remove atribuições |
| Cliente → Agendamentos | CASCADE | Excluir cliente remove agendamentos |
| Checklist → Fotos | Implícito (aplicação) | Exclusão remove arquivos do storage |

### 5.2 Proteções na aplicação (não CASCADE)

| Entidade | Proteção |
|----------|----------|
| Cliente | Não pode ser excluído se possuir OS |
| Veículo | Não pode ser excluído se possuir OS |
| Serviço/Peça | Não pode ser excluído se usado em item de OS |
| User (mecânico) | Não pode ser excluído se possuir atribuições |

---

## 6. Domínios de valores (enumerations)

| Entidade | Atributo | Valores permitidos |
|----------|----------|-------------------|
| users | role | `admin`, `funcionario` |
| servicos_pecas | tipo | `servico`, `peca` |
| ordens_servico | status | `aberta`, `em_andamento`, `concluida`, `cancelada` |
| ordens_servico | status_pagamento | `pago`, `pendente` |
| checklists | condicao | `otimo`, `bom`, `regular`, `ruim`, `pessimo` |
| atribuicoes_servico | status | `pendente`, `em_andamento`, `paralisado`, `excedido`, `concluido` |
| atribuicoes_servico | reagendamento_status | `pendente`, `aprovado`, `recusado` |
| agendamentos | status | `pendente`, `aprovado`, `recusado`, `concluido` |
| despesas | categoria | `contas_a_pagar`, `salario_mecanicos`, `impostos_taxas`, `prejuizos_retrabalhos` |
| configuracoes_sistema | tema | `light`, `dark` |

---

## 7. Fluxos principais no modelo

### 7.1 Agendamento → Ordem de Serviço

```
Agendamento (pendente)
    → Aprovação admin + mecânico + prazo
    → Cliente/Veículo criados ou vinculados
    → Ordem de Serviço (aberta)
    → Item OS (se serviço existir no catálogo)
    → Atribuição Serviço (pendente)
    → Status Atualização
    → Agendamento.ordem_servico_id preenchido
```

### 7.2 Ciclo de vida da OS

```
aberta → em_andamento → concluida
       ↘ cancelada
```

### 7.3 Estoque integrado aos itens

```
Adicionar peça → validar estoque → baixar → estoque_baixado = true
Remover item → restaurar estoque
Cancelar OS → restaurar todo estoque
Excluir OS → restaurar estoque antes da exclusão
```

---

## 8. Observações técnicas

1. **Dois universos de autenticação:** `users` (painel interno) e `clientes.senha_portal` (portal) utilizam sessões independentes.
2. **Numeração de OS:** gerada automaticamente no formato `AAAA000001`, reiniciando a sequência a cada ano.
3. **Enums como string:** valores de domínio são armazenados como VARCHAR e validados na camada de aplicação (compatível com PostgreSQL).
4. **Configuração whitelabel:** cache de 60 segundos; cores propagadas para interface web e PDFs.
5. **Notificações polimórficas:** campo `relacionado_type/id` permite vincular a diferentes entidades do sistema.

---

*Documento gerado com base na análise das migrations, models Eloquent e controllers do sistema, sem alteração de código.*
