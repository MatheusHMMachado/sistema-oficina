<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                           ->with('error', 'Você precisa estar autenticado para acessar esta área.');
        }

        $user = Auth::user();
        
        // Verificar se o usuário é admin
        // Assumindo que existe um campo 'role' ou 'is_admin' no model User
        // Se não existir, considerar o primeiro usuário como admin
        $isAdmin = false;
        
        if (isset($user->role) && $user->role === 'admin') {
            $isAdmin = true;
        } elseif (isset($user->is_admin) && $user->is_admin) {
            $isAdmin = true;
        } elseif ($user->id === 1) {
            // Primeiro usuário é admin por padrão
            $isAdmin = true;
        }

        if (!$isAdmin) {
            return redirect()->route('dashboard')
                           ->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}

