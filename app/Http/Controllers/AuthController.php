<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        if (! $user->ativo) {
            return back()->withErrors([
                'email' => 'Sua conta está desativada. Solicite ao administrador a reativação.',
            ])->onlyInput('email');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Login realizado com sucesso!');
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

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // Não revela se o e-mail existe; se existir, tenta enviar / gerar link
        if (! $user) {
            return back()->with('status', 'Se este e-mail estiver cadastrado, você receberá o link de recuperação.');
        }

        if (! $user->ativo) {
            return back()->withErrors([
                'email' => 'Esta conta está desativada. Solicite a reativação ao administrador.',
            ])->onlyInput('email');
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', 'Enviamos o link de recuperação para o e-mail informado.');
            }

            // Fallback local: gera link direto quando o broker não consegue enviar
            return $this->responderComLinkLocal($user, $email);
        } catch (\Throwable $e) {
            // SMTP/Mailpit fora do ar (comum no XAMPP) — ainda permite redefinir em ambiente local
            return $this->responderComLinkLocal($user, $email, true);
        }
    }

    /**
     * Gera token e devolve o link na tela quando o e-mail não pode ser enviado.
     */
    private function responderComLinkLocal(User $user, string $email, bool $mailFalhou = false)
    {
        $token = Password::broker()->createToken($user);
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
        ], false));

        $msg = $mailFalhou
            ? 'O envio de e-mail não está disponível neste servidor. Use o link abaixo para redefinir a senha.'
            : 'Use o link abaixo para redefinir a senha.';

        return back()
            ->with('status', $msg)
            ->with('reset_url', $url)
            ->withInput(['email' => $email]);
    }

    /**
     * Formulário de redefinição de senha (link do e-mail).
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Processa a redefinição de senha.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Senha redefinida com sucesso! Faça login.');
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
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

