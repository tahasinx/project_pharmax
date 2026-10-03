<?php

namespace App\Domain\Compliance;

use App\Domain\Audit\AuditRecorder;
use App\Models\ControlledDrugRegister;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Stock;

class ControlledDispenseRecorder
{
    /**
     * @param  array<int, array{stock: Stock, quantity: int}>  $lines
     */
    public function record(
        Medicine $medicine,
        array $lines,
        ?int $customerId,
        ?int $branchId,
        ?int $userId,
        string $source = 'prescription',
        ?Prescription $prescription = null,
        ?Invoice $invoice = null,
        ?string $notes = null,
    ): int {
        if (! $medicine->is_controlled && ! $medicine->is_narcotic) {
            return 0;
        }

        $resolvedCustomerId = $customerId ?: Customer::walkIn()->id;

        $written = 0;
        foreach ($lines as $line) {
            /** @var Stock $stock */
            $stock = $line['stock'];
            $qty = (int) ($line['quantity'] ?? 0);
            if ($qty < 1) {
                continue;
            }

            ControlledDrugRegister::create([
                'branch_id' => $branchId,
                'customer_id' => $resolvedCustomerId,
                'medicine_id' => $medicine->id,
                'stock_id' => $stock->id,
                'prescription_id' => $prescription?->id,
                'invoice_id' => $invoice?->id,
                'source' => $source,
                'notes' => $notes,
                'quantity' => $qty,
                'user_id' => $userId,
                'dispensed_at' => now(),
            ]);
            $written++;
        }

        if ($written > 0) {
            $subject = $prescription ?: $invoice ?: $medicine;
            AuditRecorder::record('compliance', $subject, 'controlled_dispense', null, [
                'medicine_id' => $medicine->id,
                'source' => $source,
                'lines' => $written,
                'prescription_id' => $prescription?->id,
                'invoice_id' => $invoice?->id,
            ]);
        }

        return $written;
    }
}
