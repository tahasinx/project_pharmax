<?php

namespace App\Domain\Inventory;

use App\Models\Medicine;

class PackagingConverter
{
    public static function format(Medicine $medicine, int $baseQuantity): string
    {
        $units = $medicine->relationLoaded('units')
            ? $medicine->units
            : $medicine->units()->orderByDesc('factor_to_base')->get();

        $levels = $units->sortByDesc('factor_to_base')->values();
        if ($levels->isEmpty() || $levels->where('factor_to_base', '>', 1)->isEmpty()) {
            return $baseQuantity . ' ' . ($levels->first()->name ?? 'piece');
        }

        $remaining = $baseQuantity;
        $parts = [];
        foreach ($levels as $unit) {
            $factor = max(1, (int) $unit->factor_to_base);
            if ($factor === 1) {
                if ($remaining > 0) {
                    $parts[] = $remaining . ' ' . $unit->name;
                }
                break;
            }
            $count = intdiv($remaining, $factor);
            if ($count > 0) {
                $parts[] = $count . ' ' . $unit->name;
                $remaining -= $count * $factor;
            }
        }

        return $parts ? implode(' + ', $parts) : '0';
    }

    public static function toBase(Medicine $medicine, string $unitName, int $quantity): int
    {
        $unit = $medicine->units()->where('name', $unitName)->first();
        $factor = $unit ? (int) $unit->factor_to_base : 1;

        return $quantity * max(1, $factor);
    }
}
