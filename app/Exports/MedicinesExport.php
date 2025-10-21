<?php

namespace App\Exports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MedicinesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Medicine::with(['category', 'manufacturer', 'stocks']);

        // Apply filters
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . $this->filters['search'] . '%')
                ->orWhere('generic_name', 'like', '%' . $this->filters['search'] . '%');
        }

        if (isset($this->filters['category_id']) && !empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (isset($this->filters['manufacturer_id']) && !empty($this->filters['manufacturer_id'])) {
            $query->where('manufacturer_id', $this->filters['manufacturer_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['date_from']) && !empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to']) && !empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Name',
            'Generic Name',
            'Category',
            'Manufacturer',
            'Strength',
            'Box Size',
            'Unit',
            'Price',
            'Manufacturer Price',
            'Total Stock',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($medicine): array
    {
        return [
            $medicine->product_id,
            $medicine->name,
            $medicine->generic_name,
            $medicine->category->name ?? '',
            $medicine->manufacturer->name ?? '',
            $medicine->strength,
            $medicine->box_size,
            $medicine->unit,
            $medicine->price,
            $medicine->manufacturer_price,
            $medicine->getTotalStockAttribute(),
            $medicine->status ? 'Active' : 'Inactive',
            $medicine->created_at->format('Y-m-d H:i:s'),
            $medicine->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Product ID
            'B' => 25, // Name
            'C' => 20, // Generic Name
            'D' => 20, // Category
            'E' => 20, // Manufacturer
            'F' => 15, // Strength
            'G' => 12, // Box Size
            'H' => 10, // Unit
            'I' => 12, // Price
            'J' => 18, // Manufacturer Price
            'K' => 12, // Total Stock
            'L' => 10, // Status
            'M' => 20, // Created At
            'N' => 20, // Updated At
        ];
    }
}
