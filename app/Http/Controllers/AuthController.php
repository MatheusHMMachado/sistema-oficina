<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Mostra o formulário de login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Processa o login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                           ->with('success', 'Login realizado com sucesso!');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    /**
     * Mostra o formulário de registro
     */
    public function showRegistrationForm()
    {
        // Permite cadastro público somente se ainda não existir nenhum usuário (bootstrap inicial).
        if (User::count() > 0 && (!Auth::check() || !Auth::user()->isAdmin())) {
            return redirect()->route('login')
                ->with('error', 'Cadastro público desativado. Solicite um administrador para criar seu usuário.');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Processa o registro
     */
    public function register(Request $request)
    {
        $hasUsers = User::count() > 0;
        if ($hasUsers && (!Auth::check() || !Auth::user()->isAdmin())) {
            return redirect()->route('login')
                ->with('error', 'Somente administradores podem cadastrar novos usuários.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $hasUsers ? 'funcionario' : 'admin',
            'ativo' => true,
        ]);
        // No primeiro usuário do sistema, faz login automático.
        if (!$hasUsers) {
            Auth::login($user);
            return redirect()->route('dashboard')
                ->with('success', 'Conta administrativa inicial criada com sucesso!');
        }

        return redirect()->route('funcionarios.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Processa o logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                        ->with('success', 'Logout realizado com sucesso!');
    }

    /**
     * Mostra o formulário de recuperação de senha
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envia o link de recuperação de senha
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Verifica se o usuário está autenticado (API)
     */
    public function checkAuth()
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::check() ? [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ] : null,
        ]);
    }
}

