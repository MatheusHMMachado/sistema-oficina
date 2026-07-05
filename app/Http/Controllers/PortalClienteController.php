<?php
// ARQUIVO: app/Http/Controllers/PortalClienteController.php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\StatusAtualizacao;

class PortalClienteController extends Controller
{
    /**
     * Mostra a tela de consulta pública por CPF
     */
    public function consultaPublica(Request $request)
    {
        // Se já veio com CPF na query string, processar automaticamente
        if ($request->has('cpf')) {
            $cliente = Cliente::buscarPorCpf($request->cpf);

            if ($cliente) {
                $ordens = OrdemServico::where('cliente_id', $cliente->id)
                    ->with(['veiculo', 'statusAtualizacoes.usuario'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                return view('portal.status-veiculo', compact('cliente', 'ordens'));
            } else {
                return view('portal.consulta-publica')->with('error', 'CPF não encontrado em nosso sistema.');
            }
        }
        
        return view('portal.consulta-publica');
    }

    /**
     * Processa a consulta pública por CPF
     */
    public function processarConsulta(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string'
        ]);

        $cliente = Cliente::buscarPorCpf($request->cpf);

        if (!$cliente) {
            return back()->with('error', 'CPF não encontrado em nosso sistema.');
        }

        // Buscar ordens de serviço do cliente
        $ordens = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'statusAtualizacoes.usuario'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.status-veiculo', compact('cliente', 'ordens'));
    }

    /**
     * Mostra a tela de login do portal
     */
    public function showLogin()
    {
        return view('portal.login');
    }

    /**
     * Processa o login do cliente
     */
    public function login(Request $request)
    {
        $tipo = $request->input('login_type');
        
        if ($tipo == 'cpf') {
            $cliente = Cliente::buscarPorCpf($request->cpf);
        } else {
            $cliente = Cliente::where('email', $request->email)->first();
        }

        if (!$cliente || !$cliente->acesso_portal) {
            return back()->with('error', 'Acesso negado. Solicite seu acesso na oficina.');
        }

        if (!Hash::check($request->senha, $cliente->senha_portal)) {
            return back()->with('error', 'Senha incorreta.');
        }

        // Atualizar último acesso
        $cliente->update(['ultimo_acesso' => now()]);

        // Criar sessão do cliente
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

        // Se o cliente não tem e-mail cadastrado, aceita o informado e grava no cadastro
        if ($emailCadastrado === '') {
            $cliente->update([
                'email' => $request->email,
                'senha_portal' => Hash::make($request->senha),
                'acesso_portal' => true,
                'token_acesso' => Str::random(32)
            ]);
            return redirect()->route('portal.login')
                ->with('success', 'Senha criada com sucesso! Faça login para continuar.');
        }

        // Se tem e-mail cadastrado, exige que confira (comparação sem diferenciar maiúsculas)
        if (strtolower($emailCadastrado) !== $emailInformado) {
            return back()->with('error', 'E-mail não confere com o cadastrado na oficina. Verifique ou peça à oficina para atualizar seu e-mail.')
                ->withInput($request->only('cpf', 'email'));
        }

        $cliente->update([
            'senha_portal' => Hash::make($request->senha),
            'acesso_portal' => true,
            'token_acesso' => Str::random(32)
        ]);

        return redirect()->route('portal.login')
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
            ->with(['itens.servicoPeca'])
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
            return redirect()->route('portal.consulta-publica')
                           ->with('error', 'Acesso inválido.');
        }

        $cliente = Cliente::buscarPorCpf($cpf);

        if (!$cliente) {
            return redirect()->route('portal.consulta-publica')
                           ->with('error', 'Cliente não encontrado.');
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
            ->with(['veiculo', 'cliente', 'itens.servicoPeca', 'checklist.fotos', 'statusAtualizacoes.usuario'])
            ->findOrFail($ordemId);

        $ordem->loadMissing(['checklist.fotos']);

        return view('portal.ordem-detalhes', compact('ordem'));
    }

    /**
     * Agendar serviço
     */
    public function agendarServico()
    {
        $cliente = $this->getClienteLogado();
        $veiculos = $cliente->veiculos;
        
        return view('portal.agendar', compact('cliente', 'veiculos'));
    }

    /**
     * Salvar agendamento
     */
    public function salvarAgendamento(Request $request)
    {
        $request->validate([
            'veiculo_id' => 'required',
            'data' => 'required|date|after:today',
            'hora' => 'required',
            'servico' => 'required',
            'observacoes' => 'nullable'
        ]);

        // Aqui você criaria um registro de agendamento
        // Por enquanto, vamos simular

        return redirect()->route('portal.dashboard')
            ->with('success', 'Agendamento realizado com sucesso! Aguarde confirmação.');
    }

    /**
     * Configurar lembretes
     */
    public function lembretes()
    {
        return redirect()->route('portal.dashboard')
            ->with('error', 'Módulo de lembretes ainda não está disponível nesta versão.');
    }

    /**
     * Salvar lembretes (placeholder até módulo completo)
     */
    public function salvarLembretes(Request $request)
    {
        return redirect()->route('portal.dashboard')
            ->with('error', 'Configuração de lembretes ainda não está disponível.');
    }

    /**
     * Solicitar orçamento (placeholder até módulo completo)
     */
    public function orcamento()
    {
        return redirect()->route('portal.dashboard')
            ->with('error', 'Módulo de orçamento ainda não está disponível nesta versão.');
    }

    /**
     * Enviar solicitação de orçamento (placeholder até módulo completo)
     */
    public function enviarOrcamento(Request $request)
    {
        return redirect()->route('portal.dashboard')
            ->with('error', 'Solicitação de orçamento ainda não está disponível.');
    }

    /**
     * Histórico completo das ordens do cliente logado
     */
    public function historicoCompleto()
    {
        $cliente = $this->getClienteLogado();

        $ordens = OrdemServico::where('cliente_id', $cliente->id)
            ->with(['veiculo', 'statusAtualizacoes.usuario'])
            ->orderByDesc('created_at')
            ->get();

        return view('portal.status-veiculo', compact('cliente', 'ordens'));
    }

    /**
     * Acesso via QR token (abre consulta pública já filtrada pelo CPF)
     */
    public function acessoQR($token)
    {
        $cliente = Cliente::where('token_acesso', $token)->first();

        if (!$cliente) {
            return redirect()->route('portal.consulta-publica')
                ->with('error', 'Token de acesso inválido ou expirado.');
        }

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
        
        return redirect()->route('portal.login');
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