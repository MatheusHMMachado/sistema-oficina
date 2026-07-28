@extends('layouts.app')

@section('title', 'Identidade Visual')
@section('page-title', 'Configurações de Marca')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card-modern rounded-2xl shadow-xl p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Whitelabel & Temas</h1>
                <p class="text-sm text-gray-600">As alterações valem para admin, mecânico, login e portal do cliente.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('configuracoes.tema.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da oficina</label>
                <input type="text" name="nome_empresa" value="{{ old('nome_empresa', $config->nome_empresa) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor Primária</label>
                    <div class="flex gap-2">
                        <input type="color" value="{{ old('cor_primaria', $config->cor_primaria) }}"
                               oninput="document.getElementById('cor_primaria').value = this.value"
                               class="w-12 h-10 rounded border cursor-pointer">
                        <input type="text" id="cor_primaria" name="cor_primaria" pattern="^#[0-9A-Fa-f]{6}$"
                               value="{{ old('cor_primaria', $config->cor_primaria) }}" required
                               class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Botões, menu e cabeçalhos</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor Secundária</label>
                    <div class="flex gap-2">
                        <input type="color" value="{{ old('cor_secundaria', $config->cor_secundaria) }}"
                               oninput="document.getElementById('cor_secundaria').value = this.value"
                               class="w-12 h-10 rounded border cursor-pointer">
                        <input type="text" id="cor_secundaria" name="cor_secundaria" pattern="^#[0-9A-Fa-f]{6}$"
                               value="{{ old('cor_secundaria', $config->cor_secundaria) }}" required
                               class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Hover, tags e ícones secundários</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tema do sistema</label>
                <div class="flex gap-3">
                    <label class="flex-1 border rounded-xl p-4 cursor-pointer {{ $config->tema === 'light' ? 'ring-2 ring-orange-primary' : '' }}">
                        <input type="radio" name="tema" value="light" class="mr-2" {{ old('tema', $config->tema) === 'light' ? 'checked' : '' }}>
                        <span class="font-medium">Modo Claro</span>
                    </label>
                    <label class="flex-1 border rounded-xl p-4 cursor-pointer {{ $config->tema === 'dark' ? 'ring-2 ring-orange-primary' : '' }}">
                        <input type="radio" name="tema" value="dark" class="mr-2" {{ old('tema', $config->tema) === 'dark' ? 'checked' : '' }}>
                        <span class="font-medium">Modo Escuro</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logomarca</label>
                @if($config->logoUrl())
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ $config->logoUrl() }}" alt="Logo" class="h-16 object-contain bg-white rounded border p-2">
                        <label class="text-sm text-rose-600 flex items-center gap-2">
                            <input type="checkbox" name="remover_logo" value="1"> Remover logo atual
                        </label>
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm border rounded-lg px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">PNG/JPG/WebP até 2MB. Exibida no login e no portal.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 rounded-lg text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 btn-primary text-white rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>Salvar identidade
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
