{{--
    Conteúdo da tela de detalhes da OS no portal ($ordem com checklist.fotos carregados).
    Variáveis: $ordem, $cliente, $voltarHref, $voltarLabel (texto opcional do botão voltar).
--}}
@php
    $pontosChecklistLabels = App\Models\Checklist::pontosVeiculo();
@endphp

<div class="space-y-6" x-data="{
        lightboxOpen: false,
        lightboxSrc: '',
        lightboxAlt: '',
        openLightbox(src, alt = '') {
            this.lightboxSrc = src;
            this.lightboxAlt = alt || 'Foto do checklist';
            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLightbox() {
            this.lightboxOpen = false;
            this.lightboxSrc = '';
            this.lightboxAlt = '';
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @keydown.escape.window="if (lightboxOpen) closeLightbox()">

    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
         x-show="lightboxOpen"
         x-cloak
         x-transition.opacity
         @click.self="closeLightbox()"
         role="dialog"
         aria-modal="true"
         aria-label="Visualização ampliada da foto">
        <div class="relative max-w-[min(92vw,56rem)] max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden border border-orange-100">
            <button type="button"
                    @click="closeLightbox()"
                    class="absolute top-3 right-3 z-10 w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900 transition-colors"
                    aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
            <img :src="lightboxSrc"
                 :alt="lightboxAlt"
                 class="max-h-[85vh] w-auto max-w-full mx-auto object-contain block bg-gray-950">
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">OS: {{ $ordem->numero_ordem }}</h1>
                <p class="text-gray-600 mt-1">{{ optional($cliente)->nome ?? 'Cliente' }}</p>
            </div>
            <a href="{{ $voltarHref }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>{{ $voltarLabel ?? 'Voltar' }}
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Status Atual</h3>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-semibold
                    {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' :
                       ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' :
                       ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                    @if($ordem->status === 'aberta')
                        <i class="fas fa-folder-open mr-2"></i> Aberta
                    @elseif($ordem->status === 'em_andamento')
                        <i class="fas fa-cog fa-spin mr-2"></i> Em Andamento
                    @elseif($ordem->status === 'concluida')
                        <i class="fas fa-check-circle mr-2"></i> Concluída
                    @else
                        <i class="fas fa-times-circle mr-2"></i> Cancelada
                    @endif
                </span>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Valor Total</p>
                <p class="text-2xl font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    @if($ordem->veiculo)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-car text-orange-primary mr-2"></i>Informações do Veículo
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Veículo</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Placa</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->placa_formatada }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Ano</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->ano }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">KM Entrada</label>
                    <p class="text-gray-900 font-semibold">{{ number_format($ordem->km_entrada ?? 0) }}</p>
                </div>
            </div>
        </div>
    @endif

    @php
        $checklistItens = $ordem->relationLoaded('checklist') ? $ordem->checklist : $ordem->checklist()->with('fotos')->get();
        $totalFotosChecklist = $checklistItens->sum(fn ($item) => $item->fotos->count());
    @endphp
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-primary">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-primary">
                        <i class="fas fa-camera"></i>
                    </span>
                    Fotos do Checklist
                </h3>
                <p class="text-sm text-gray-600 mt-2 max-w-2xl">
                    Imagens da vistoria registradas pela oficina. Quando houver fotos, toque ou clique para ampliar.
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800 self-start">
                {{ $totalFotosChecklist }} {{ $totalFotosChecklist === 1 ? 'foto' : 'fotos' }}
            </span>
        </div>

        @if($totalFotosChecklist > 0)
            <div class="space-y-8">
                @foreach($checklistItens->sortBy('ponto_veiculo') as $item)
                    @if($item->fotos->isNotEmpty())
                        <div class="rounded-xl bg-gradient-to-br from-orange-50/80 to-white border border-orange-100 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-4">
                                <div>
                                    <p class="text-sm font-medium text-orange-primary uppercase tracking-wide">Ponto inspecionado</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ $pontosChecklistLabels[$item->ponto_veiculo] ?? ucfirst(str_replace('_', ' ', $item->ponto_veiculo)) }}
                                    </p>
                                    @if($item->observacao)
                                        <p class="text-sm text-gray-600 mt-1">{{ $item->observacao }}</p>
                                    @endif
                                    @php
                                        $condLabel = App\Models\Checklist::condicoes()[$item->condicao] ?? ucfirst(str_replace('_', ' ', $item->condicao));
                                    @endphp
                                    <p class="text-xs text-gray-500 mt-2">
                                        <span class="font-semibold text-gray-700">Condição:</span> {{ $condLabel }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                                @foreach($item->fotos as $foto)
                                    @php
                                        $captionFoto = $foto->descricao
                                            ? trim($foto->descricao)
                                            : (($pontosChecklistLabels[$item->ponto_veiculo] ?? 'Checklist') . ' • OS ' . $ordem->numero_ordem);
                                        $urlFoto = $foto->url_foto;
                                    @endphp
                                    @if($urlFoto !== '')
                                        <button type="button"
                                                class="group relative rounded-xl overflow-hidden border-2 border-white shadow-md focus:outline-none focus:ring-2 focus:ring-orange-primary aspect-[4/3] bg-gray-100"
                                                @click="openLightbox({{ json_encode($urlFoto) }}, {{ json_encode($captionFoto) }})">
                                            <img src="{{ $urlFoto }}"
                                                 alt="{{ $pontosChecklistLabels[$item->ponto_veiculo] ?? 'Checklist' }}"
                                                 loading="lazy"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                                 onerror="this.classList.add('opacity-40'); this.alt='Foto indisponível';">
                                            <span class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-3 pointer-events-none">
                                                <i class="fas fa-search-plus text-white text-xl drop-shadow"></i>
                                            </span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @elseif($checklistItens->count() > 0)
            <div class="rounded-xl border border-dashed border-orange-200 bg-orange-50/50 p-8 text-center">
                <i class="fas fa-images text-orange-200 text-4xl mb-3"></i>
                <p class="text-gray-700 font-medium">Checklist registrado, sem fotos anexadas ainda</p>
                <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                    A oficina já preencheu pontos da vistoria nesta OS, mas não há imagens. Quando forem enviadas no sistema interno (menu Checklist), elas aparecerão aqui.
                </p>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center">
                <i class="fas fa-clipboard-check text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-700 font-medium">Nenhum checklist nesta ordem ainda</p>
                <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                    As fotos da vistoria aparecem aqui depois que a oficina registrar o checklist e anexar imagens pelo sistema da empresa.
                </p>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-history text-orange-primary mr-2"></i>Andamento do Serviço
        </h3>

        @if($ordem->statusAtualizacoes->count() > 0)
            <div class="space-y-6">
                @foreach($ordem->statusAtualizacoes as $atualizacao)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-info text-white"></i>
                        </div>
                        <div class="ml-4 flex-1 pb-6">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-base font-semibold text-gray-900">
                                    @if($atualizacao->status_anterior != $atualizacao->status_novo)
                                        <i class="fas fa-sync-alt text-orange-primary mr-2"></i>Status alterado:
                                        <span class="text-orange-primary">
                                            {{ ucfirst(str_replace('_', ' ', $atualizacao->status_anterior)) }} →
                                            {{ ucfirst(str_replace('_', ' ', $atualizacao->status_novo)) }}</span>
                                    @else
                                        <i class="fas fa-comment-dots text-purple-500 mr-2"></i>Atualização do Serviço
                                    @endif
                                </p>
                                <span class="text-sm text-gray-500">{{ $atualizacao->data_atualizacao->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($atualizacao->mensagem)
                                <div class="bg-orange-50 border-l-4 border-orange-primary p-4 rounded-r-lg mb-2">
                                    <p class="text-gray-800">{{ $atualizacao->mensagem }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-500">Ainda não há atualizações para esta ordem.</p>
            </div>
        @endif
    </div>

    @if($ordem->itens->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-cogs text-purple-500 mr-2"></i>Serviços e Peças
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordem->itens as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->servicoPeca->descricao ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->quantidade }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">TOTAL:</td>
                            <td class="px-4 py-3 text-lg font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    @if($ordem->observacoes)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>Observações
            </h3>
            <p class="text-gray-700 bg-yellow-50 p-4 rounded-lg">{{ $ordem->observacoes }}</p>
        </div>
    @endif
</div>
