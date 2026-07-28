<!DOCTYPE html>
<html lang="pt-BR" class="{{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark() ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar Senha - {{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->nome_empresa }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.brand-theme')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.login-page {
            min-height: 100vh;
            background: {{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark()
                ? 'radial-gradient(ellipse at top, #1a2332 0%, #0b1220 55%, #070b12 100%)'
                : 'radial-gradient(ellipse at top, #f8fafc 0%, #eef2f7 55%, #e8edf5 100%)' }};
        }
        .login-card {
            background: {{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark()
                ? 'rgba(26, 35, 50, 0.72)'
                : 'rgba(255, 255, 255, 0.72)' }};
            backdrop-filter: blur(18px);
            border: 1px solid {{ ($brand ?? \App\Models\ConfiguracaoSistema::atual())->isDark()
                ? 'rgba(255,255,255,0.08)'
                : 'rgba(255,255,255,0.7)' }};
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        }
        .login-input:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(var(--brand-primary-rgb), 0.2);
        }
    </style>
</head>
<body class="login-page font-sans antialiased">
@php $cfg = $brand ?? \App\Models\ConfiguracaoSistema::atual(); @endphp
<div class="min-h-screen flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            @if($cfg->logoUrl())
                <img src="{{ $cfg->logoUrl() }}" alt="{{ $cfg->nome_empresa }}" class="h-16 mx-auto mb-4 object-contain">
            @else
                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white shadow-lg"
                     style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))">
                    <i class="fas fa-key text-2xl"></i>
                </div>
            @endif
            <h1 class="text-2xl font-bold tracking-tight" style="color: {{ $cfg->isDark() ? '#e8eef7' : '#111827' }}">Recuperar senha</h1>
            <p class="text-sm mt-1" style="color: {{ $cfg->isDark() ? '#9aa8bc' : '#6b7280' }}">
                Digite seu e-mail para receber o link de recuperação
            </p>
        </div>

        <div class="login-card rounded-2xl p-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                    <i class="fas fa-check-circle mr-1"></i>{{ session('status') }}
                </div>
            @endif

            @if (session('reset_url'))
                <div class="mb-4 bg-sky-50 border border-sky-200 text-sky-900 px-4 py-3 rounded-xl text-sm break-all">
                    <p class="font-semibold mb-1">Link de redefinição (ambiente local):</p>
                    <a href="{{ session('reset_url') }}" class="underline font-medium" style="color: var(--brand-primary)">
                        {{ session('reset_url') }}
                    </a>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: {{ $cfg->isDark() ? '#c5d0de' : '#374151' }}">
                        <i class="fas fa-envelope mr-1" style="color: var(--brand-primary)"></i> E-mail
                    </label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="login-input w-full px-4 py-3 rounded-xl border border-gray-300 bg-white/80 @error('email') border-red-500 @enderror"
                           placeholder="seu@email.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl text-white font-semibold btn-primary">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar link de recuperação
                </button>
            </form>

            <div class="mt-6 pt-5 border-t text-center text-sm" style="border-color: var(--border-soft)">
                <a href="{{ route('login') }}" class="inline-flex items-center font-medium" style="color: var(--brand-primary)">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao login
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
