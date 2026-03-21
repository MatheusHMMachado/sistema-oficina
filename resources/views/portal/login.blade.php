<!-- ARQUIVO: resources/views/portal/login.blade.php -->
<!-- Crie a pasta: resources/views/portal/ -->

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Cliente - Sistema Oficina</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuração de cores -->
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
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center items-center p-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 bg-orange-primary rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-car text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Portal do Cliente</h1>
            <p class="text-gray-600 mt-2">Acompanhe seus veículos e serviços</p>
        </div>

        <!-- Card de Login -->
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
            <!-- Abas -->
            <div class="flex mb-6 border-b border-gray-200">
                <button onclick="switchTab('cpf')" id="tab-cpf" class="flex-1 py-2 text-center font-medium text-orange-primary border-b-2 border-orange-primary">
                    <i class="fas fa-id-card mr-2"></i>CPF
                </button>
                <button onclick="switchTab('email')" id="tab-email" class="flex-1 py-2 text-center font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-envelope mr-2"></i>Email
                </button>
            </div>

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Formulário CPF -->
            <form id="form-cpf" method="POST" action="{{ route('portal.login') }}">
                @csrf
                <input type="hidden" name="login_type" value="cpf">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2 text-orange-primary"></i>CPF
                        </label>
                        <input type="text" 
                               name="cpf" 
                               required
                               maxlength="14"
                               onkeyup="maskCPF(this)"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                               placeholder="000.000.000-00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-orange-primary"></i>Senha de Acesso
                        </label>
                        <input type="password" 
                               name="senha" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                               placeholder="••••••••">
                    </div>

                    <button type="submit" class="w-full py-3 bg-orange-primary text-white rounded-lg font-medium hover:bg-orange-secondary transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Acessar Portal
                    </button>
                </div>
            </form>

            <!-- Formulário Email (Escondido por padrão) -->
            <form id="form-email" method="POST" action="{{ route('portal.login') }}" class="hidden">
                @csrf
                <input type="hidden" name="login_type" value="email">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-orange-primary"></i>Email
                        </label>
                        <input type="email" 
                               name="email" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                               placeholder="seu@email.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-orange-primary"></i>Senha de Acesso
                        </label>
                        <input type="password" 
                               name="senha" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                               placeholder="••••••••">
                    </div>

                    <button type="submit" class="w-full py-3 bg-orange-primary text-white rounded-lg font-medium hover:bg-orange-secondary transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Acessar Portal
                    </button>
                </div>
            </form>

            <!-- Primeira vez? -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600 mb-3">Primeira vez acessando?</p>
                <a href="{{ route('portal.primeiro-acesso') }}" class="text-orange-primary hover:text-orange-secondary font-medium">
                    <i class="fas fa-user-plus mr-2"></i>Criar senha de acesso
                </a>
            </div>
        </div>

        <!-- Voltar ao Site -->
        <div class="mt-6">
            <a href="/" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao site
            </a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            if (tab === 'cpf') {
                document.getElementById('tab-cpf').classList.add('text-orange-primary', 'border-orange-primary');
                document.getElementById('tab-cpf').classList.remove('text-gray-500');
                document.getElementById('tab-email').classList.add('text-gray-500');
                document.getElementById('tab-email').classList.remove('text-orange-primary', 'border-orange-primary');
                document.getElementById('form-cpf').classList.remove('hidden');
                document.getElementById('form-email').classList.add('hidden');
            } else {
                document.getElementById('tab-email').classList.add('text-orange-primary', 'border-orange-primary');
                document.getElementById('tab-email').classList.remove('text-gray-500');
                document.getElementById('tab-cpf').classList.add('text-gray-500');
                document.getElementById('tab-cpf').classList.remove('text-orange-primary', 'border-orange-primary');
                document.getElementById('form-email').classList.remove('hidden');
                document.getElementById('form-cpf').classList.add('hidden');
            }
        }

        function maskCPF(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})/, '$1-$2');
            input.value = value;
        }
    </script>
</body>
</html>