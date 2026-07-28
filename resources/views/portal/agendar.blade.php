@extends('layouts.portal-cliente')

@section('title', 'Agendar Serviço')

@section('content')
@php
    $logado = !empty($cliente);
    $veiculoPre = request('veiculo');
    $abaErro = 'contato';
    if (! $logado) {
        if ($errors->hasAny(['placa_veiculo', 'marca_veiculo', 'modelo_veiculo', 'ano_veiculo', 'cor_veiculo', 'km_veiculo', 'chassi_veiculo'])) {
            $abaErro = 'veiculo';
        } elseif ($errors->hasAny(['servico', 'data', 'hora', 'observacoes'])) {
            $abaErro = 'servico';
        }
    }
@endphp

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Agendamento Online</h1>
        <p class="text-gray-600 mb-6">
            @if($logado)
                Escolha o veículo, o serviço e o horário. A oficina receberá sua solicitação para aprovação.
            @else
                Não precisa ter cadastro. Preencha seus dados e os do veículo nas abas abaixo.
                Se você já for cliente, use o mesmo CPF do cadastro.
            @endif
        </p>

        @if(!$logado)
            <div class="mb-6 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sm text-sky-900">
                <i class="fas fa-info-circle mr-1"></i>
                Já tem conta no portal?
                <a href="{{ route('portal.home', ['aba' => 'conta', 'entrar' => 1]) }}" class="font-semibold underline">Entre aqui</a>
                para agendar com seus veículos cadastrados.
            </div>
        @endif

        <form method="POST" action="{{ route('portal.agendar') }}" class="space-y-4"
              @if(!$logado)
                  x-data="{ aba: '{{ $abaErro }}' }"
                  novalidate
              @endif>
            @csrf

            @if($logado)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Veículo *</label>
                    <select name="veiculo_id" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">Selecione...</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}" {{ old('veiculo_id', $veiculoPre) == $v->id ? 'selected' : '' }}>
                                {{ $v->placa_formatada }} — {{ $v->marca }} {{ $v->modelo }} {{ $v->ano }}
                            </option>
                        @endforeach
                    </select>
                    @error('veiculo_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serviço desejado *</label>
                    <select name="servico" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">Selecione...</option>
                        @foreach($servicos as $s)
                            <option value="{{ $s->descricao }}" {{ old('servico') == $s->descricao ? 'selected' : '' }}>{{ $s->descricao }}</option>
                        @endforeach
                        <option value="Outro / Diagnóstico" {{ old('servico') == 'Outro / Diagnóstico' ? 'selected' : '' }}>Outro / Diagnóstico</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                        <input type="date" name="data" value="{{ old('data') }}" min="{{ now()->addDay()->format('Y-m-d') }}" required
                               class="w-full border rounded-lg px-3 py-2">
                        @error('data')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horário *</label>
                        <select name="hora" required class="w-full border rounded-lg px-3 py-2">
                            @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00'] as $h)
                                <option value="{{ $h }}" {{ old('hora') == $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Ex.: barulho no freio, luz do motor acesa...">{{ old('observacoes') }}</textarea>
                </div>
            @else
                {{-- Abas visitante --}}
                <div class="flex border-b mb-4">
                    <button type="button" @click="aba = 'contato'"
                            class="flex-1 py-2.5 text-sm font-semibold border-b-2 transition-colors"
                            :class="aba === 'contato' ? 'border-current' : 'border-transparent text-gray-500'"
                            :style="aba === 'contato' ? 'color: var(--brand-primary); border-color: var(--brand-primary)' : ''">
                        1. Contato
                    </button>
                    <button type="button" @click="aba = 'veiculo'"
                            class="flex-1 py-2.5 text-sm font-semibold border-b-2 transition-colors"
                            :class="aba === 'veiculo' ? 'border-current' : 'border-transparent text-gray-500'"
                            :style="aba === 'veiculo' ? 'color: var(--brand-primary); border-color: var(--brand-primary)' : ''">
                        2. Veículo
                    </button>
                    <button type="button" @click="aba = 'servico'"
                            class="flex-1 py-2.5 text-sm font-semibold border-b-2 transition-colors"
                            :class="aba === 'servico' ? 'border-current' : 'border-transparent text-gray-500'"
                            :style="aba === 'servico' ? 'color: var(--brand-primary); border-color: var(--brand-primary)' : ''">
                        3. Serviço
                    </button>
                </div>

                {{-- Aba 1: Contato --}}
                <div x-show="aba === 'contato'" x-cloak class="space-y-4">
                    <p class="text-sm text-gray-600">Seus dados para a oficina entrar em contato.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                        <input type="text" name="nome_contato" value="{{ old('nome_contato') }}" required
                               class="w-full border rounded-lg px-3 py-2">
                        @error('nome_contato')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                            <input type="text" name="cpf_contato" value="{{ old('cpf_contato') }}" required
                                   placeholder="000.000.000-00" class="w-full border rounded-lg px-3 py-2">
                            @error('cpf_contato')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp *</label>
                            <input type="text" name="telefone_contato" value="{{ old('telefone_contato') }}" required
                                   placeholder="(00) 00000-0000" class="w-full border rounded-lg px-3 py-2">
                            @error('telefone_contato')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" name="email_contato" value="{{ old('email_contato') }}"
                               class="w-full border rounded-lg px-3 py-2">
                        @error('email_contato')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="button" @click="aba = 'veiculo'" class="px-5 py-2.5 btn-primary text-white rounded-xl font-semibold text-sm">
                            Próximo: Veículo <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- Aba 2: Veículo --}}
                <div x-show="aba === 'veiculo'" x-cloak class="space-y-4">
                    <p class="text-sm text-gray-600">Informações do veículo que será atendido.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placa *</label>
                            <input type="text" name="placa_veiculo" value="{{ old('placa_veiculo') }}" required
                                   placeholder="ABC1D23" maxlength="8" class="w-full border rounded-lg px-3 py-2 uppercase">
                            @error('placa_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ano *</label>
                            <input type="number" name="ano_veiculo" value="{{ old('ano_veiculo') }}" required
                                   min="1950" max="{{ now()->year + 1 }}" placeholder="{{ now()->year }}"
                                   class="w-full border rounded-lg px-3 py-2">
                            @error('ano_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
                            <input type="text" name="marca_veiculo" value="{{ old('marca_veiculo') }}" required
                                   placeholder="Fiat, VW, Chevrolet..." class="w-full border rounded-lg px-3 py-2">
                            @error('marca_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
                            <input type="text" name="modelo_veiculo" value="{{ old('modelo_veiculo') }}" required
                                   placeholder="Argo, Gol, Onix..." class="w-full border rounded-lg px-3 py-2">
                            @error('modelo_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
                            <input type="text" name="cor_veiculo" value="{{ old('cor_veiculo') }}" required
                                   placeholder="Prata, Branco..." class="w-full border rounded-lg px-3 py-2">
                            @error('cor_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quilometragem (km)</label>
                            <input type="number" name="km_veiculo" value="{{ old('km_veiculo') }}" min="0"
                                   placeholder="Ex.: 45000" class="w-full border rounded-lg px-3 py-2">
                            @error('km_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chassi</label>
                            <input type="text" name="chassi_veiculo" value="{{ old('chassi_veiculo') }}"
                                   maxlength="30" placeholder="Opcional" class="w-full border rounded-lg px-3 py-2">
                            @error('chassi_veiculo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-between pt-2">
                        <button type="button" @click="aba = 'contato'" class="px-4 py-2 text-gray-600 hover:underline text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Contato
                        </button>
                        <button type="button" @click="aba = 'servico'" class="px-5 py-2.5 btn-primary text-white rounded-xl font-semibold text-sm">
                            Próximo: Serviço <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- Aba 3: Serviço --}}
                <div x-show="aba === 'servico'" x-cloak class="space-y-4">
                    <p class="text-sm text-gray-600">Escolha o serviço e o horário desejados.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serviço desejado *</label>
                        <select name="servico" required class="w-full border rounded-lg px-3 py-2">
                            <option value="">Selecione...</option>
                            @foreach($servicos as $s)
                                <option value="{{ $s->descricao }}" {{ old('servico') == $s->descricao ? 'selected' : '' }}>{{ $s->descricao }}</option>
                            @endforeach
                            <option value="Outro / Diagnóstico" {{ old('servico') == 'Outro / Diagnóstico' ? 'selected' : '' }}>Outro / Diagnóstico</option>
                        </select>
                        @error('servico')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                            <input type="date" name="data" value="{{ old('data') }}" min="{{ now()->addDay()->format('Y-m-d') }}" required
                                   class="w-full border rounded-lg px-3 py-2">
                            @error('data')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário *</label>
                            <select name="hora" required class="w-full border rounded-lg px-3 py-2">
                                @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00'] as $h)
                                    <option value="{{ $h }}" {{ old('hora') == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Ex.: barulho no freio, luz do motor acesa...">{{ old('observacoes') }}</textarea>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <button type="button" @click="aba = 'veiculo'" class="px-4 py-2 text-gray-600 hover:underline text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Veículo
                        </button>
                        <button type="submit" class="px-6 py-3 btn-primary text-white rounded-xl font-semibold">
                            Solicitar agendamento
                        </button>
                    </div>
                </div>
            @endif

            @if($logado)
                <div class="flex justify-between items-center pt-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-gray-600 hover:underline">Voltar</a>
                    <button type="submit" class="px-6 py-3 btn-primary text-white rounded-xl font-semibold">
                        Solicitar agendamento
                    </button>
                </div>
            @else
                <div class="pt-2" x-show="aba !== 'servico'" x-cloak>
                    <a href="{{ route('portal.home') }}" class="text-sm text-gray-600 hover:underline">Voltar ao portal</a>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
