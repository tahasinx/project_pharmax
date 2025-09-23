<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class MedicineImport implements ToCollection, WithHeadingRow
{
    /**
     * Expected headers (case-insensitive):
     * name, generic_name, category, manufacturer, price
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Normalize keys to lower-case
            $data = collect($row)->keyBy(fn($v, $k) => strtolower(trim($k)));

            $name         = trim((string) ($data['name'] ?? ''));
            $genericName  = trim((string) ($data['generic_name'] ?? ''));
            $categoryName = trim((string) ($data['category'] ?? ''));
            $makerName    = trim((string) ($data['manufacturer'] ?? ''));
            $price        = is_numeric($data['price'] ?? null) ? (float) $data['price'] : null;

            if ($name === '' || $categoryName === '' || $makerName === '' || $price === null) {
                // Skip incomplete rows
                continue;
            }

            // Resolve category and manufacturer by name
            $category = Category::firstOrCreate([
                'name' => $categoryName,
            ], [
                'description' => null,
                'status' => true,
            ]);

            $manufacturer = Manufacturer::firstOrCreate([
                'name' => $makerName,
            ], [
                'address' => null,
                'mobile' => null,
                'email' => null,
                'details' => null,
                'status' => true,
            ]);

            // Skip duplicates: by name + manufacturer or exact name
            $duplicate = Medicine::where('name', $name)
                ->where('manufacturer_id', $manufacturer->id)
                ->first();

            if ($duplicate) {
                continue;
            }

            // Ensure unique product_id
            do {
                $productId = Str::random(8);
            } while (Medicine::where('product_id', $productId)->exists());

            Medicine::create([
                'product_id'         => $productId,
                'name'               => $name,
                'category_id'        => $category->id,
                'manufacturer_id'    => $manufacturer->id,
                'generic_name'       => $genericName !== '' ? $genericName : null,
                'strength'           => null,
                'box_size'           => 1,
                'product_location'   => null,
                'price'              => $price,
                'manufacturer_price' => 0,
                'unit'               => null,
                'details'            => null,
                'status'             => true,
                // Codes will be generated later by default logic if needed
            ]);
        }
    }
}
