<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class StocksImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $updateExisting;

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        // Find medicine by name
        $medicine = Medicine::where('name', $row['medicine_name'])->first();

        if (!$medicine) {
            throw new \Exception("Medicine '{$row['medicine_name']}' not found");
        }

        $stockData = [
            'medicine_id' => $medicine->id,
            'batch_number' => $row['batch_number'],
            'expiry_date' => Carbon::parse($row['expiry_date']),
            'quantity' => $row['quantity'],
            'min_stock_level' => $row['min_stock_level'],
            'max_stock_level' => $row['max_stock_level'],
            'purchase_price' => $row['purchase_price'],
            'selling_price' => $row['selling_price'],
            'supplier' => $row['supplier'] ?? null,
            'notes' => $row['notes'] ?? null,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
        ];

        if ($this->updateExisting) {
            return Stock::updateOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => $row['batch_number']
                ],
                $stockData
            );
        }

        return new Stock($stockData);
    }

    public function rules(): array
    {
        return [
            'medicine_name' => 'required|string|exists:medicines,name',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after:today',
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ];
    }
}
