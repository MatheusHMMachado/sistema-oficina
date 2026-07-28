@extends('layouts.app')

@section('title', 'Notificações')
@section('page-title', 'Notificações')

@section('content')
<div class="space-y-4 max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Central de Alertas</h1>
        <form method="POST" action="{{ route('notificacoes.marcar-todas') }}">
            @csrf
            <button class="text-sm text-orange-primary hover:underline">Marcar todas como lidas</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow divide-y">
        @forelse($notificacoes as $n)
            <div class="p-4 flex gap-3 {{ $n->lida ? 'opacity-60' : 'bg-orange-50/40' }}">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                    {{ $n->tipo === 'prazo_excedido' || $n->tipo === 'estoque_baixo' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700' }}">
                    <i class="fas {{ $n->tipo === 'prazo_excedido' ? 'fa-bell' : ($n->tipo === 'estoque_baixo' ? 'fa-boxes' : 'fa-info') }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">{{ $n->titulo }}</p>
                    <p class="text-sm text-gray-600">{{ $n->mensagem }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                @if(!$n->lida)
                    <form method="POST" action="{{ route('notificacoes.marcar-lida', $n->id) }}">
                        @csrf
                        <button class="text-xs px-3 py-1 rounded bg-orange-primary text-white">Abrir</button>
                    </form>
                @elseif($n->link)
                    <a href="{{ $n->link }}" class="text-xs px-3 py-1 rounded bg-gray-200 text-gray-700 self-start">Ver</a>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">Nenhuma notificação no momento.</div>
        @endforelse
    </div>

    {{ $notificacoes->links() }}
</div>
@endsection
