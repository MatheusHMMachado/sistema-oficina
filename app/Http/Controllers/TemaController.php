<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemaController extends Controller
{
    public function edit()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Apenas administradores podem alterar a identidade visual.');
        }

        $config = ConfiguracaoSistema::atual();

        return view('configuracoes.tema', compact('config'));
    }

    public function update(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'nome_empresa' => 'required|string|max:120',
            'cor_primaria' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cor_secundaria' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tema' => 'required|in:light,dark',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ], [
            'cor_primaria.regex' => 'Informe a cor primária no formato #RRGGBB.',
            'cor_secundaria.regex' => 'Informe a cor secundária no formato #RRGGBB.',
        ]);

        $config = ConfiguracaoSistema::query()->first();

        if (! $config) {
            $config = ConfiguracaoSistema::query()->create([
                'nome_empresa' => $request->nome_empresa,
                'cor_primaria' => $request->cor_primaria,
                'cor_secundaria' => $request->cor_secundaria,
                'tema' => $request->tema,
            ]);
        }

        $data = $request->only(['nome_empresa', 'cor_primaria', 'cor_secundaria', 'tema']);

        if ($request->hasFile('logo')) {
            if ($config->logo_path) {
                Storage::disk('public')->delete($config->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->boolean('remover_logo') && $config->logo_path) {
            Storage::disk('public')->delete($config->logo_path);
            $data['logo_path'] = null;
        }

        $config->update($data);
        ConfiguracaoSistema::limparCache();

        return back()->with('success', 'Identidade visual atualizada. As alterações valem para todo o sistema.');
    }
}
