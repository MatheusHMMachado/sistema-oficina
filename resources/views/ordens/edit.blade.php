@extends('layouts.app')

@section('title', 'Editar OS ' . $ordem->numero_ordem . ' - Sistema de Oficina')
@section('page-title', 'Editar Ordem de Serviço')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-primary rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-edit text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Editar OS {{ $ordem->numero_ordem }}</h1>
                    <p class="text-gray-600">
                        @if($ordem->cliente && $ordem->veiculo)
                            {{ $ordem->cliente->nome }} - {{ $ordem->veiculo->descricao_completa }}
                        @elseif($ordem->cliente)
                            {{ $ordem->cliente->nome }} - Veículo não encontrado
                        @elseif($ordem->veiculo)
                            Cliente não encontrado - {{ $ordem->veiculo->descricao_completa }}
                        @else
                            Dados incompletos
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('ordens.show', $ordem) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-eye mr-2"></i>
                    Visualizar
                </a>
                <a href="{{ route('ordens.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Feedback para o Cliente (o cliente vê em outro ambiente: Portal do Cliente) -->
        @if($ordem->cliente)
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl shadow-lg p-5 mb-6 border-2 border-orange-200">
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-comments text-orange-primary mr-2"></i>
                    Feedback para o Cliente
                </h4>
                <p class="text-sm text-gray-600 mb-4">
                    O cliente vê o andamento em <strong>outro ambiente</strong> (Portal do Cliente), consultando com o CPF. Aqui você só envia os feedbacks do serviço até a conclusão.
                </p>
                <a href="{{ url('/portal/consulta') }}?cpf={{ preg_replace('/[^0-9]/', '', $ordem->cliente->cpf) }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors text-sm mb-4">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Abrir Portal do Cliente
                </a>
            </div>
        @endif

        <!-- Status e Informações Básicas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Dar feedback: status + mensagem (cliente vê no portal) -->
            <div class="bg-gray-50 rounded-lg p-4 border-2 border-orange-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-paper-plane text-orange-primary mr-2"></i>
                    Dar feedback do serviço ao cliente
                </h3>
                <p class="text-xs text-gray-600 mb-3">Altere o status (se quiser) e escreva a mensagem que o cliente verá no Portal do Cliente.</p>
                <form method="POST" action="{{ route('ordens.update-status', $ordem) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                            <option value="aberta" {{ $ordem->status === 'aberta' ? 'selected' : '' }}>Aberta</option>
                            <option value="em_andamento" {{ $ordem->status === 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                            <option value="concluida" {{ $ordem->status === 'concluida' ? 'selected' : '' }}>Concluída</option>
                            <option value="cancelada" {{ $ordem->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem para o cliente *</label>
                        <textarea name="mensagem" 
                                  rows="2"
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                                  placeholder="Ex: Veículo em manutenção; previsão amanhã."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações internas (opcional)</label>
                        <textarea name="observacoes" 
                                  rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                                  placeholder="Só para a equipe."></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar feedback ao cliente
                    </button>
                </form>
            </div>

            <!-- Informações da Ordem -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Entrada:</span>
                        <span class="font-medium">{{ $ordem->data_entrada->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">KM:</span>
                        <span class="font-medium">{{ number_format($ordem->km_entrada ?? 0) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total:</span>
                        <span class="font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Itens:</span>
                        <span class="font-medium">{{ $ordem->itens->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicionar Itens -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-plus text-green-500 mr-2"></i>
            Adicionar Serviços e Peças
        </h3>

        <form method="POST" action="{{ route('ordens.adicionar-item', $ordem) }}" 
              class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4" 
              x-data="{ quantidade: 1 }">
            @csrf
            
            <div class="flex-1">
                <select name="servico_peca_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Selecione um serviço ou peça</option>
                    @foreach($servicosPecas->groupBy('tipo') as $tipo => $items)
                        <optgroup label="{{ $tipo === 'servico' ? 'Serviços' : 'Peças' }}">
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->descricao }} - R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="w-32">
                <input type="number" 
                       name="quantidade" 
                       min="1" 
                       value="1"
                       x-model="quantidade"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Qtd">
            </div>

            <button type="submit" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Adicionar
            </button>
        </form>
    </div>

    <!-- Lista de Itens -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-list text-purple-500 mr-2"></i>
                Itens da Ordem ({{ $ordem->itens->count() }})
            </h3>
            @if($ordem->itens->count() > 0)
                <div class="text-lg font-bold text-orange-primary">
                    Total: R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                </div>
            @endif
        </div>

        @if($ordem->itens->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Unit.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordem->itens as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 {{ $item->servicoPeca->tipo == 'servico' ? 'bg-green-100' : 'bg-purple-100' }} rounded-full flex items-center justify-center mr-3">
                                            <i class="fas {{ $item->servicoPeca->tipo == 'servico' ? 'fa-wrench text-green-600' : 'fa-cog text-purple-600' }} text-xs"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->servicoPeca->descricao }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->servicoPeca->tipo == 'servico' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $item->servicoPeca->tipo == 'servico' ? 'Serviço' : 'Peça' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->quantidade }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" action="{{ route('ordens.remover-item', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Tem certeza que deseja remover este item?')"
                                                class="text-red-600 hover:text-red-900 transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                TOTAL GERAL:
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-orange-primary">
                                R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum item adicionado</h3>
                <p class="text-gray-500">Use o formulário acima para adicionar serviços e peças à esta ordem.</p>
            </div>
        @endif
    </div>

    <!-- Observações -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
            Observações
        </h3>

        <form method="POST" action="{{ route('ordens.update', $ordem) }}">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="data_entrada" value="{{ $ordem->data_entrada->format('Y-m-d') }}">
            <input type="hidden" name="data_saida" value="{{ $ordem->data_saida ? $ordem->data_saida->format('Y-m-d') : '' }}">
            <input type="hidden" name="km_entrada" value="{{ $ordem->km_entrada }}">
            <input type="hidden" name="status" value="{{ $ordem->status }}">
            
            <div class="flex space-x-4">
                <textarea name="observacoes" 
                          rows="3"
                          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                          placeholder="Adicione observações sobre esta ordem de serviço...">{{ old('observacoes', $ordem->observacoes) }}</textarea>
                
                <button type="submit" 
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Salvar
                </button>
            </div>
        </form>
    </div>

    <!-- Enviar apenas mensagem ao cliente (sem mudar status) -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-primary">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-comment-dots text-orange-primary mr-2"></i>
            Enviar apenas mensagem ao cliente
        </h3>
        <p class="text-sm text-gray-600 mb-4">
            Enviar uma mensagem que o cliente verá no Portal do Cliente, sem alterar o status da ordem.
        </p>
        <form method="POST" action="{{ route('ordens.adicionar-atualizacao', $ordem) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem para o cliente *</label>
                <textarea name="mensagem" 
                          rows="3"
                          required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                          placeholder="Ex: Peças encomendadas, chegada prevista para amanhã..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações internas (opcional)</label>
                <textarea name="observacoes" 
                          rows="2"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                          placeholder="Só para a equipe."></textarea>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                <i class="fas fa-paper-plane mr-2"></i>
                Enviar mensagem
            </button>
        </form>
    </div>

    <!-- Ações Rápidas -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-bolt text-blue-500 mr-2"></i>
            Ações Rápidas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('checklist.show', $ordem) }}" 
               class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors">
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-search text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Checklist</span>
                </div>
            </a>

            <a href="{{ route('ordens.pdf', $ordem) }}" 
               class="flex items-center justify-center p-4 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-colors">
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-file-pdf text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Gerar PDF</span>
                </div>
            </a>

            <a href="{{ route('ordens.show', $ordem) }}" 
               class="flex items-center justify-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-colors">
                <div class="text-center">
                    <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-eye text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Visualizar</span>
                </div>
            </a>

            @if($ordem->cliente)
                <a href="{{ route('clientes.show', $ordem->cliente) }}" 
                   class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Ver Cliente</span>
                    </div>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection