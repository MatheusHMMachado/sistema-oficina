<!-- ARQUIVO: resources/views/portal/dashboard.blade.php -->
<!-- Esta é a tela principal do cliente após login -->

@extends('layouts.portal-cliente')

@section('title', 'Meu Painel - Portal do Cliente')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Boas-vindas -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Olá, {{ $cliente->nome }}!</h1>
        <p class="text-gray-600 mt-2">Bem-vindo ao seu portal. Aqui você pode acompanhar seus veículos e serviços.</p>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-car text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Meus Veículos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $cliente->veiculos->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wrench text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Serviços em Andamento</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $ordensEmAndamento }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Serviços Concluídos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $ordensConcluidas }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Próxima Revisão</p>
                    <p class="text-lg font-bold text-gray-900">{{ $proximaRevisao ?? 'Não agendada' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Veículos -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">
                <i class="fas fa-car mr-2 text-orange-primary"></i>
                Meus Veículos
            </h2>
            <button onclick="openQRCode()" class="text-orange-primary hover:text-orange-secondary">
                <i class="fas fa-qrcode mr-2"></i>Meu QR Code
            </button>
        </div>

        @if($cliente->veiculos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cliente->veiculos as $veiculo)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-3">
                            <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-car text-orange-600 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-bold text-gray-900">{{ $veiculo->marca }} {{ $veiculo->modelo }}</h3>
                                <p class="text-sm text-gray-600">{{ $veiculo->cor }} • {{ $veiculo->ano }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Placa:</span>
                                <span class="font-medium">{{ $veiculo->placa_formatada }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">KM Atual:</span>
                                <span class="font-medium">{{ number_format($veiculo->km) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Última OS:</span>
                                <span class="font-medium">
                                    @if($veiculo->ordensServico->first())
                                        {{ $veiculo->ordensServico->first()->created_at->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button onclick="verHistorico({{ $veiculo->id }})" 
                                    class="flex-1 py-2 bg-orange-primary text-white rounded-lg text-sm hover:bg-orange-secondary transition-colors">
                                <i class="fas fa-history mr-1"></i>Histórico
                            </button>
                            <button onclick="agendarServico({{ $veiculo->id }})" 
                                    class="flex-1 py-2 border border-orange-primary text-orange-primary rounded-lg text-sm hover:bg-orange-50 transition-colors">
                                <i class="fas fa-calendar-plus mr-1"></i>Agendar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-car text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">Nenhum veículo cadastrado</p>
            </div>
        @endif
    </div>

    <!-- Ordens de Serviço Recentes -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">
            <i class="fas fa-clipboard-list mr-2 text-orange-primary"></i>
            Serviços Recentes
        </h2>

        @if($ordensRecentes->count() > 0)
            <div class="space-y-4">
                @foreach($ordensRecentes as $ordem)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center mb-2">
                                    <span class="font-bold text-gray-900">OS #{{ $ordem->numero_ordem }}</span>
                                    <span class="ml-3 px-2 py-1 text-xs rounded-full 
                                        {{ $ordem->status == 'aberta' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $ordem->status == 'em_andamento' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $ordem->status == 'concluida' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-car mr-1"></i>
                                    {{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }} - {{ $ordem->veiculo->placa_formatada }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Entrada: {{ $ordem->data_entrada->format('d/m/Y') }}
                                    @if($ordem->data_saida)
                                        | Saída: {{ $ordem->data_saida->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-lg font-bold text-orange-primary">
                                    R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                                </p>
                                <button onclick="verDetalhesOS({{ $ordem->id }})" 
                                        class="mt-2 text-sm text-orange-primary hover:text-orange-secondary">
                                    <i class="fas fa-eye mr-1"></i>Ver Detalhes
                                </button>
                            </div>
                        </div>

                        <!-- Timeline do Serviço -->
                        @if($ordem->status == 'em_andamento')
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span class="text-green-600"><i class="fas fa-check-circle"></i> Recebido</span>
                                    <span class="text-blue-600"><i class="fas fa-wrench"></i> Em Execução</span>
                                    <span class="text-gray-400"><i class="far fa-circle"></i> Concluído</span>
                                    <span class="text-gray-400"><i class="far fa-circle"></i> Entregue</span>
                                </div>
                                <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600" style="width: 50%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('portal.historico') }}" class="text-orange-primary hover:text-orange-secondary font-medium">
                    <i class="fas fa-history mr-2"></i>Ver Histórico Completo
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">Nenhum serviço realizado</p>
            </div>
        @endif
    </div>

    <!-- Funcionalidades Extras -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Agendamento Online -->
        <div class="bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition-shadow cursor-pointer" onclick="abrirAgendamento()">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Agendar Serviço</h3>
            <p class="text-sm text-gray-600">Agende seu serviço online de forma rápida e fácil</p>
        </div>

        <!-- Orçamento Online -->
        <div class="bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition-shadow cursor-pointer" onclick="solicitarOrcamento()">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calculator text-blue-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Solicitar Orçamento</h3>
            <p class="text-sm text-gray-600">Receba um orçamento detalhado para seu veículo</p>
        </div>

        <!-- Lembretes -->
        <div class="bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition-shadow cursor-pointer" onclick="configurarLembretes()">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bell text-purple-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Lembretes</h3>
            <p class="text-sm text-gray-600">Configure lembretes de manutenção e revisão</p>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div id="modalQRCode" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-sm">
        <h3 class="text-lg font-bold mb-4 text-center">Seu QR Code de Acesso Rápido</h3>
        <div class="bg-gray-100 p-4 rounded-lg">
            @php
                $urlQR = $cliente->token_acesso ? url(route('portal.cliente.qr', $cliente->token_acesso)) : '';
            @endphp
            @if($urlQR)
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($urlQR) }}" alt="QR Code de acesso" class="w-48 h-48 mx-auto block">
            @else
                <div class="w-48 h-48 mx-auto bg-gray-300 flex items-center justify-center text-gray-500 text-sm text-center px-2">Token não disponível</div>
            @endif
        </div>
        <p class="text-sm text-gray-600 mt-4 text-center">Mostre este código na oficina para acesso rápido aos seus dados</p>
        <button onclick="closeModal('modalQRCode')" class="mt-4 w-full py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary">
            Fechar
        </button>
    </div>
</div>

<script>
    function verHistorico(veiculoId) {
        window.location.href = `/portal/veiculo/${veiculoId}/historico`;
    }

    function agendarServico(veiculoId) {
        window.location.href = `/portal/agendar?veiculo=${veiculoId}`;
    }

    function verDetalhesOS(ordemId) {
        window.location.href = `/portal/ordem/${ordemId}`;
    }

    function openQRCode() {
        document.getElementById('modalQRCode').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function abrirAgendamento() {
        window.location.href = '/portal/agendar';
    }

    function solicitarOrcamento() {
        window.location.href = '/portal/orcamento';
    }

    function configurarLembretes() {
        window.location.href = '/portal/lembretes';
    }
</script>
@endsection