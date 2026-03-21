<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal do Cliente - Acompanhe seu Veículo')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'orange-primary': '#FF6B35',
                        'orange-secondary': '#FF8C42'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 50%, #fff5f0 100%); background-attachment: fixed; }
    </style>
</head>
<body class="min-h-screen font-sans antialiased">
    {{-- AMBIENTE EXCLUSIVO DO CLIENTE - Não é o sistema da oficina --}}
    <header class="bg-white border-b-4 border-orange-primary shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/portal/consulta') }}" class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-car text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Acompanhe seu Veículo</h1>
                    <p class="text-xs text-gray-500">Portal do Cliente</p>
                </div>
            </a>
            <a href="{{ url('/portal/consulta') }}" class="text-sm text-gray-600 hover:text-orange-primary transition-colors">
                <i class="fas fa-search mr-1"></i> Nova consulta
            </a>
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
            <p>Portal do Cliente — Consulte o andamento do seu veículo usando seu CPF.</p>
            <p class="mt-1">Este ambiente é exclusivo para clientes. Sistema da oficina em área restrita.</p>
        </div>
    </footer>
</body>
</html>
