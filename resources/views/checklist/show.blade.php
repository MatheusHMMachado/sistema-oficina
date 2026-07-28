@extends('layouts.app')

@section('title', 'Checklist - OS ' . $ordem->numero_ordem)
@section('page-title', 'Checklist do Veículo')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header da Ordem -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-search text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Checklist do Veículo</h1>
                    <p class="text-gray-600">OS: {{ $ordem->numero_ordem }} | {{ $ordem->veiculo->descricao_completa }}</p>
                </div>
            </div>
            <a href="{{ route('checklist.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>

        <!-- Info da Ordem -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <p class="text-sm text-gray-900">{{ $ordem->cliente->nome }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Veículo</label>
                <p class="text-sm text-gray-900">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Placa</label>
                <p class="text-sm text-gray-900">{{ $ordem->veiculo->placa_formatada }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">KM</label>
                <p class="text-sm text-gray-900">{{ number_format($ordem->km_entrada ?? $ordem->veiculo->km) }}</p>
            </div>
        </div>
    </div>

    <!-- Diagrama Visual do Veículo -->
    <div class="bg-white rounded-xl shadow-lg p-6" x-data="checklistApp()">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-car text-purple-600 mr-2"></i>
            Inspeção Visual do Veículo
        </h3>

        <!-- Diagrama do Carro -->
        <div class="relative mx-auto" style="width: 600px; height: 400px;">
            <!-- SVG do carro -->
            <svg width="600" height="400" viewBox="0 0 600 400" class="border-2 border-gray-200 rounded-lg bg-gray-50">
                <!-- Corpo do carro -->
                <rect x="150" y="150" width="300" height="100" fill="#e5e5e5" stroke="#333" stroke-width="2" rx="10"/>
                
                <!-- Pontos clicáveis -->
                <!-- Frente Esquerda -->
                <circle cx="120" cy="170" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3" 
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('frente_esquerda', 'Frente Esquerda')" />
                <text x="120" y="175" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">FE</text>

                <!-- Frente Direita -->
                <circle cx="480" cy="170" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('frente_direita', 'Frente Direita')" />
                <text x="480" y="175" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">FD</text>

                <!-- Lateral Esquerda -->
                <circle cx="120" cy="200" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('lateral_esquerda', 'Lateral Esquerda')" />
                <text x="120" y="205" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">LE</text>

                <!-- Lateral Direita -->
                <circle cx="480" cy="200" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('lateral_direita', 'Lateral Direita')" />
                <text x="480" y="205" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">LD</text>

                <!-- Traseira Esquerda -->
                <circle cx="120" cy="230" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('traseira_esquerda', 'Traseira Esquerda')" />
                <text x="120" y="235" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">TE</text>

                <!-- Traseira Direita -->
                <circle cx="480" cy="230" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('traseira_direita', 'Traseira Direita')" />
                <text x="480" y="235" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">TD</text>

                <!-- Capô -->
                <circle cx="300" cy="120" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('capot', 'Capô')" />
                <text x="300" y="125" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">CP</text>

                <!-- Teto -->
                <circle cx="300" cy="200" r="25" fill="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35' }}" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-orange-secondary transition-colors"
                        @click="abrirModal('teto', 'Teto')" />
                <text x="300" y="205" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">TT</text>

                <!-- Para-brisa -->
                <circle cx="250" cy="150" r="20" fill="#4F46E5" stroke="#fff" stroke-width="2"
                        class="cursor-pointer hover:fill-indigo-600 transition-colors"
                        @click="abrirModal('para_brisa', 'Para-brisa')" />
                <text x="250" y="155" text-anchor="middle" fill="white" font-size="8" class="pointer-events-none">PB</text>

                <!-- Vidro Traseiro -->
                <circle cx="350" cy="150" r="20" fill="#4F46E5" stroke="#fff" stroke-width="2"
                        class="cursor-pointer hover:fill-indigo-600 transition-colors"
                        @click="abrirModal('vidro_traseiro', 'Vidro Traseiro')" />
                <text x="350" y="155" text-anchor="middle" fill="white" font-size="8" class="pointer-events-none">VT</text>

                <!-- Motor -->
                <circle cx="300" cy="80" r="30" fill="#10B981" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-emerald-600 transition-colors"
                        @click="abrirModal('motor', 'Motor')" />
                <text x="300" y="85" text-anchor="middle" fill="white" font-size="10" class="pointer-events-none">MOTOR</text>

                <!-- Interior -->
                <circle cx="300" cy="280" r="30" fill="#8B5CF6" stroke="#fff" stroke-width="3"
                        class="cursor-pointer hover:fill-violet-600 transition-colors"
                        @click="abrirModal('interior', 'Interior')" />
                <text x="300" y="285" text-anchor="middle" fill="white" font-size="9" class="pointer-events-none">INT.</text>
            </svg>

            <!-- Legenda -->
            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-orange-primary rounded-full mr-2"></div>
                    <span>Carroceria</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-indigo-500 rounded-full mr-2"></div>
                    <span>Vidros</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-emerald-500 rounded-full mr-2"></div>
                    <span>Motor</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-violet-500 rounded-full mr-2"></div>
                    <span>Interior</span>
                </div>
            </div>
        </div>

        <!-- Modal de Checklist -->
        <div x-show="modalAberto" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="modalAberto = false">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="modalAberto" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity" 
                     @click="modalAberto = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <!-- Modal -->
                <div x-show="modalAberto" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <form :action="`{{ route('checklist.store', $ordem) }}`" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="ponto_veiculo" :value="pontoAtual">
                        
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium text-gray-900" x-text="tituloAtual"></h3>
                                <button type="button" @click="modalAberto = false" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>

                            <!-- Condição -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Condição *</label>
                                <div class="grid grid-cols-5 gap-2">
                                    @foreach(['otimo' => 'Ótimo', 'bom' => 'Bom', 'regular' => 'Regular', 'ruim' => 'Ruim', 'pessimo' => 'Péssimo'] as $key => $label)
                                        <label class="relative flex items-center justify-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="condicao" value="{{ $key }}" required class="sr-only">
                                            <div class="text-center">
                                                <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center 
                                                    {{ $key === 'otimo' ? 'bg-green-500' : 
                                                       ($key === 'bom' ? 'bg-blue-500' : 
                                                       ($key === 'regular' ? 'bg-yellow-500' : 
                                                       ($key === 'ruim' ? 'bg-orange-500' : 'bg-red-500'))) }} text-white">
                                                    @if($key === 'otimo')
                                                        <i class="fas fa-star text-sm"></i>
                                                    @elseif($key === 'bom')
                                                        <i class="fas fa-thumbs-up text-sm"></i>
                                                    @elseif($key === 'regular')
                                                        <i class="fas fa-minus text-sm"></i>
                                                    @elseif($key === 'ruim')
                                                        <i class="fas fa-thumbs-down text-sm"></i>
                                                    @else
                                                        <i class="fas fa-times text-sm"></i>
                                                    @endif
                                                </div>
                                                <span class="text-xs font-medium">{{ $label }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="mb-6">
                                <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                                <textarea name="observacao" 
                                          id="observacao" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                          placeholder="Descreva detalhes sobre a condição deste ponto..."></textarea>
                            </div>

                            <!-- Upload de Fotos -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fotos</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-600 transition-colors">
                                    <input type="file" 
                                           name="fotos[]" 
                                           multiple 
                                           accept="image/*"
                                           class="hidden"
                                           id="upload-fotos"
                                           @change="handleFileSelect">
                                    <label for="upload-fotos" class="cursor-pointer">
                                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-camera text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-600 mb-2">Clique para adicionar fotos ou arraste aqui</p>
                                        <p class="text-sm text-gray-400">Máximo 5MB por foto, formatos: JPG, PNG</p>
                                    </label>
                                </div>
                                
                                <!-- Preview das fotos selecionadas -->
                                <div x-show="fotosPreview.length > 0" class="mt-4">
                                    <div class="grid grid-cols-3 gap-4">
                                        <template x-for="(foto, index) in fotosPreview" :key="index">
                                            <div class="relative">
                                                <img :src="foto.url" :alt="foto.name" class="w-full h-24 object-cover rounded-lg">
                                                <button type="button" 
                                                        @click="removerFoto(index)"
                                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                            <button type="button" 
                                    @click="modalAberto = false"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklists Já Realizados -->
    @if($ordem->checklist->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                Pontos Inspecionados ({{ $ordem->checklist->count() }})
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($ordem->checklist as $item)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">{{ $pontosVeiculo[$item->ponto_veiculo] }}</h4>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $item->condicao === 'otimo' ? 'bg-green-100 text-green-800' : 
                                   ($item->condicao === 'bom' ? 'bg-blue-100 text-blue-800' : 
                                   ($item->condicao === 'regular' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($item->condicao === 'ruim' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                                {{ $condicoes[$item->condicao] }}
                            </span>
                        </div>
                        
                        @if($item->observacao)
                            <p class="text-sm text-gray-600 mb-3">{{ $item->observacao }}</p>
                        @endif

                        @if($item->fotos->count() > 0)
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($item->fotos as $foto)
                                    <div class="relative">
                                        <img src="{{ $foto->url_foto }}" 
                                             alt="Foto {{ $pontosVeiculo[$item->ponto_veiculo] }}"
                                             class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-75 transition-opacity"
                                             onclick="abrirImagemModal('{{ $foto->url_foto }}')">
                                        <a href="{{ $foto->url_foto }}" 
                                           target="_blank"
                                           class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 hover:bg-opacity-30 transition-opacity rounded">
                                            <i class="fas fa-expand text-white opacity-0 hover:opacity-100"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                            <span>{{ $item->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    function checklistApp() {
        return {
            modalAberto: false,
            pontoAtual: '',
            tituloAtual: '',
            fotosPreview: [],

            abrirModal(ponto, titulo) {
                this.pontoAtual = ponto;
                this.tituloAtual = titulo;
                this.modalAberto = true;
                this.fotosPreview = [];
            },

            handleFileSelect(event) {
                const files = Array.from(event.target.files);
                this.fotosPreview = [];
                
                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.fotosPreview.push({
                                name: file.name,
                                url: e.target.result,
                                index: index
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },

            removerFoto(index) {
                this.fotosPreview.splice(index, 1);
                
                const fileInput = document.getElementById('upload-fotos');
                const dt = new DataTransfer();
                
                const files = Array.from(fileInput.files);
                files.forEach((file, i) => {
                    if (i !== index) {
                        dt.items.add(file);
                    }
                });
                
                fileInput.files = dt.files;
            }
        }
    }

    // Validação do formulário
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('change', function(e) {
            if (e.target.name === 'condicao') {
                document.querySelectorAll('input[name="condicao"]').forEach(radio => {
                    radio.closest('label').classList.remove('ring-2', 'ring-purple-600', 'border-purple-600');
                });
                
                e.target.closest('label').classList.add('ring-2', 'ring-purple-600', 'border-purple-600');
            }
        });
    });

    // Função para abrir imagem em modal
    function abrirImagemModal(url) {
        // Criar modal simples
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75';
        modal.innerHTML = `
            <div class="relative max-w-4xl max-h-full p-4">
                <button onclick="this.closest('.fixed').remove()" 
                        class="absolute -top-2 -right-2 w-10 h-10 bg-red-500 text-white rounded-full hover:bg-red-600 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
                <img src="${url}" alt="Foto ampliada" class="max-w-full max-h-screen rounded-lg">
            </div>
        `;
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
        document.body.appendChild(modal);
    }
</script>
@endsection