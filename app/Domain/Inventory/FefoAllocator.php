<?php

namespace App\Domain\Inventory;

use App\Models\Stock;
use Illuminate\Support\Collection;

class FefoAllocator
{
    /**
     * @return array{lines: array<int, array{stock: Stock, quantity: int, cost: float}>, short: int, cost: float}
     */
    public function allocate(int $medicineId, int $baseQuantity, ?int $branchId): array
    {
        $query = Stock::query()
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'available');
            })
            ->where(function ($q) {
                $q->where('recalled', false)->orWhereNull('recalled');
            })
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date');

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }

        /** @var Collection<int, Stock> $stocks */
        $stocks    = $query->lockForUpdate()->get();
        $remaining = $baseQuantity;
        $lines     = [];
        $cost      = 0.0;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }
            $take     = min($remaining, (int) $stock->quantity);
            $unitCost = (float) ($stock->purchase_price ?? 0);
            $lines[]  = ['stock' => $stock, 'quantity' => $take, 'cost' => $take * $unitCost];
            $cost += $take * $unitCost;
            $remaining -= $take;
        }

        return ['lines' => $lines, 'short' => $remaining, 'cost' => $cost];
    }
}
