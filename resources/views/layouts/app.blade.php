<!DOCTYPE html>
<html lang="pt-BR" class="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark() ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($brand ?? \App\Models\ConfiguracaoSistema::atual())->nome_empresa)</title>
    
    @include('partials.head-assets')
    
    @stack('styles')
    <style>
        .nav-item { position: relative; transition: all 0.2s ease; }
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; height: 100%; width: 4px;
            background: linear-gradient(180deg, var(--brand-primary), var(--brand-secondary));
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }
        .nav-item:hover::before, .nav-item.active::before { transform: scaleY(1); }
        .animate-fade-in-up { animation: fadeInUp 0.4s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="theme-app font-sans antialiased {{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark() ? 'theme-dark' : '' }}">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <div class="sidebar-gradient text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform lg:relative lg:translate-x-0 transition-all duration-300 ease-in-out z-50"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- Logo -->
            <div class="flex items-center space-x-3 px-4 mb-8">
                @php $cfg = $brand ?? \App\Models\ConfiguracaoSistema::atual(); @endphp
                @if($cfg->logoUrl())
                    <img src="{{ $cfg->logoUrl() }}" alt="{{ $cfg->nome_empresa }}" class="h-12 w-12 object-contain rounded-xl bg-white/10 p-1">
                @else
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))">
                        <i class="fas fa-wrench text-white text-xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-lg font-bold tracking-tight leading-tight">{{ $cfg->nome_empresa }}</h1>
                    <p class="text-xs text-gray-400">Gestão Completa</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1 px-2">
                @php $isAdmin = auth()->user() && auth()->user()->isAdmin(); @endphp

                <a href="{{ route('dashboard') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('clientes.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span class="font-medium">Clientes</span>
                </a>

                <a href="{{ route('veiculos.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('veiculos.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-car w-5 text-center"></i>
                    <span class="font-medium">Veículos</span>
                </a>

                <a href="{{ route('ordens.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('ordens.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-clipboard-list w-5 text-center"></i>
                    <span class="font-medium">Ordens de Serviço</span>
                </a>

                <a href="{{ route('servicos-pecas.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('servicos-pecas.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-cogs w-5 text-center"></i>
                    <span class="font-medium">Serviços e Peças</span>
                </a>

                <a href="{{ route('checklist.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('checklist.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-search w-5 text-center"></i>
                    <span class="font-medium">Checklist</span>
                </a>

                <a href="{{ route('atribuicoes.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('atribuicoes.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-tasks w-5 text-center"></i>
                    <span class="font-medium">{{ $isAdmin ? 'Atribuições' : 'Minhas Tarefas' }}</span>
                </a>

                <a href="{{ route('notificacoes.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('notificacoes.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-bell w-5 text-center"></i>
                    <span class="font-medium">Notificações</span>
                    @php
                        $notifCount = 0;
                        try {
                            $notifCount = \App\Models\Notificacao::where('user_id', auth()->id())->where('lida', false)->count();
                        } catch (\Throwable $e) {
                            $notifCount = 0;
                        }
                    @endphp
                    @if($notifCount > 0)
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-500 text-white">{{ $notifCount }}</span>
                    @endif
                </a>

                @if($isAdmin)
                    <a href="{{ route('agendamentos.index') }}"
                       class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('agendamentos.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                        <i class="fas fa-calendar-check w-5 text-center"></i>
                        <span class="font-medium">Agendamentos</span>
                    </a>

                    <a href="{{ route('financeiro.index') }}"
                       class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('financeiro.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                        <i class="fas fa-wallet w-5 text-center"></i>
                        <span class="font-medium">Controle Financeiro</span>
                    </a>

                    <a href="{{ route('relatorios.index') }}"
                       class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('relatorios.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span class="font-medium">Relatórios</span>
                    </a>

                    <a href="{{ route('funcionarios.index') }}" 
                       class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('funcionarios.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                        <i class="fas fa-user-shield w-5 text-center"></i>
                        <span class="font-medium">Funcionários</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black opacity-50 lg:hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="header-gradient shadow-xl">
                <div class="flex justify-between items-center py-4 px-6">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-white focus:outline-none lg:hidden mr-4 hover:bg-white/10 rounded-lg p-2 transition-colors">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-white text-xl font-bold tracking-tight">@yield('page-title', 'Dashboard')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @if($isAdmin)
                            <a href="{{ route('configuracoes.tema') }}"
                               class="text-white hover:bg-white/10 rounded-lg p-2 transition-colors"
                               title="Configurações de marca / tema">
                                <i class="fas fa-cog text-lg"></i>
                            </a>
                        @endif

                        <!-- User Info -->
                        @php
                            $avatarBg = ltrim(($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35', '#');
                        @endphp
                        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2 hover:bg-white/20 transition-all duration-300">
                            <img class="h-10 w-10 rounded-full ring-2 ring-white/30"
                                 src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background={{ $avatarBg }}&color=fff&bold=true"
                                 alt="User">
                            <div class="hidden md:block">
                                <p class="text-white text-sm font-semibold">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-white/80 text-xs">{{ auth()->user()->email ?? 'admin@oficina.com' }}</p>
                            </div>
                        </div>
                        
                        <!-- Logout (visível também no mobile) -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-white hover:bg-white/10 rounded-lg p-2 transition-colors" title="Sair">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="container mx-auto px-6 py-8 animate-fade-in-up">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-800 rounded-xl shadow-lg" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                    <span class="font-medium">{{ session('success') }}</span>
                                </div>
                                <button @click="show = false" class="text-green-700 hover:text-green-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-800 rounded-xl shadow-lg" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                                    <span class="font-medium">{{ session('error') }}</span>
                                </div>
                                <button @click="show = false" class="text-red-700 hover:text-red-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-800 rounded-xl shadow-lg">
                            <div class="flex items-center space-x-3 mb-2">
                                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                                <strong class="font-semibold">Ops! Algo deu errado:</strong>
                            </div>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('partials.copiloto-widget')

    <!-- JavaScript personalizado -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[x-data*="show: true"]');
            alerts.forEach(alert => {
                if (alert.__x) {
                    alert.__x.$data.show = false;
                }
            });
        }, 5000);

        // Função para formatar CPF
        function maskCPF(value) {
            return value
                .replace(/\D/g, '')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})/, '$1-$2')
                .replace(/(-\d{2})\d+?$/, '$1');
        }

        // Função para formatar telefone
        function maskPhone(value) {
            const cleanValue = value.replace(/\D/g, '');
            if (cleanValue.length <= 10) {
                return cleanValue
                    .replace(/(\d{2})(\d)/, '($1) $2')
                    .replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                return cleanValue
                    .replace(/(\d{2})(\d)/, '($1) $2')
                    .replace(/(\d{5})(\d)/, '$1-$2');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>