@extends('layouts.app')

@section('title', 'Serviços e Peças - Sistema de Oficina')
@section('page-title', 'Serviços e Peças')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Serviços e Peças</h1>
            <p class="text-gray-600">Gerencie o catálogo de serviços e peças</p>
        </div>
        <a href="{{ route('servicos-pecas.create') }}" 
           class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
            <i class="fas fa-plus mr-2"></i>
            Novo Item
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('servicos-pecas.index') }}" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por descrição..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>
            </div>
            <div>
                <select name="tipo" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                    <option value="">Todos os tipos</option>
                    @foreach($tipos as $key => $label)
                        <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'tipo']))
                    <a href="{{ route('servicos-pecas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Itens -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($servicosPecas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($servicosPecas as $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-150
                                {{ $item->estoqueCritico() ? 'bg-rose-50' : ($item->estoqueBaixo() ? 'bg-amber-50' : '') }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 {{ $item->tipo == 'servico' ? 'bg-green-100' : 'bg-purple-100' }} rounded-full flex items-center justify-center">
                                            <i class="fas {{ $item->tipo == 'servico' ? 'fa-wrench text-green-600' : 'fa-cog text-purple-600' }}"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->descricao }}</div>
                                            @if($item->estoqueCritico())
                                                <div class="text-xs font-semibold text-rose-700">Estoque zerado — reposição urgente</div>
                                            @elseif($item->estoqueBaixo())
                                                <div class="text-xs font-semibold text-amber-700">Abaixo do mínimo — repor estoque</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->tipo == 'servico' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $item->tipo == 'servico' ? 'Serviço' : 'Peça' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->tipo === 'peca')
                                        <div class="text-sm font-semibold {{ $item->estoqueCritico() ? 'text-rose-700' : ($item->estoqueBaixo() ? 'text-amber-700' : 'text-gray-900') }}">
                                            {{ $item->quantidade_estoque }} un
                                        </div>
                                        <div class="text-xs text-gray-500">Mín: {{ $item->estoque_minimo }}</div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('servicos-pecas.edit', $item) }}" 
                                           class="text-orange-600 hover:text-orange-900 transition-colors" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" onclick="toggleStatus({{ $item->id }})" 
                                                class="text-{{ $item->ativo ? 'red' : 'green' }}-600 hover:text-{{ $item->ativo ? 'red' : 'green' }}-900 transition-colors" 
                                                title="{{ $item->ativo ? 'Desativar' : 'Ativar' }}">
                                            <i class="fas fa-{{ $item->ativo ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($servicosPecas->hasPages())
                <div class="bg-white px-6 py-3 border-t border-gray-200">
                    {{ $servicosPecas->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-gray-400 text-3xl"></i>
                </div>
                @if(request()->hasAny(['search', 'tipo']))
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum item encontrado</h3>
                    <p class="text-gray-500 mb-6">Não encontramos itens com os filtros aplicados.</p>
                    <a href="{{ route('servicos-pecas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>
                        Limpar Filtros
                    </a>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum item cadastrado</h3>
                    <p class="text-gray-500 mb-6">Comece cadastrando serviços e peças para sua oficina.</p>
                    <a href="{{ route('servicos-pecas.create') }}" 
                       class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Cadastrar Primeiro Item
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    function toggleStatus(id) {
        if (!confirm('Tem certeza que deseja alterar o status deste item?')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(url('/servicos-pecas')) + '/' + id + '/toggle-status';

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PATCH';

        form.appendChild(token);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection