# Copiloto de IA — Sistema Oficina

## Estudo de viabilidade (escolha técnica)

| Opção | Adequação a este projeto | Veredito |
|-------|--------------------------|----------|
| **LangChain / LangGraph** | Excelente para agentes complexos, mas é ecossistema **Python**. Exigiria microserviço separado + fila + deploy extra no XAMPP. | ❌ Overkill agora |
| **Vercel AI SDK** | Ótimo em **Next.js/Node**. O frontend atual é Blade + Alpine, sem app React. | ❌ Não encaixa |
| **Function Calling (OpenAI-compatible) no Laravel** | O LLM decide quais *tools* chamar; o PHP executa com Eloquent/validação já existentes. Uma rota, um widget, sem stack nova. | ✅ **Escolhido** |

Arquitetura adotada:

1. **UI**: ícone flutuante + drawer de chat (Alpine.js) no layout admin.
2. **API**: `POST /copiloto/chat` autenticação Laravel.
3. **Orquestração**: `CopilotoService` com loop de tool calls.
4. **Ferramentas**: `CopilotoTools` (ajuda, resumos, buscar/cadastrar cliente e veículo, listar OS, financeiro, despesas).
5. **Fallback**: se não houver `AI_API_KEY`, motor local interpreta comandos em português e chama as **mesmas** tools.

Provedores suportados (API estilo OpenAI): OpenAI, OpenRouter, Groq, etc.

## Configuração

No arquivo `.env`:

```env
AI_COPILOTO_ENABLED=true
AI_API_KEY=
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-4o-mini
```

### Exemplos de provedores

**OpenAI**
```env
AI_API_KEY=sk-...
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-4o-mini
```

**OpenRouter**
```env
AI_API_KEY=sk-or-...
AI_BASE_URL=https://openrouter.ai/api/v1
AI_MODEL=openai/gpt-4o-mini
```

**Groq**
```env
AI_API_KEY=gsk_...
AI_BASE_URL=https://api.groq.com/openai/v1
AI_MODEL=llama-3.3-70b-versatile
```

Sem chave: o Copiloto funciona em **modo local** (comandos estruturados).

Depois de alterar o `.env`:
```powershell
$env:Path = "C:\php;" + $env:Path
php artisan config:clear
```

## Como usar

1. Faça login no sistema admin.
2. Clique no ícone **robô** no canto inferior direito.
3. Exemplos:
   - `ajuda ordens`
   - `resumo da oficina`
   - `resumo financeiro`
   - `cadastrar cliente Maria Souza CPF 529.982.247-25 telefone 11988887777`
   - `buscar cliente Maria`
   - `listar ordens pendentes`
   - `cadastrar despesa Conta de luz 350,00 impostos_taxas`

Microfone: ditado via Web Speech API (Chrome/Edge).

## Segurança

- Só usuários autenticados (`auth` + `checkActive`).
- Financeiro/despesas/pagamento: apenas **admin**.
- Validação Laravel em todos os cadastros (CPF único, etc.).
- O LLM **não** acessa o banco diretamente — só via tools PHP.

## Arquivos principais

- `config/ai.php`
- `app/Services/Ai/CopilotoService.php`
- `app/Services/Ai/CopilotoTools.php`
- `app/Http/Controllers/AiCopilotoController.php`
- `resources/views/partials/copiloto-widget.blade.php`
- Rotas: `copiloto.status`, `copiloto.chat`
