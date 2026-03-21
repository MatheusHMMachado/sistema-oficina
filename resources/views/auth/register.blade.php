<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro - Sistema de Oficina</title>
    
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
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        }
        
        .gradient-bg:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
        }
        
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .icon-container {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col justify-center items-center p-4 py-8">
        <!-- Header -->
        <div class="text-center mb-10 animate-fade-in-up">
            <div class="icon-container w-28 h-28 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-user-plus text-white text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2 drop-shadow-lg">Criar Conta</h1>
            <p class="text-white/90 text-lg">Registre-se para acessar o sistema</p>
        </div>

        <!-- Card de Registro -->
        <div class="w-full max-w-md card-glass rounded-3xl shadow-2xl p-10 animate-fade-in-up">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <strong>Ops! Algo deu errado:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-orange-primary"></i>Nome Completo
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Seu nome completo">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-orange-primary"></i>E-mail
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('email') border-red-500 @enderror"
                               placeholder="seu@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-orange-primary"></i>Senha
                        </label>
                        <input type="password" 
                               name="password" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('password') border-red-500 @enderror"
                               placeholder="Mínimo 8 caracteres">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-orange-primary"></i>Confirmar Senha
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                               placeholder="Digite a senha novamente">
                    </div>

                    <button type="submit" class="w-full py-4 gradient-bg text-white rounded-xl font-bold text-lg transition-all duration-300">
                        <i class="fas fa-user-plus mr-2"></i>Criar Conta
                    </button>
                </div>
            </form>

            <!-- Login -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600 mb-3">Já tem uma conta?</p>
                <a href="{{ route('login') }}" class="inline-flex items-center text-orange-primary hover:text-orange-secondary font-semibold transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Fazer login
                </a>
            </div>
        </div>
    </div>
</body>
</html>

