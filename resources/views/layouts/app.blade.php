<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Oficina')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuração personalizada do Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'orange-primary': '#FF6B35',
                        'orange-secondary': '#FF8C42',
                        'black-primary': '#1A1A1A',
                        'gray-dark': '#2D2D2D'
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer></script>
    
    <!-- Estilos personalizados -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Background pattern sutil */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-attachment: fixed;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }
        
        .sidebar-gradient {
            background: linear-gradient(180deg, #1A1A1A 0%, #2D2D2D 50%, #1A1A1A 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Cards modernos */
        .card-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Nav items com animação */
        .nav-item {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(180deg, #FF6B35 0%, #FF8C42 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .nav-item:hover::before,
        .nav-item.active::before {
            transform: scaleY(1);
        }
        
        /* Header gradient melhorado */
        .header-gradient {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 50%, #FF6B35 100%);
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Animações suaves */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #FF6B35 0%, #FF8C42 100%);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #FF8C42 0%, #FF6B35 100%);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <div class="sidebar-gradient text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform lg:relative lg:translate-x-0 transition-all duration-300 ease-in-out z-50"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- Logo -->
            <div class="flex items-center space-x-3 px-4 mb-8">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-wrench text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Sistema Oficina</h1>
                    <p class="text-xs text-gray-400">Gestão Completa</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1 px-2">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- Clientes -->
                <a href="{{ route('clientes.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span class="font-medium">Clientes</span>
                </a>

                <!-- Veículos -->
                <a href="{{ route('veiculos.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('veiculos.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-car w-5 text-center"></i>
                    <span class="font-medium">Veículos</span>
                </a>

                <!-- Ordens de Serviço -->
                <a href="{{ route('ordens.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('ordens.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-clipboard-list w-5 text-center"></i>
                    <span class="font-medium">Ordens de Serviço</span>
                </a>

                <!-- Serviços e Peças -->
                <a href="{{ route('servicos-pecas.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('servicos-pecas.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-cogs w-5 text-center"></i>
                    <span class="font-medium">Serviços e Peças</span>
                </a>

                <!-- Checklist -->
                <a href="{{ route('checklist.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('checklist.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-search w-5 text-center"></i>
                    <span class="font-medium">Checklist</span>
                </a>

                <!-- Atribuições de Serviço -->
                <a href="{{ route('atribuicoes.index') }}" 
                   class="nav-item flex items-center space-x-3 py-3 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('atribuicoes.*') ? 'bg-gradient-to-r from-orange-primary/20 to-orange-secondary/20 active' : 'hover:bg-white/5' }}">
                    <i class="fas fa-tasks w-5 text-center"></i>
                    <span class="font-medium">Atribuições</span>
                </a>

                @if(auth()->user() && auth()->user()->isAdmin())
                    <!-- Funcionários (somente admin) -->
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
                        <!-- User Info -->
                        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2 hover:bg-white/20 transition-all duration-300">
                            <img class="h-10 w-10 rounded-full ring-2 ring-white/30" src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&background=FF6B35&color=fff&bold=true" alt="User">
                            <div class="hidden md:block">
                                <p class="text-white text-sm font-semibold">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-white/80 text-xs">{{ auth()->user()->email ?? 'admin@oficina.com' }}</p>
                            </div>
                        </div>
                        
                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
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
</body>
</html>