<!DOCTYPE html>
<html lang="pt-BR" class="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark() ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($brand ?? \App\Models\ConfiguracaoSistema::atual())->nome_empresa . ' — Portal do Cliente')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.brand-theme')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
@php $cfg = $brand ?? \App\Models\ConfiguracaoSistema::atual(); @endphp
<body class="theme-app min-h-screen font-sans antialiased {{ $cfg->isDark() ? 'theme-dark' : '' }}">
    <header class="bg-white/90 backdrop-blur border-b-4 shadow-sm" style="border-color: var(--brand-primary)">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('portal.home') }}" class="flex items-center space-x-3">
                @if($cfg->logoUrl())
                    <img src="{{ $cfg->logoUrl() }}" alt="{{ $cfg->nome_empresa }}" class="h-12 object-contain">
                @else
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg text-white"
                         style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $cfg->nome_empresa }}</h1>
                    <p class="text-xs text-gray-500">Portal do Cliente</p>
                </div>
            </a>
            <div class="flex items-center gap-3 text-sm">
                @if(session('cliente_id'))
                    <a href="{{ route('portal.dashboard') }}" class="text-gray-600 hover:opacity-80" style="color: var(--brand-primary)">Minha conta</a>
                    <a href="{{ route('portal.agendar') }}" class="text-gray-600 hover:opacity-80">Agendar</a>
                    <a href="{{ route('portal.orcamento') }}" class="text-gray-600 hover:opacity-80">Orçamentos</a>
                    <form method="POST" action="{{ route('portal.logout') }}">@csrf
                        <button class="text-gray-500 hover:text-rose-600">Sair</button>
                    </form>
                @else
                    <a href="{{ route('portal.home') }}" class="text-gray-600 hover:opacity-80">
                        <i class="fas fa-home mr-1"></i> Início
                    </a>
                    <a href="{{ route('portal.agendar') }}" class="font-semibold hover:opacity-80" style="color: var(--brand-primary)">
                        <i class="fas fa-calendar-check mr-1"></i> Agendar
                    </a>
                    <a href="{{ route('portal.home', ['aba' => 'conta', 'entrar' => 1]) }}" class="text-gray-600 hover:opacity-80">Entrar</a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-lg flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-12 py-6 border-t border-gray-200 bg-white/80">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-500">
            <p>{{ $cfg->nome_empresa }} — Acompanhe o andamento do seu veículo.</p>
        </div>
    </footer>
</body>
</html>
