@extends('layouts.portal-cliente')

@section('title', 'Detalhes da OS ' . $ordem->numero_ordem)

@section('content')
@include('portal.partials.ordem-detalhes-conteudo', [
    'ordem' => $ordem,
    'cliente' => $cliente,
    'voltarHref' => route('portal.home'),
    'voltarLabel' => 'Voltar',
])
@endsection
