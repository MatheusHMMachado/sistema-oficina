<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ConfiguracaoSistema extends Model
{
    protected $table = 'configuracoes_sistema';

    protected $fillable = [
        'nome_empresa',
        'cor_primaria',
        'cor_secundaria',
        'tema',
        'logo_path',
    ];

    public static function atual(): self
    {
        try {
            if (! Schema::hasTable('configuracoes_sistema')) {
                return self::defaults();
            }

            return Cache::remember('configuracao_sistema', 60, function () {
                $cfg = self::query()->first();

                return $cfg ?: self::query()->create([
                    'nome_empresa' => 'Sistema de Oficina',
                    'cor_primaria' => '#FF6B35',
                    'cor_secundaria' => '#FF8C42',
                    'tema' => 'light',
                ]);
            });
        } catch (\Throwable $e) {
            return self::defaults();
        }
    }

    public static function limparCache(): void
    {
        Cache::forget('configuracao_sistema');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . ltrim($this->logo_path, '/')) : null;
    }

    public function isDark(): bool
    {
        return $this->tema === 'dark';
    }

    /** Clareia a cor misturando com branco (0 = original, 1 = branco). */
    public function corClara(?string $hex = null, float $fator = 0.88): string
    {
        return $this->misturar($hex ?? $this->cor_primaria, '#FFFFFF', $fator);
    }

    /** Escurece a cor misturando com preto (0 = original, 1 = preto). */
    public function corEscura(?string $hex = null, float $fator = 0.35): string
    {
        return $this->misturar($hex ?? $this->cor_primaria, '#000000', $fator);
    }

    public function misturar(string $hexA, string $hexB, float $fator): string
    {
        $fator = max(0, min(1, $fator));
        [$r1, $g1, $b1] = $this->hexParaRgb($hexA);
        [$r2, $g2, $b2] = $this->hexParaRgb($hexB);

        return sprintf(
            '#%02X%02X%02X',
            (int) round($r1 + ($r2 - $r1) * $fator),
            (int) round($g1 + ($g2 - $g1) * $fator),
            (int) round($b1 + ($b2 - $b1) * $fator)
        );
    }

    /** @return array{0:int,1:int,2:int} */
    public function hexParaRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            return [255, 107, 53];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function defaults(): self
    {
        $m = new self();
        $m->forceFill([
            'nome_empresa' => 'Sistema de Oficina',
            'cor_primaria' => '#FF6B35',
            'cor_secundaria' => '#FF8C42',
            'tema' => 'light',
            'logo_path' => null,
        ]);

        return $m;
    }
}
