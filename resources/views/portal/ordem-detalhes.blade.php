@extends('layouts.portal-cliente')

@section('title', 'Detalhes da OS ' . $ordem->numero_ordem)

@section('content')
@include('portal.partials.ordem-detalhes-conteudo', [
    'ordem' => $ordem,
    'cliente' => $ordem->cliente,
    'voltarHref' => route('portal.dashboard'),
    'voltarLabel' => 'Voltar ao portal',
])
@endsection
