# Regras de Negócio
## Sistema de Oficina Mecânica

**Projeto:** Sistema de Oficina  
**Versão do documento:** 1.0  
**Data:** 28/08/2026  

---

## 1. Introdução

Este documento descreve as regras de negócio implementadas no Sistema de Oficina, organizadas por módulo funcional. Cada regra indica o comportamento esperado do sistema, restrições aplicáveis e consequências de violação.

---

## 2. Autenticação, autorização e perfis de acesso

### RN-AUTH-01 — Perfis de usuário interno
O sistema possui dois perfis internos:
- **Administrador (`admin`):** acesso total ao painel, incluindo financeiro, relatórios, funcionários, agendamentos, configurações de tema e gestão completa de atribuições.
- **Funcionário/Mecânico (`funcionario`):** acesso operacional limitado às OS e atribuições sob sua responsabilidade.

### RN-AUTH-02 — Administrador principal
O usuário com identificador `id = 1` é sempre tratado como administrador, independentemente de alterações no campo `role`.

### RN-AUTH-03 — Conta ativa
Usuários com `ativo = false` não podem permanecer autenticados. Ao tentar acessar o sistema, a sessão é encerrada automaticamente.

### RN-AUTH-04 — Registro público restrito
O registro de novos usuários internos via tela pública só é permitido quando não existe nenhum usuário cadastrado (bootstrap inicial). Após isso, novos funcionários só podem ser criados por um administrador.

### RN-AUTH-05 — Senha do painel interno
A senha de usuários internos deve ter no mínimo 8 caracteres. A recuperação de senha utiliza token enviado por e-mail; em ambiente local sem SMTP configurado, o link de redefinição pode ser exibido na própria tela.

### RN-AUTH-06 — Isolamento de visibilidade (mecânico)
Mecânicos visualizam apenas Ordens de Serviço que possuem atribuição vinculada ao seu `user_id`. Não têm acesso a OS sem atribuição ou atribuídas a outros mecânicos.

### RN-AUTH-07 — Portal do cliente (autenticação separada)
O portal utiliza autenticação independente via `clientes.senha_portal`. A sessão do portal (`cliente_id`) é distinta da sessão do painel interno (`users`).

### RN-AUTH-08 — Consulta pública com segundo fator
A consulta pública de OS exige **CPF + placa do veículo** como segundo fator de identificação. Alternativamente, o acesso via **QR Code** (token único do cliente) dispensa a placa, com sessão válida por 1 hora.

### RN-AUTH-09 — Primeiro acesso ao portal
No primeiro acesso, o cliente informa CPF, e-mail (deve coincidir com o cadastro) e define senha (mínimo 6 caracteres). Se o cliente já possui senha de portal definida, a operação é rejeitada para evitar sobrescrita não autorizada.

### RN-AUTH-10 — Proteção contra takeover de conta
Se o e-mail informado no primeiro acesso pertencer a outro cliente, a operação é bloqueada. Não é permitido redefinir senha de portal por meio do fluxo de primeiro acesso quando a senha já existe.

---

## 3. Clientes

### RN-CLI-01 — CPF obrigatório e único
Todo cliente deve possuir CPF com 11 dígitos numéricos, normalizado (sem pontuação) e único no sistema.

### RN-CLI-02 — E-mail único
Quando informado, o e-mail do cliente deve ser único no cadastro.

### RN-CLI-03 — Exclusão condicionada
Cliente não pode ser excluído se possuir Ordens de Serviço vinculadas.

### RN-CLI-04 — Ativação do portal
O acesso ao portal só é permitido após ativação explícita (`acesso_portal = true`) por um administrador ou funcionário autorizado.

### RN-CLI-05 — Envio de credenciais
O envio de credenciais de acesso ao portal exige e-mail cadastrado e portal ativo. Se o serviço de e-mail (SMTP) não estiver configurado, o sistema retorna erro explícito — não simula sucesso.

### RN-CLI-06 — Token QR Code
Cada cliente pode possuir um `token_acesso` único (32 caracteres) para acesso rápido via QR Code, direcionando à consulta pública autenticada.

### RN-CLI-07 — Preferências de notificação
O cliente possui flags de preferência (`notificar_email`, `notificar_whatsapp`). A lógica de WhatsApp está reservada para implementação futura.

---

## 4. Veículos

### RN-VEI-01 — Vinculação obrigatória
Todo veículo deve estar vinculado a um cliente existente.

### RN-VEI-02 — Placa única e normalizada
A placa deve conter 7 caracteres alfanuméricos, convertida para maiúsculas, e ser única globalmente no sistema.

### RN-VEI-03 — Ano válido
O ano de fabricação deve estar entre 1900 e o ano corrente + 1.

### RN-VEI-04 — Exclusão condicionada
Veículo não pode ser excluído se possuir Ordens de Serviço vinculadas.

### RN-VEI-05 — Reutilização de placa em agendamentos
Ao aprovar agendamento de visitante, se já existir veículo com a mesma placa, ele só é reutilizado se pertencer ao **mesmo cliente**. Caso contrário, um novo veículo é criado para evitar associação incorreta.

---

## 5. Catálogo de serviços e peças

### RN-CAT-01 — Tipos de item
Cada registro do catálogo é classificado como `servico` ou `peca`.

### RN-CAT-02 — Controle de estoque (peças)
Apenas itens do tipo `peca` possuem controle de estoque (`quantidade_estoque`, `estoque_minimo`).

### RN-CAT-03 — Alerta de estoque baixo
Quando `quantidade_estoque ≤ estoque_minimo` para uma peça ativa, administradores recebem notificação in-app.

### RN-CAT-04 — Ativação/Inativação
Itens inativos não devem aparecer para seleção em novas OS, mas permanecem no histórico de OS existentes.

### RN-CAT-05 — Exclusão condicionada
Serviço ou peça não pode ser excluído se estiver referenciado em algum item de Ordem de Serviço.

---

## 6. Ordens de Serviço (OS)

### RN-OS-01 — Numeração automática
Cada OS recebe número único no formato `AAAA` + 6 dígitos sequenciais (ex.: `2026000001`), reiniciando a cada ano civil.

### RN-OS-02 — Dados obrigatórios na criação
Para criar uma OS são obrigatórios: cliente, veículo (que deve pertencer ao cliente), data de entrada.

### RN-OS-03 — Ciclo de status
```
aberta → em_andamento → concluida
       ↘ cancelada
```
Transições inválidas devem ser rejeitadas pela aplicação.

### RN-OS-04 — Cálculo automático de totais
O `valor_total` da OS é recalculado automaticamente como soma dos `valor_total` de seus itens.

### RN-OS-05 — Itens da OS
- Quantidade mínima: 1.
- Valor unitário é copiado do catálogo no momento da inclusão.
- Valor total do item = quantidade × valor unitário.

### RN-OS-06 — Status de pagamento
Cada OS possui status de pagamento independente: `pago` ou `pendente`. Utilizado pelo módulo financeiro.

### RN-OS-07 — Comunicação com cliente (status updates)
Atualizações de status podem incluir mensagem visível no portal (máximo 500 caracteres). São registradas em `status_atualizacoes` com data, usuário e status anterior/novo.

### RN-OS-08 — Notificação por e-mail
O envio de notificação ao cliente exige e-mail cadastrado. Se SMTP não estiver configurado, retorna erro — não informa sucesso falso.

### RN-OS-09 — Geração de PDF
A OS pode ser exportada em PDF (formato A4, retrato), utilizando as cores e identidade visual configuradas no sistema.

### RN-OS-10 — Exclusão com restauração de estoque
Antes de excluir uma OS, todo estoque de peças debitado por seus itens deve ser restaurado.

---

## 7. Estoque integrado à OS

### RN-EST-01 — Baixa imediata ao adicionar peça
Ao incluir peça na OS, o sistema valida disponibilidade e debita o estoque imediatamente, marcando `estoque_baixado = true` no item.

### RN-EST-02 — Restauração ao remover item
Ao remover item de peça da OS, o estoque é restaurado na quantidade correspondente.

### RN-EST-03 — Baixa residual na transição para "em andamento"
Ao mudar status da OS para `em_andamento`, itens de peça ainda não baixados têm estoque debitado.

### RN-EST-04 — Restauração total ao cancelar
Ao cancelar a OS, todo estoque debitado por seus itens é restaurado.

### RN-EST-05 — Concorrência
Operações de baixa/restauração utilizam transação de banco com `lockForUpdate` para evitar condições de corrida.

### RN-EST-06 — Serviços não afetam estoque
Itens do tipo `servico` não alteram quantidade em estoque.

---

## 8. Checklist de inspeção

### RN-CHK-01 — Pontos fixos de inspeção
O checklist possui 12 pontos predefinidos do veículo: frente esquerda/direita, lateral esquerda/direita, traseira esquerda/direita, capô, teto, para-brisa, vidro traseiro, interior e motor.

### RN-CHK-02 — Condições avaliativas
Cada ponto recebe condição: `otimo`, `bom`, `regular`, `ruim` ou `pessimo`.

### RN-CHK-03 — Um registro por ponto
Existe no máximo um registro de checklist por combinação `(ordem_servico_id, ponto_veiculo)`. Novas submissões atualizam o registro existente (upsert).

### RN-CHK-04 — Fotos por ponto
Cada item de checklist pode ter fotos anexadas. Formatos aceitos: JPEG, PNG, JPG. Tamanho máximo: 5 MB por arquivo.

### RN-CHK-05 — Armazenamento de fotos
Fotos são salvas em `storage/app/public/checklist/{numero_ordem}/`.

### RN-CHK-06 — Exclusão de fotos e itens
Ao excluir foto ou item de checklist, o arquivo físico correspondente é removido do storage.

### RN-CHK-07 — Acesso restrito (mecânico)
Mecânicos só acessam checklist de OS atribuídas a eles.

---

## 9. Atribuições de serviço e SLA

### RN-ATR-01 — Criação por administrador
Novas atribuições são criadas exclusivamente por administradores, vinculando OS, mecânico, título e prazo (SLA).

### RN-ATR-02 — Mecânico ativo
O mecânico selecionado deve ser usuário com perfil `funcionario` e `ativo = true`.

### RN-ATR-03 — Status da atribuição
Valores permitidos: `pendente`, `em_andamento`, `paralisado`, `excedido`, `concluido`.

### RN-ATR-04 — Progresso
O mecânico pode informar progresso de 0 a 100%. Mecânicos não podem definir manualmente status `excedido`.

### RN-ATR-05 — SLA automático
Ao listar atribuições, o sistema verifica prazos vencidos e marca automaticamente como `excedido`, gerando alerta único para mecânico e administradores.

### RN-ATR-06 — Reagendamento de prazo
O mecânico pode solicitar prorrogação informando motivo (mínimo 5 caracteres) e novo prazo futuro. A solicitação fica com `reagendamento_status = pendente` até decisão do admin.

### RN-ATR-07 — Aprovação/Recusa de reagendamento
Administrador pode aprovar (atualiza prazo, limpa alerta) ou recusar a solicitação de prorrogação.

### RN-ATR-08 — Visibilidade do mecânico
Mecânico visualiza e atualiza apenas suas próprias atribuições.

### RN-ATR-09 — Notificação de nova atribuição
Ao criar atribuição (manual ou via aprovação de agendamento), o mecânico designado recebe notificação in-app.

### RN-ATR-10 — Exclusão de atribuição
Apenas administradores podem excluir atribuições.

---

## 10. Agendamentos

### RN-AGE-01 — Agendamento por cliente logado
Cliente autenticado no portal seleciona veículo cadastrado, serviço, data futura e horário.

### RN-AGE-02 — Agendamento por visitante (sem cadastro)
Visitantes informam: nome, CPF (11 dígitos), telefone, dados completos do veículo (marca, modelo, placa, ano, cor), serviço, data futura e horário. Flag `eh_visitante = true`.

### RN-AGE-03 — Data futura obrigatória
A data agendada deve ser posterior à data atual.

### RN-AGE-04 — Vinculação por CPF existente
Se visitante informar CPF já cadastrado, o agendamento pode ser vinculado ao cliente existente.

### RN-AGE-05 — Status do agendamento
Valores: `pendente`, `aprovado`, `recusado`, `concluido`.

### RN-AGE-06 — Aprovação com atribuição inteligente
Ao aprovar agendamento `pendente`:
1. Administrador deve selecionar mecânico e definir prazo (SLA).
2. Sistema cria ou vincula cliente e veículo (para visitantes).
3. Sistema gera OS com status `aberta`.
4. Se existir serviço correspondente no catálogo, cria item na OS.
5. Sistema cria atribuição para o mecânico selecionado.
6. Sistema registra atualização de status na OS.
7. Agendamento recebe `ordem_servico_id` e status `aprovado`.
8. Mecânico é notificado.

### RN-AGE-07 — Não reprocessar aprovação
Agendamento que já possui OS vinculada não pode ser aprovado novamente.

### RN-AGE-08 — CPF temporário para visitante
Se visitante não informar CPF válido, sistema pode gerar CPF temporário no formato `TEMP{timestamp}` para permitir cadastro.

### RN-AGE-09 — Painel de carga de mecânicos
Na aprovação, o sistema exibe carga de trabalho de cada mecânico: tarefas ativas, indicador de disponibilidade (Livre / Carga Moderada / Carga Máxima) e lista resumida de OS em execução.

### RN-AGE-10 — Recusa de agendamento
Administrador pode recusar agendamento informando `resposta_admin`. Status muda para `recusado`.

---

## 11. Portal do cliente

### RN-POR-01 — Dashboard autenticado
Cliente logado visualiza suas OS, veículos e histórico de serviços.

### RN-POR-02 — Detalhe de OS (logado)
Cliente autenticado acessa detalhes completos de suas OS, incluindo itens, valores, histórico de status e opção de avaliação NPS.

### RN-POR-03 — Detalhe de OS (público)
Consulta pública exibe informações da OS após validação CPF + placa ou acesso QR, com sessão válida por 1 hora.

### RN-POR-04 — Avaliação NPS
Cliente pode avaliar OS **concluída** com nota de 0 a 10 e comentário opcional. Apenas **uma avaliação por OS** é permitida.

### RN-POR-05 — Solicitação de orçamento
Cliente pode solicitar orçamento; a solicitação gera notificação para administradores.

### RN-POR-06 — Lembretes de revisão
Funcionalidade de lembretes orienta o cliente a agendar nova revisão (sugestão: 6 meses após última OS concluída).

### RN-POR-07 — Etapas amigáveis no portal
Status técnicos da OS são traduzidos para linguagem amigável ao cliente (ex.: `aberta` → "Na Recepção / Em Diagnóstico").

---

## 12. Financeiro

### RN-FIN-01 — Competência mensal
Relatórios financeiros utilizam competência mensal (`AAAA-MM`).

### RN-FIN-02 — Faturamento
Faturamento = soma do `valor_total` de OS com `status_pagamento = pago` no mês de referência.

### RN-FIN-03 — Despesas
Despesas são registradas por categoria, valor e `data_despesa`. Categorias: contas a pagar, salários de mecânicos, impostos/taxas, prejuízos/retrabalhos.

### RN-FIN-04 — Resultado líquido
Resultado líquido = faturamento bruto − total de despesas do período.

### RN-FIN-05 — Atualização de pagamento
Administrador pode alterar `status_pagamento` de OS individualmente.

### RN-FIN-06 — Relatório PDF mensal
Relatório financeiro mensal pode ser exportado em PDF com identidade visual do sistema.

### RN-FIN-07 — Acesso restrito
Módulo financeiro acessível apenas por administradores.

---

## 13. Funcionários

### RN-FUN-01 — CRUD restrito
Apenas administradores gerenciam funcionários (criar, editar, ativar/desativar, excluir).

### RN-FUN-02 — Auto-proteção
Administrador não pode desativar ou excluir a própria conta.

### RN-FUN-03 — Exclusão condicionada
Funcionário não pode ser excluído se possuir atribuições de serviço (como mecânico ou como atribuidor). Recomenda-se desativação.

### RN-FUN-04 — Desativação vs exclusão
Funcionário desativado (`ativo = false`) permanece no histórico, mas não pode acessar o sistema.

---

## 14. Notificações in-app

### RN-NOT-01 — Destinatários
Notificações são direcionadas a usuários internos (`users`), não a clientes.

### RN-NOT-02 — Tipos de evento
Eventos que geram notificação: novo agendamento, solicitação de orçamento, nova atribuição, prazo excedido, solicitação de reagendamento, reagendamento aprovado/recusado, estoque baixo.

### RN-NOT-03 — Marcação de leitura
Usuário pode marcar notificação individual ou todas como lidas. Notificações com link redirecionam para a tela relacionada.

### RN-NOT-04 — Notificação a administradores
Eventos relevantes ao negócio (agendamentos, orçamentos, estoque) notificam todos os administradores ativos.

---

## 15. Configurações e identidade visual (Whitelabel)

### RN-TEM-01 — Acesso restrito
Configurações de tema acessíveis apenas por administradores.

### RN-TEM-02 — Personalização
Administrador pode configurar: nome da empresa, cor primária (#RRGGBB), cor secundária, tema (`light`/`dark`) e logo.

### RN-TEM-03 — Upload de logo
Formatos aceitos: PNG, JPG, WEBP, SVG. Tamanho máximo: 2 MB.

### RN-TEM-04 — Propagação visual
Cores configuradas são aplicadas em toda a interface web, telas de autenticação, portal do cliente e documentos PDF gerados.

### RN-TEM-05 — Cache
Configurações são cacheadas por 60 segundos para performance.

---

## 16. Relatórios gerenciais

### RN-REL-01 — Acesso restrito
Relatórios acessíveis apenas por administradores.

### RN-REL-02 — Relatório financeiro
Exibe receita, ticket médio, OS pagas e principais indicadores do período.

### RN-REL-03 — Relatório de serviços
Exibe serviços mais executados, peças mais vendidas e distribuição de status de OS.

### RN-REL-04 — Relatório de clientes
Exibe clientes por faturamento, frequência e novos cadastros.

---

## 17. Copiloto de IA

### RN-IA-01 — Assistente integrado
Sistema possui widget de copiloto para auxiliar usuários internos.

### RN-IA-02 — Modos de operação
Pode operar em modo local (respostas pré-definidas) ou com LLM externa (OpenAI-compatible), dependendo de configuração de `AI_API_KEY`.

---

## 18. Regras transversais

### RN-TRV-01 — Proteção CSRF
Todos os formulários web utilizam proteção CSRF (token Laravel).

### RN-TRV-02 — Hash de senhas
Senhas de usuários internos e portal de clientes são armazenadas com bcrypt.

### RN-TRV-03 — Paginação
Listagens utilizam paginação (10 a 20 registros por página).

### RN-TRV-04 — Validação na aplicação
Enums e domínios de valores são validados na camada de aplicação (controllers/requests), não como ENUM nativo do PostgreSQL.

### RN-TRV-05 — Integridade referencial
Exclusões que violariam integridade referencial são bloqueadas com mensagem amigável ao usuário.

### RN-TRV-06 — Health check
Endpoint `/up` disponível para verificação de saúde da aplicação.

---

## 19. Regras pendentes ou parcialmente implementadas

| Código | Regra | Status |
|--------|-------|--------|
| RN-PEN-01 | Envio real de e-mails ao cliente (OS, credenciais portal) | Estrutura existe; retorna erro se SMTP não configurado |
| RN-PEN-02 | Notificação WhatsApp | Campo existe no cadastro; lógica não implementada |
| RN-PEN-03 | Recuperação de senha do portal do cliente | Não implementado |
| RN-PEN-04 | Processamento SLA via job agendado (cron) | SLA verificado on-demand na listagem |
| RN-PEN-05 | API REST completa | Apenas endpoints pontuais (veículos por cliente, check-auth) |

---

*Documento gerado com base na análise dos controllers, models, middlewares e services do sistema, sem alteração de código.*
