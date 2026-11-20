<?php

namespace App\Support;

/**
 * Affichage des quantités : le stock est en unité de base (g, mL, pcs),
 * mais les gens pensent en kg et en litres.
 */
class Qty
{
    /** @return array{label: string, factor: int} unité d'affichage et facteur vers l'unité de base */
    public static function displayUnit(?string $symbol): array
    {
        return match (strtolower((string) $symbol)) {
            'g'     => ['label' => 'kg', 'factor' => 1000],
            'ml'    => ['label' => 'L', 'factor' => 1000],
            default => ['label' => (string) $symbol, 'factor' => 1],
        };
    }

    /** 15000 g → « 15 kg » ; 500 g → « 500 g ». */
    public static function format($base, ?string $symbol): string
    {
        $base = (float) $base;
        $d = self::displayUnit($symbol);
        if ($d['factor'] > 1 && abs($base) >= $d['factor']) {
            return self::num($base / $d['factor']) . ' ' . $d['label'];
        }

        return trim(self::num($base) . ' ' . $symbol);
    }

    public static function num($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, ',', ' '), '0'), ',');
    }
}
