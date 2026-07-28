<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Expressões SQL compatíveis entre drivers (foco em PostgreSQL).
 */
class DatabaseCompat
{
    /**
     * Agrupa data/timestamp em ano-mês (YYYY-MM).
     */
    public static function yearMonth(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * Ordenação customizada de status (substitui FIELD() do MySQL).
     *
     * @param  array<int, string>  $orderedValues
     */
    public static function caseOrder(string $column, array $orderedValues): string
    {
        $cases = [];
        foreach (array_values($orderedValues) as $index => $value) {
            $safe = str_replace("'", "''", $value);
            $cases[] = "WHEN '{$safe}' THEN " . ($index + 1);
        }

        return 'CASE ' . $column . ' ' . implode(' ', $cases) . ' ELSE ' . (count($orderedValues) + 1) . ' END';
    }
}
