<?php

namespace App\Imports;

use App\Models\Medicine;
use App\Models\Category;
use App\Models\Manufacturer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Str;

class MedicinesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $updateExisting;

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        // Find or create category
        $category = Category::firstOrCreate(
            ['name' => $row['category']],
            ['description' => 'Imported category', 'status' => true]
        );

        // Find or create manufacturer
        $manufacturer = Manufacturer::firstOrCreate(
            ['name' => $row['manufacturer']],
            ['status' => true]
        );

        $medicineData = [
            'product_id' => $row['product_id'] ?? 'MED' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => $row['name'],
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'generic_name' => $row['generic_name'] ?? null,
            'strength' => $row['strength'] ?? null,
            'box_size' => $row['box_size'],
            'price' => $row['price'],
            'manufacturer_price' => $row['manufacturer_price'],
            'unit' => $row['unit'] ?? 'tablet',
            'details' => $row['details'] ?? null,
            'status' => isset($row['status']) ? (bool) $row['status'] : true,
        ];

        if ($this->updateExisting) {
            return Medicine::updateOrCreate(
                ['name' => $row['name']],
                $medicineData
            );
        }

        return new Medicine($medicineData);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'box_size' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
        ];
    }
}
