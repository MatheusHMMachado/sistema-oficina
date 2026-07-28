<?php

namespace App\Services\Ai;

/**
 * Base de tutoriais, FAQs e dúvidas frequentes do Sistema Oficina.
 */
class CopilotoTutoriais
{
    public static function catalogo(): array
    {
        return [
            'geral' => self::geral(),
            'clientes' => self::clientes(),
            'veiculos' => self::veiculos(),
            'servicos_pecas' => self::servicosPecas(),
            'ordens' => self::ordens(),
            'checklist' => self::checklist(),
            'financeiro' => self::financeiro(),
            'portal' => self::portal(),
            'atribuicoes' => self::atribuicoes(),
            'funcionarios' => self::funcionarios(),
            'dashboard' => self::dashboard(),
            'copiloto' => self::copiloto(),
            'pdf' => self::pdf(),
            'duvidas' => self::duvidasGerais(),
        ];
    }

    public static function listarTemas(): array
    {
        return [
            'geral' => 'Visão geral do sistema',
            'clientes' => 'Cadastrar e gerenciar clientes',
            'veiculos' => 'Cadastrar e gerenciar veículos',
            'servicos_pecas' => 'Cadastrar serviços e peças',
            'ordens' => 'Ordens de serviço (OS)',
            'checklist' => 'Checklist e fotos',
            'financeiro' => 'Controle financeiro e despesas',
            'portal' => 'Portal do cliente',
            'atribuicoes' => 'Atribuições de serviço',
            'funcionarios' => 'Funcionários (admin)',
            'dashboard' => 'Dashboard',
            'copiloto' => 'Usar este Copiloto (chat)',
            'pdf' => 'Gerar PDF da OS',
            'duvidas' => 'Dúvidas frequentes e soluções',
        ];
    }

    public static function obter(string $topico): array
    {
        $key = self::normalizarTopico($topico);
        $catalogo = self::catalogo();
        $dados = $catalogo[$key] ?? $catalogo['geral'];

        return [
            'ok' => true,
            'topico' => $key,
            'titulo' => self::listarTemas()[$key] ?? 'Ajuda',
            'tutorial' => $dados['tutorial'],
            'duvidas' => $dados['duvidas'],
            'comandos_chat' => $dados['comandos_chat'] ?? [],
            'temas_disponiveis' => self::listarTemas(),
        ];
    }

    public static function formatarResposta(array $dados): string
    {
        $out = "📚 {$dados['titulo']}\n\n";
        $out .= "🧾 TUTORIAL (passo a passo)\n{$dados['tutorial']}\n\n";
        $out .= "❓ DÚVIDAS E SOLUÇÕES\n";
        foreach ($dados['duvidas'] as $i => $d) {
            $n = $i + 1;
            $out .= "{$n}. {$d['pergunta']}\n   → {$d['resposta']}\n";
        }
        if (! empty($dados['comandos_chat'])) {
            $out .= "\n💬 VOCÊ TAMBÉM PODE PEDIR NO CHAT:\n";
            foreach ($dados['comandos_chat'] as $cmd) {
                $out .= "• {$cmd}\n";
            }
        }
        $out .= "\n📌 Outros tutoriais: diga \"lista de tutoriais\" ou \"tutorial clientes\", \"tutorial peças\", etc.";

        return trim($out);
    }

    public static function normalizarTopico(string $topico): string
    {
        $t = mb_strtolower(trim($topico));
        $t = str_replace(['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'], ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'], $t);

        $mapa = [
            'cliente' => 'clientes',
            'cadastrar cliente' => 'clientes',
            'cadastro de cliente' => 'clientes',
            'veiculo' => 'veiculos',
            'veiculos' => 'veiculos',
            'carro' => 'veiculos',
            'placa' => 'veiculos',
            'peca' => 'servicos_pecas',
            'pecas' => 'servicos_pecas',
            'servico' => 'servicos_pecas',
            'servicos' => 'servicos_pecas',
            'servicos e pecas' => 'servicos_pecas',
            'servicos_pecas' => 'servicos_pecas',
            'catalogo' => 'servicos_pecas',
            'ordem' => 'ordens',
            'ordens' => 'ordens',
            'os' => 'ordens',
            'ordem de servico' => 'ordens',
            'check list' => 'checklist',
            'checklist' => 'checklist',
            'foto' => 'checklist',
            'fotos' => 'checklist',
            'financeiro' => 'financeiro',
            'despesa' => 'financeiro',
            'despesas' => 'financeiro',
            'caixa' => 'financeiro',
            'portal' => 'portal',
            'cliente portal' => 'portal',
            'atribuicao' => 'atribuicoes',
            'atribuicoes' => 'atribuicoes',
            'mecanico' => 'atribuicoes',
            'funcionario' => 'funcionarios',
            'funcionarios' => 'funcionarios',
            'usuario' => 'funcionarios',
            'dashboard' => 'dashboard',
            'inicio' => 'dashboard',
            'painel' => 'dashboard',
            'ia' => 'copiloto',
            'copiloto' => 'copiloto',
            'chat' => 'copiloto',
            'pdf' => 'pdf',
            'nota' => 'pdf',
            'duvida' => 'duvidas',
            'duvidas' => 'duvidas',
            'erro' => 'duvidas',
            'problema' => 'duvidas',
            'faq' => 'duvidas',
            'geral' => 'geral',
            'sistema' => 'geral',
            'tudo' => 'geral',
        ];

        if (isset($mapa[$t])) {
            return $mapa[$t];
        }

        foreach ($mapa as $chave => $valor) {
            if (str_contains($t, $chave)) {
                return $valor;
            }
        }

        return array_key_exists($t, self::listarTemas()) ? $t : 'geral';
    }

    private static function geral(): array
    {
        return [
            'tutorial' => "1. Faça login no sistema administrativo.\n"
                . "2. Use o menu lateral: Dashboard, Clientes, Veículos, Ordens, Serviços e Peças, Checklist, Atribuições.\n"
                . "3. Admin também vê: Controle Financeiro e Funcionários.\n"
                . "4. Fluxo típico: cadastrar Cliente → Veículo → abrir OS → adicionar itens → checklist/fotos → feedback → PDF → marcar pagamento no Financeiro.\n"
                . "5. O ícone do robô (canto inferior direito) abre este Copiloto para tutoriais e cadastros rápidos.",
            'duvidas' => [
                [
                    'pergunta' => 'Por onde começo se é a primeira vez?',
                    'resposta' => 'Cadastre um cliente, depois o veículo dele, depois abra uma Ordem de Serviço. Peça no chat: "tutorial clientes".',
                ],
                [
                    'pergunta' => 'Não acho um menu (ex.: Financeiro).',
                    'resposta' => 'Controle Financeiro e Funcionários aparecem só para administrador. Confirme seu usuário admin.',
                ],
                [
                    'pergunta' => 'O sistema não abre / erro de PHP?',
                    'resposta' => 'Use PHP 8.4 (C:\\php) e `php artisan serve` ou o serve.bat. O PHP 8.2 do XAMPP não roda esta versão.',
                ],
            ],
            'comandos_chat' => [
                'lista de tutoriais',
                'tutorial clientes',
                'tutorial veículos',
                'tutorial peças',
                'dúvidas frequentes',
            ],
        ];
    }

    private static function clientes(): array
    {
        return [
            'tutorial' => "PELA TELA:\n"
                . "1. Menu Clientes → botão Novo Cliente.\n"
                . "2. Preencha Nome (obrigatório) e CPF (obrigatório e único).\n"
                . "3. Opcional: RG, endereço, telefone, celular, e-mail.\n"
                . "4. Salvar. Depois abra o cliente para ver veículos/OS ou ativar portal.\n\n"
                . "PELO CHAT (Copiloto):\n"
                . "Digite: Cadastrar cliente Maria Souza CPF 529.982.247-25 telefone 11988887777",
            'duvidas' => [
                [
                    'pergunta' => 'Erro: CPF já cadastrado.',
                    'resposta' => 'Busque o cliente existente: "buscar cliente 529.982.247-25". Não cadastre o mesmo CPF duas vezes.',
                ],
                [
                    'pergunta' => 'CPF inválido / não salva.',
                    'resposta' => 'Use 11 dígitos (com ou sem máscara). Ex.: 123.456.789-09 ou 12345678909.',
                ],
                [
                    'pergunta' => 'Como editar um cliente?',
                    'resposta' => 'Clientes → buscar → Editar. Altere os campos e salve.',
                ],
                [
                    'pergunta' => 'Como o cliente acessa o portal?',
                    'resposta' => 'No perfil do cliente, ative o portal. Ele consulta em /portal/consulta só com o CPF. Veja também "tutorial portal".',
                ],
            ],
            'comandos_chat' => [
                'Cadastrar cliente João Silva CPF 111.444.777-35 telefone 11999999999',
                'Buscar cliente Silva',
                'Tutorial clientes',
            ],
        ];
    }

    private static function veiculos(): array
    {
        return [
            'tutorial' => "PELA TELA:\n"
                . "1. Menu Veículos → Novo Veículo.\n"
                . "2. Selecione o Cliente dono do veículo.\n"
                . "3. Informe marca, modelo, cor, ano e placa (única).\n"
                . "4. Opcional: chassi e km. Salvar.\n\n"
                . "PELO CHAT:\n"
                . "Cadastrar veiculo CPF 529.982.247-25 marca Fiat modelo Uno placa ABC1D23 cor Prata ano 2018 km 45000\n"
                . "(O cliente precisa existir antes.)",
            'duvidas' => [
                [
                    'pergunta' => 'Erro: placa já cadastrada.',
                    'resposta' => 'Cada placa é única. Busque: "buscar veiculo ABC1D23".',
                ],
                [
                    'pergunta' => 'Não aparece o cliente na lista.',
                    'resposta' => 'Cadastre o cliente primeiro. Depois vincule o veículo a ele.',
                ],
                [
                    'pergunta' => 'Posso trocar o dono do veículo?',
                    'resposta' => 'Sim: Veículos → Editar → escolha outro cliente → Salvar.',
                ],
            ],
            'comandos_chat' => [
                'Cadastrar veiculo CPF 529.982.247-25 marca VW modelo Gol placa XYZ1A23 cor Branco ano 2020',
                'Buscar veiculo Gol',
                'Tutorial veículos',
            ],
        ];
    }

    private static function servicosPecas(): array
    {
        return [
            'tutorial' => "PELA TELA (geralmente admin):\n"
                . "1. Menu Serviços e Peças → Novo Item.\n"
                . "2. Escolha tipo: Serviço (mão de obra) ou Peça.\n"
                . "3. Descrição + valor unitário. Salvar.\n"
                . "4. Esses itens serão usados dentro da OS.\n\n"
                . "PELO CHAT (admin):\n"
                . "Cadastrar peça Filtro de óleo valor 45,90\n"
                . "Cadastrar serviço Alinhamento valor 120,00",
            'duvidas' => [
                [
                    'pergunta' => 'Não consigo cadastrar peça/serviço.',
                    'resposta' => 'Cadastro do catálogo é restrito a administrador. Peça acesso admin ou peça ao admin cadastrar.',
                ],
                [
                    'pergunta' => 'Como usar a peça na OS?',
                    'resposta' => 'Abra a OS → Adicionar item → selecione o serviço/peça e a quantidade. O valor total da OS recalcula.',
                ],
                [
                    'pergunta' => 'Posso desativar um item?',
                    'resposta' => 'Sim, na listagem use o botão de status (ativo/inativo) para não aparecer em novas OS.',
                ],
            ],
            'comandos_chat' => [
                'Cadastrar peça Pastilha de freio valor 89,90',
                'Cadastrar serviço Balanceamento valor 80',
                'Listar peças',
                'Tutorial peças',
            ],
        ];
    }

    private static function ordens(): array
    {
        return [
            'tutorial' => "PELA TELA:\n"
                . "1. Menu Ordens de Serviço → Nova OS.\n"
                . "2. Selecione cliente e veículo, data de entrada e km.\n"
                . "3. Salve a OS.\n"
                . "4. Adicione itens (serviços/peças) na tela da OS.\n"
                . "5. Atualize status (aberta / em andamento / concluída).\n"
                . "6. Envie feedback ao cliente (aparece no portal).\n"
                . "7. Gerar PDF para a nota.\n"
                . "8. No Financeiro, marque Pagamento como Pago quando receber.\n\n"
                . "PELO CHAT:\n"
                . "Abrir OS CPF 529.982.247-25 placa ABC1D23 km 50000\n"
                . "Listar ordens pendentes",
            'duvidas' => [
                [
                    'pergunta' => 'Não consigo gerar PDF.',
                    'resposta' => 'Abra a OS e clique em Gerar PDF. Se der erro de view, a view ordens/pdf precisa existir (já incluída no sistema).',
                ],
                [
                    'pergunta' => 'Valor total zerado.',
                    'resposta' => 'Adicione itens na OS. O total é a soma dos itens.',
                ],
                [
                    'pergunta' => 'Cliente não vê a atualização.',
                    'resposta' => 'Use o formulário de Feedback na OS. O cliente consulta pelo CPF no portal.',
                ],
                [
                    'pergunta' => 'Erro de rota ao editar OS.',
                    'resposta' => 'Atualize a página. As rotas usam o parâmetro {ordem}. Se persistir, limpe cache: php artisan route:clear',
                ],
            ],
            'comandos_chat' => [
                'Abrir OS CPF 529.982.247-25 placa ABC1D23',
                'Listar ordens abertas',
                'Listar ordens pendentes',
                'Tutorial ordens',
            ],
        ];
    }

    private static function checklist(): array
    {
        return [
            'tutorial' => "1. Menu Checklist → escolha a OS.\n"
                . "2. Preencha os itens do checklist do veículo.\n"
                . "3. Anexe fotos (estado de entrada/saída).\n"
                . "4. Salve. As fotos ficam no storage e aparecem no portal do cliente.",
            'duvidas' => [
                [
                    'pergunta' => 'Fotos não aparecem no portal.',
                    'resposta' => 'Rode `php artisan storage:link` uma vez. Confirme que as fotos foram salvas na OS.',
                ],
                [
                    'pergunta' => 'Não acho a OS no checklist.',
                    'resposta' => 'A OS precisa existir. Abra Ordens e confirme o número, depois volte ao Checklist.',
                ],
            ],
            'comandos_chat' => [
                'Tutorial checklist',
                'Ajuda fotos',
            ],
        ];
    }

    private static function financeiro(): array
    {
        return [
            'tutorial' => "1. Menu Controle Financeiro (admin).\n"
                . "2. KPIs: Valor Bruto (OS pagas), Despesas, Líquido, Pagos vs Pendentes.\n"
                . "3. Na tabela de OS, marque Pago/Pendente.\n"
                . "4. Cadastre despesas (descrição, valor, categoria).\n"
                . "5. O gráfico compara receita bruta × despesas.\n\n"
                . "PELO CHAT:\n"
                . "Resumo financeiro\n"
                . "Cadastrar despesa Conta de luz 350,00 impostos_taxas",
            'duvidas' => [
                [
                    'pergunta' => 'Valor bruto está zerado.',
                    'resposta' => 'Só entram OS com status de pagamento = Pago. Marque as OS pagas no Financeiro ou peça no chat.',
                ],
                [
                    'pergunta' => 'Quais categorias de despesa existem?',
                    'resposta' => 'Contas a Pagar, Salário de Mecânicos, Impostos/Taxas, Prejuízos/Retrabalhos.',
                ],
                [
                    'pergunta' => 'Não vejo o menu Financeiro.',
                    'resposta' => 'Somente administrador. Peça ao admin ou use usuário admin@oficina.com (ambiente local).',
                ],
            ],
            'comandos_chat' => [
                'Resumo financeiro',
                'Cadastrar despesa Salário João 2500 salario_mecanicos',
                'Tutorial financeiro',
            ],
        ];
    }

    private static function portal(): array
    {
        return [
            'tutorial' => "1. No perfil do Cliente, ative o acesso ao portal.\n"
                . "2. O cliente acessa /portal/consulta e digita o CPF.\n"
                . "3. Vê OS, status, mensagens de feedback e fotos do checklist.\n"
                . "4. Também existe login do portal (primeiro acesso) se configurado.",
            'duvidas' => [
                [
                    'pergunta' => 'CPF não encontrado no portal.',
                    'resposta' => 'Confirme se o cliente está cadastrado e se o CPF tem 11 dígitos corretos.',
                ],
                [
                    'pergunta' => 'Cliente não vê feedback.',
                    'resposta' => 'Na OS, use \"Enviar feedback ao cliente\". A mensagem precisa ser salva na seção de feedback.',
                ],
            ],
            'comandos_chat' => [
                'Tutorial portal',
                'Ajuda portal do cliente',
            ],
        ];
    }

    private static function atribuicoes(): array
    {
        return [
            'tutorial' => "1. Menu Atribuições.\n"
                . "2. Admin escolhe OS, serviço e mecânico responsável.\n"
                . "3. O mecânico atualiza o progresso da atribuição.\n"
                . "4. Use junto com o status da OS e o feedback ao cliente.",
            'duvidas' => [
                [
                    'pergunta' => 'Não há mecânicos na lista.',
                    'resposta' => 'Cadastre funcionários ativos em Funcionários (admin).',
                ],
            ],
            'comandos_chat' => [
                'Tutorial atribuições',
            ],
        ];
    }

    private static function funcionarios(): array
    {
        return [
            'tutorial' => "1. Menu Funcionários (admin).\n"
                . "2. Informe nome, e-mail, senha e confirmação de senha.\n"
                . "3. Marque Ativo se puder entrar no sistema.\n"
                . "4. Pode editar, desativar ou excluir depois.",
            'duvidas' => [
                [
                    'pergunta' => 'Erro ao criar funcionário (senha/ativo).',
                    'resposta' => 'Preencha senha e confirmação iguais. O campo ativo deve ser marcado corretamente no formulário.',
                ],
                [
                    'pergunta' => 'Funcionário não consegue logar.',
                    'resposta' => 'Verifique se está Ativo. Usuário inativo é bloqueado pelo middleware checkActive.',
                ],
            ],
            'comandos_chat' => [
                'Tutorial funcionários',
            ],
        ];
    }

    private static function dashboard(): array
    {
        return [
            'tutorial' => "1. Após o login você cai no Dashboard.\n"
                . "2. Veja cards: clientes, veículos, OS abertas, faturamento do mês.\n"
                . "3. Use Ações Rápidas para criar cliente, veículo, OS ou item do catálogo.",
            'duvidas' => [
                [
                    'pergunta' => 'Números parecem desatualizados.',
                    'resposta' => 'Atualize a página (F5). Os totais vêm direto do banco.',
                ],
            ],
            'comandos_chat' => [
                'Resumo da oficina',
                'Tutorial dashboard',
            ],
        ];
    }

    private static function copiloto(): array
    {
        return [
            'tutorial' => "1. Clique no ícone do robô (canto inferior direito).\n"
                . "2. Digite ou use o microfone.\n"
                . "3. Peça tutoriais, consultas ou cadastros.\n"
                . "4. Sem AI_API_KEY: modo local (comandos em português).\n"
                . "5. Com AI_API_KEY: conversa livre com Function Calling.",
            'duvidas' => [
                [
                    'pergunta' => 'O chat só entende frases certas.',
                    'resposta' => 'No modo local use os exemplos dos tutoriais. Com chave de IA (OpenAI/OpenRouter/Groq) a conversa fica mais natural.',
                ],
                [
                    'pergunta' => 'Cadastro pelo chat falhou.',
                    'resposta' => 'Leia a mensagem de erro (CPF duplicado, placa duplicada, falta de permissão admin, etc.) e corrija os dados.',
                ],
            ],
            'comandos_chat' => [
                'Lista de tutoriais',
                'Cadastrar cliente ...',
                'Cadastrar veiculo ...',
                'Cadastrar peça ...',
            ],
        ];
    }

    private static function pdf(): array
    {
        return [
            'tutorial' => "1. Abra a Ordem de Serviço.\n"
                . "2. Clique em Gerar PDF.\n"
                . "3. O arquivo OS_NUMERO.pdf será baixado com cliente, veículo, itens e total.",
            'duvidas' => [
                [
                    'pergunta' => 'Erro View [ordens.pdf] not found.',
                    'resposta' => 'A view resources/views/ordens/pdf.blade.php deve existir. Se faltou no seu ambiente, restaure esse arquivo e limpe views: php artisan view:clear',
                ],
            ],
            'comandos_chat' => [
                'Tutorial PDF',
                'Ajuda gerar PDF',
            ],
        ];
    }

    private static function duvidasGerais(): array
    {
        return [
            'tutorial' => "Problemas comuns e onde resolver:\n"
                . "• Login / usuário inativo → Funcionários (admin) ou senha.\n"
                . "• PHP 8.2 vs 8.4 → use C:\\php e serve.bat.\n"
                . "• Banco PostgreSQL → confira DB_* no .env.\n"
                . "• Fotos → php artisan storage:link.\n"
                . "• Rotas de edição → parâmetros servicoPeca / ordem já corrigidos no projeto.\n"
                . "• Financeiro zerado → marcar OS como Pagas.",
            'duvidas' => [
                [
                    'pergunta' => 'Composer diz que precisa PHP >= 8.4.',
                    'resposta' => 'No terminal: $env:Path = \"C:\\php;\" + $env:Path  depois php -v e php artisan serve.',
                ],
                [
                    'pergunta' => 'Página em branco / 500.',
                    'resposta' => 'Com APP_DEBUG=true veja a mensagem. Confira .env, migrations e logs em storage/logs.',
                ],
                [
                    'pergunta' => 'Como listar todos os tutoriais?',
                    'resposta' => 'Digite: lista de tutoriais',
                ],
            ],
            'comandos_chat' => [
                'Lista de tutoriais',
                'Dúvidas frequentes',
                'Tutorial geral',
            ],
        ];
    }
}
