<?php
// ARQUIVO: app/Http/Controllers/PortalClienteController.php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use App\Models\Agendamento;
use App\Models\AvaliacaoNps;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\StatusAtualizacao;

class PortalClienteController extends Controller
{
    /**
     * Entrada única do Portal do Cliente (consulta CPF + login).
     */
    public function home(Request $request)
    {
        if (Session::get('cliente_id')) {
            return redirect()->route('portal.dashboard');
        }

        // Atalho da oficina: ?cpf= já lista as OS (mesmo conteúdo de fotos/orçamento)
        if ($request->filled('cpf')) {
            return $this->consultaPublica($request);
        }

        return view('portal.home');
    }

    /**
     * Mostra a tela de consulta pública por CPF
     */
    public function consultaPublica(Request $request)
    {
        // Se já veio com CPF na query string, processar automaticamente
        if ($request->has('cpf')) {
            return $this->resolverConsultaPublica(
                (string) $request->cpf,
                $request->input('placa'),
                (bool) Session::get('portal_qr_cliente_id')
            );
        }

        return redirect()->route('portal.home', ['aba' => 'consulta']);
    }

    /**
     * Processa a consulta pública por CPF + placa (2º fator).
     * QR válido na sessão dispensa a placa.
     */
    public function processarConsulta(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string',
            'placa' => 'nullable|string|max:10',
        ]);

        return $this->resolverConsultaPublica(
            (string) $request->cpf,
            $request->input('placa'),
            (bool) Session::get('portal_qr_cliente_id')
        );
    }

    private function resolverConsultaPublica(string $cpf, ?string $placa, bool $viaQr = false)
    {
        $cliente = Cliente::buscarPorCpf($cpf);

        if (! $cliente) {
            return redirect()->route('portal.home', ['aba' => 'consulta'])
                ->with('error', 'CPF não encontrado em nosso sistema.')
                ->withInput(['cpf' => $cpf, 'placa' => $placa]);
        }

        $qrOk = $viaQr && (int) Session::get('portal_qr_cliente_id') === (int) $cliente->id;

        if (! $qrOk) {
            $placaDigits = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $placa));
            if (strlen($placaDigits) < 7) {
                return redirect()->route('portal.home', ['aba' => 'consulta'])
                    ->with('error', 'Informe também a placa de um veículo cadastrado para consultar a O.S.')
                    ->withInput(['cpf' => $cpf, 'placa' => $placa]);
            }

            $placaOk = $cliente->veiculos()
                ->get()
                ->contains(function ($v) use ($placaDigits) {
                    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $v->placa)) === $placaDigits;
                });

            if (! $placaOk) {
                return redirect()->route('portal.home', ['aba' => 'consulta'])
                    ->with('error', 'CPF e placa não conferem. Verifique os dados ou entre na Minha Conta.')
                    ->withInput(['cpf' => $cpf, 'placa' => $placa]);
            }
        }

        Session::put('portal_consulta_cliente_id', $cliente->id);
        Session::put('portal_consulta_at', now()->timestamp);

        $ordens = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'statusAtualizacoes.usuario', 'itens.servicoPeca', 'checklist.fotos'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.status-veiculo', compact('cliente', 'ordens'));
    }

    /**
     * Mostra a tela de login do portal (unificada na home)
     */
    public function showLogin()
    {
        if (Session::get('cliente_id')) {
            return redirect()->route('portal.dashboard');
        }

        return redirect()->route('portal.home', ['aba' => 'conta', 'entrar' => 1]);
    }

    /**
     * Processa o login do cliente
     */
    public function login(Request $request)
    {
        $tipo = $request->input('login_type', 'cpf');

        $request->validate([
            'senha' => 'required|string',
            'cpf' => $tipo === 'cpf' ? 'required|string' : 'nullable',
            'email' => $tipo === 'email' ? 'required|email' : 'nullable',
        ], [
            'senha.required' => 'Informe a senha de acesso.',
            'cpf.required' => 'Informe o CPF.',
            'email.required' => 'Informe o e-mail.',
        ]);

        if ($tipo === 'cpf') {
            $cliente = Cliente::buscarPorCpf($request->cpf);
        } else {
            $cliente = Cliente::whereRaw('LOWER(email) = ?', [strtolower(trim($request->email))])->first();
        }

        if (! $cliente) {
            return redirect()->route('portal.home', ['aba' => 'conta', 'entrar' => 1])
                ->with('error', 'Cliente não encontrado. Confira o CPF/e-mail ou peça o cadastro na oficina.')
                ->withInput($request->except('senha'));
        }

        if (! $cliente->acesso_portal || ! $cliente->senha_portal) {
            return redirect()->route('portal.primeiro-acesso')
                ->with('error', 'Ainda não há senha de acesso. Crie sua senha no primeiro acesso.')
                ->withInput($request->only('cpf', 'email'));
        }

        if (! Hash::check($request->senha, $cliente->senha_portal)) {
            return redirect()->route('portal.home', ['aba' => 'conta', 'entrar' => 1])
                ->with('error', 'Senha incorreta.')
                ->withInput($request->except('senha'));
        }

        $cliente->update(['ultimo_acesso' => now()]);

        Session::put('cliente_id', $cliente->id);
        Session::put('cliente_nome', $cliente->nome);

        return redirect()->route('portal.dashboard');
    }

    /**
     * Dashboard do cliente
     */
    public function dashboard()
    {
        $cliente = $this->getClienteLogado();
        
        $ordensEmAndamento = OrdemServico::where('cliente_id', $cliente->id)
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->count();
            
        $ordensConcluidas = OrdemServico::where('cliente_id', $cliente->id)
            ->where('status', 'concluida')
            ->count();
            
        $ordensRecentes = OrdemServico::where('cliente_id', $cliente->id)
            ->with('veiculo')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $proximaRevisao = $this->calcularProximaRevisao($cliente);

        return view('portal.dashboard', compact(
            'cliente', 
            'ordensEmAndamento', 
            'ordensConcluidas', 
            'ordensRecentes',
            'proximaRevisao'
        ));
    }

    /**
     * Primeiro acesso - criar senha
     */
    public function primeiroAcesso()
    {
        return view('portal.primeiro-acesso');
    }

    /**
     * Salvar senha do primeiro acesso
     */
    public function salvarPrimeiroAcesso(Request $request)
    {
        $request->validate([
            'cpf' => 'required',
            'email' => 'required|email',
            'senha' => 'required|min:6|confirmed'
        ], [
            'senha.required' => 'Informe a senha.',
            'senha.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'senha.confirmed' => 'A confirmação da senha não confere. Digite a mesma senha nos dois campos.',
        ]);

        $cliente = Cliente::buscarPorCpf($request->cpf);

        if (!$cliente) {
            return back()->with('error', 'CPF não encontrado. Verifique com a oficina se seu cadastro está ativo.')
                ->withInput($request->only('cpf', 'email'));
        }

        $emailCadastrado = trim((string) ($cliente->email ?? ''));
        $emailInformado = strtolower(trim($request->email));

        $emailEmUsoPorOutro = Cliente::query()
            ->whereRaw('LOWER(email) = ?', [$emailInformado])
            ->where('id', '!=', $cliente->id)
            ->exists();

        // Se o cliente não tem e-mail cadastrado, aceita o informado (se não estiver em uso)
        if ($emailCadastrado === '') {
            if ($emailEmUsoPorOutro) {
                return back()
                    ->with('error', 'Este e-mail já está cadastrado em outro cliente. Use outro e-mail ou peça à oficina para corrigir o cadastro.')
                    ->withInput($request->only('cpf', 'email'));
            }

            // Já possui senha: não permite redefinir por este formulário (evita takeover)
            if (! empty($cliente->senha_portal) && $cliente->acesso_portal) {
                return back()
                    ->with('error', 'Este CPF já possui senha no portal. Faça login ou solicite à oficina a redefinição de acesso.')
                    ->withInput($request->only('cpf', 'email'));
            }

            $cliente->update([
                'email' => $request->email,
                'senha_portal' => Hash::make($request->senha),
                'acesso_portal' => true,
                'token_acesso' => $cliente->token_acesso ?: Str::random(32),
            ]);

            return redirect()->route('portal.home', ['aba' => 'conta', 'entrar' => 1])
                ->with('success', 'Senha criada com sucesso! Faça login para continuar.');
        }

        // Se tem e-mail cadastrado, exige que confira (comparação sem diferenciar maiúsculas)
        if (strtolower($emailCadastrado) !== $emailInformado) {
            return back()->with('error', 'E-mail não confere com o cadastrado na oficina. Verifique ou peça à oficina para atualizar seu e-mail.')
                ->withInput($request->only('cpf', 'email'));
        }

        // Já possui senha: não permite redefinir por este formulário (evita takeover)
        if (! empty($cliente->senha_portal) && $cliente->acesso_portal) {
            return back()
                ->with('error', 'Este CPF já possui senha no portal. Faça login ou solicite à oficina a redefinição de acesso.')
                ->withInput($request->only('cpf', 'email'));
        }

        $cliente->update([
            'senha_portal' => Hash::make($request->senha),
            'acesso_portal' => true,
            'token_acesso' => $cliente->token_acesso ?: Str::random(32),
        ]);

        return redirect()->route('portal.home', ['aba' => 'conta', 'entrar' => 1])
            ->with('success', 'Senha criada com sucesso! Faça login para continuar.');
    }

    /**
     * Visualizar histórico do veículo
     */
    public function historicoVeiculo($veiculoId)
    {
        $cliente = $this->getClienteLogado();
        
        $veiculo = $cliente->veiculos()->findOrFail($veiculoId);
        
        $historico = OrdemServico::where('veiculo_id', $veiculo->id)
            ->with(['itens.servicoPeca', 'avaliacaoNps'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal.historico-veiculo', compact('veiculo', 'historico'));
    }

    /**
     * Ver detalhes de uma OS (público por CPF)
     */
    public function verOrdemPublica($ordemId, Request $request)
    {
        $cpf = $request->query('cpf');
        
        if (!$cpf) {
            return redirect()->route('portal.home')
                           ->with('error', 'Acesso inválido.');
        }

        $cliente = Cliente::buscarPorCpf($cpf);

        if (!$cliente) {
            return redirect()->route('portal.home')
                           ->with('error', 'Cliente não encontrado.');
        }

        $consultaOk = (int) Session::get('portal_consulta_cliente_id') === (int) $cliente->id
            && (now()->timestamp - (int) Session::get('portal_consulta_at', 0)) < 3600;
        $qrOk = (int) Session::get('portal_qr_cliente_id') === (int) $cliente->id;

        if (! $consultaOk && ! $qrOk) {
            return redirect()->route('portal.home', ['aba' => 'consulta'])
                ->with('error', 'Para ver os detalhes, consulte novamente com CPF e placa.');
        }

        $ordem = OrdemServico::where('cliente_id', $cliente->id)
            ->where('id', $ordemId)
            ->with(['veiculo', 'itens.servicoPeca', 'checklist.fotos', 'statusAtualizacoes.usuario'])
            ->firstOrFail();

        $ordem->loadMissing(['checklist.fotos']);

        return view('portal.ordem-detalhes-publica', compact('ordem', 'cliente'));
    }

    /**
     * Ver detalhes de uma OS
     */
    public function verOrdem($ordemId)
    {
        $cliente = $this->getClienteLogado();
        
        $ordem = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'cliente', 'itens.servicoPeca', 'checklist.fotos', 'statusAtualizacoes.usuario', 'avaliacaoNps'])
            ->findOrFail($ordemId);

        $ordem->loadMissing(['checklist.fotos']);

        return view('portal.ordem-detalhes', compact('ordem'));
    }

    /**
     * Formulário de agendamento (cliente logado OU visitante sem cadastro).
     */
    public function agendarServico()
    {
        $cliente = null;
        $veiculos = collect();

        if (Session::get('cliente_id')) {
            $cliente = $this->getClienteLogado();
            $veiculos = $cliente->veiculos;
        }

        $servicos = ServicoPeca::where('tipo', 'servico')->where('ativo', true)->orderBy('descricao')->get();

        return view('portal.agendar', compact('cliente', 'veiculos', 'servicos'));
    }

    /**
     * Salvar agendamento — logado ou visitante (pré-cadastro / solicitação pública).
     */
    public function salvarAgendamento(Request $request)
    {
        $logado = (bool) Session::get('cliente_id');

        if ($logado) {
            $cliente = $this->getClienteLogado();

            $request->validate([
                'veiculo_id' => 'required|exists:veiculos,id',
                'data' => 'required|date|after:today',
                'hora' => 'required|string',
                'servico' => 'required|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
            ]);

            $veiculo = $cliente->veiculos()->findOrFail($request->veiculo_id);

            $agendamento = Agendamento::create([
                'cliente_id' => $cliente->id,
                'veiculo_id' => $veiculo->id,
                'eh_visitante' => false,
                'nome_contato' => $cliente->nome,
                'cpf_contato' => preg_replace('/[^0-9]/', '', (string) $cliente->cpf),
                'telefone_contato' => $cliente->celular ?: $cliente->telefone,
                'email_contato' => $cliente->email,
                'servico' => $request->servico,
                'data_agendada' => $request->data,
                'hora' => $request->hora,
                'observacoes' => $request->observacoes,
                'status' => 'pendente',
            ]);

            $nomeNotificacao = $cliente->nome;
            $redirect = redirect()->route('portal.dashboard');
        } else {
            $request->validate([
                'nome_contato' => 'required|string|max:255',
                'cpf_contato' => 'required|string|min:11|max:14',
                'telefone_contato' => 'required|string|max:20',
                'email_contato' => 'nullable|email|max:255',
                'placa_veiculo' => 'required|string|min:7|max:8',
                'marca_veiculo' => 'required|string|max:100',
                'modelo_veiculo' => 'required|string|max:100',
                'ano_veiculo' => 'required|integer|min:1950|max:' . (date('Y') + 1),
                'cor_veiculo' => 'required|string|max:50',
                'km_veiculo' => 'nullable|integer|min:0',
                'chassi_veiculo' => 'nullable|string|max:30',
                'data' => 'required|date|after:today',
                'hora' => 'required|string',
                'servico' => 'required|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
            ], [
                'cpf_contato.required' => 'Informe o CPF.',
                'placa_veiculo.required' => 'Informe a placa do veículo.',
                'marca_veiculo.required' => 'Informe a marca do veículo.',
                'modelo_veiculo.required' => 'Informe o modelo do veículo.',
            ]);

            $cpfDigits = preg_replace('/[^0-9]/', '', $request->cpf_contato);
            if (strlen($cpfDigits) !== 11) {
                return back()->withInput()->with('error', 'Informe um CPF válido com 11 dígitos.');
            }

            $placaDigits = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->placa_veiculo));
            if (strlen($placaDigits) < 7) {
                return back()->withInput()->with('error', 'Informe uma placa válida.');
            }

            $veiculoInfo = trim(sprintf(
                '%s — %s %s %s %s',
                $placaDigits,
                $request->marca_veiculo,
                $request->modelo_veiculo,
                $request->ano_veiculo,
                $request->cor_veiculo
            ));

            // Se já for cliente, vincula automaticamente
            $clienteExistente = Cliente::buscarPorCpf($cpfDigits);

            $agendamento = Agendamento::create([
                'cliente_id' => $clienteExistente?->id,
                'veiculo_id' => null,
                'eh_visitante' => ! $clienteExistente,
                'nome_contato' => $request->nome_contato,
                'cpf_contato' => $cpfDigits,
                'telefone_contato' => $request->telefone_contato,
                'email_contato' => $request->email_contato,
                'veiculo_info' => $veiculoInfo,
                'placa_veiculo' => $placaDigits,
                'marca_veiculo' => $request->marca_veiculo,
                'modelo_veiculo' => $request->modelo_veiculo,
                'ano_veiculo' => $request->ano_veiculo,
                'cor_veiculo' => $request->cor_veiculo,
                'km_veiculo' => $request->km_veiculo,
                'chassi_veiculo' => $request->chassi_veiculo,
                'servico' => $request->servico,
                'data_agendada' => $request->data,
                'hora' => $request->hora,
                'observacoes' => $request->observacoes,
                'status' => 'pendente',
            ]);

            $nomeNotificacao = $request->nome_contato . ($clienteExistente ? ' (cliente)' : ' (novo contato)');
            $redirect = redirect()->route('portal.home');
        }

        NotificacaoService::notificarAdmins(
            'agendamento',
            'Nova solicitação de agendamento',
            "{$nomeNotificacao} solicitou {$request->servico} em {$agendamento->data_agendada->format('d/m/Y')} às {$request->hora}.",
            route('agendamentos.index'),
            $agendamento
        );

        $msg = $logado
            ? 'Agendamento enviado! Aguarde a confirmação da oficina.'
            : 'Solicitação enviada! A oficina entrará em contato para confirmar. Se o CPF já estiver cadastrado, o agendamento foi vinculado ao seu cadastro.';

        return $redirect->with('success', $msg);
    }

    public function salvarAvaliacao(Request $request, $ordemId)
    {
        $cliente = $this->getClienteLogado();

        $ordem = OrdemServico::where('cliente_id', $cliente->id)
            ->where('status', 'concluida')
            ->findOrFail($ordemId);

        if ($ordem->avaliacaoNps) {
            return back()->with('error', 'Esta ordem já foi avaliada.');
        }

        $request->validate([
            'nota' => 'required|integer|min:0|max:10',
            'comentario' => 'nullable|string|max:1000',
        ]);

        AvaliacaoNps::create([
            'cliente_id' => $cliente->id,
            'ordem_servico_id' => $ordem->id,
            'nota' => $request->nota,
            'comentario' => $request->comentario,
        ]);

        return back()->with('success', 'Obrigado pela sua avaliação!');
    }

    /**
     * Orçamentos = ordens com valores, peças e fotos
     */
    public function orcamento()
    {
        $cliente = $this->getClienteLogado();

        $ordens = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'itens.servicoPeca', 'checklist.fotos'])
            ->orderByDesc('created_at')
            ->get();

        return view('portal.orcamentos', compact('ordens', 'cliente'));
    }

    /**
     * Solicitar orçamento: notifica a oficina (mantém a listagem atual).
     */
    public function enviarOrcamento(Request $request)
    {
        $cliente = $this->getClienteLogado();

        $request->validate([
            'mensagem' => 'nullable|string|max:1000',
            'servico' => 'nullable|string|max:255',
        ]);

        $detalhe = collect([
            $request->servico ? 'Serviço: ' . $request->servico : null,
            $request->mensagem ? 'Mensagem: ' . $request->mensagem : null,
        ])->filter()->implode(' | ') ?: 'Solicitação de orçamento pelo portal.';

        NotificacaoService::notificarAdmins(
            'orcamento',
            'Solicitação de orçamento (portal)',
            "{$cliente->nome}: {$detalhe}",
            route('ordens.index'),
            $cliente
        );

        return redirect()->route('portal.orcamento')
            ->with('success', 'Solicitação enviada à oficina. Em breve entraremos em contato.');
    }

    /**
     * Lembretes simples: aponta próxima revisão / agendar
     */
    public function lembretes()
    {
        $cliente = $this->getClienteLogado();
        $proximaRevisao = $this->calcularProximaRevisao($cliente);

        return redirect()->route('portal.agendar')
            ->with('success', $proximaRevisao
                ? "Sugestão: agende sua próxima revisão ({$proximaRevisao})."
                : 'Agende um serviço ou revisão quando quiser.');
    }

    /**
     * Preferências de lembrete: por enquanto direciona ao agendamento (sem fingir gravação).
     */
    public function salvarLembretes(Request $request)
    {
        return redirect()->route('portal.agendar')
            ->with('success', 'Para receber lembretes, agende o próximo serviço. A oficina confirmará o horário.');
    }

    /**
     * Histórico completo das ordens do cliente logado
     */
    public function historicoCompleto()
    {
        $cliente = $this->getClienteLogado();

        $ordens = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'statusAtualizacoes.usuario', 'itens.servicoPeca', 'checklist.fotos'])
            ->orderByDesc('created_at')
            ->get();

        return view('portal.status-veiculo', compact('cliente', 'ordens'));
    }

    /**
     * Acesso via QR token (abre o portal único já filtrado pelo CPF)
     */
    public function acessoQR($token)
    {
        $cliente = Cliente::where('token_acesso', $token)->first();

        if (! $cliente) {
            return redirect()->route('portal.home')
                ->with('error', 'Token de acesso inválido ou expirado.');
        }

        Session::put('portal_qr_cliente_id', $cliente->id);
        Session::put('portal_consulta_cliente_id', $cliente->id);
        Session::put('portal_consulta_at', now()->timestamp);

        return redirect()->route('portal.consulta-publica', [
            'cpf' => preg_replace('/[^0-9]/', '', (string) $cliente->cpf),
        ]);
    }

    /**
     * Logout do portal
     */
    public function logout()
    {
        Session::forget('cliente_id');
        Session::forget('cliente_nome');

        return redirect()->route('portal.home')
            ->with('success', 'Você saiu do portal.');
    }

    /**
     * Helper para pegar cliente logado
     */
    private function getClienteLogado()
    {
        $clienteId = Session::get('cliente_id');
        
        if (!$clienteId) {
            abort(403, 'Acesso negado');
        }
        
        return Cliente::with('veiculos')->findOrFail($clienteId);
    }

    /**
     * Calcular próxima revisão
     */
    private function calcularProximaRevisao($cliente)
    {
        // Lógica simplificada - você pode melhorar
        $ultimaOS = OrdemServico::where('cliente_id', $cliente->id)
            ->where('status', 'concluida')
            ->orderBy('data_saida', 'desc')
            ->first();
            
        if ($ultimaOS && $ultimaOS->data_saida) {
            return $ultimaOS->data_saida->addMonths(6)->format('d/m/Y');
        }
        
        return null;
    }
}