<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StocksExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Stock::with(['medicine.category', 'medicine.manufacturer']);

        // Apply filters
        if (isset($this->filters['search']) && ! empty($this->filters['search'])) {
            $query->whereHas('medicine', function ($q) {
                $q->where('name', 'like', '%'.$this->filters['search'].'%');
            });
        }

        if (isset($this->filters['medicine_id']) && ! empty($this->filters['medicine_id'])) {
            $query->where('medicine_id', $this->filters['medicine_id']);
        }

        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (isset($this->filters['expiry_from']) && ! empty($this->filters['expiry_from'])) {
            $query->whereDate('expiry_date', '>=', $this->filters['expiry_from']);
        }

        if (isset($this->filters['expiry_to']) && ! empty($this->filters['expiry_to'])) {
            $query->whereDate('expiry_date', '<=', $this->filters['expiry_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Medicine Name',
            'Category',
            'Manufacturer',
            'Batch Number',
            'Expiry Date',
            'Quantity',
            'Min Stock Level',
            'Max Stock Level',
            'Purchase Price',
            'Selling Price',
            'Supplier',
            'Status',
            'Created At',
        ];
    }

    public function map($stock): array
    {
        return [
            $stock->medicine->name ?? '',
            $stock->medicine->category->name ?? '',
            $stock->medicine->manufacturer->name ?? '',
            $stock->batch_number,
            $stock->expiry_date->format('Y-m-d'),
            $stock->quantity,
            $stock->min_stock_level,
            $stock->max_stock_level,
            $stock->purchase_price,
            $stock->selling_price,
            $stock->supplier,
            $stock->is_active ? 'Active' : 'Inactive',
            $stock->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Medicine Name
            'B' => 20, // Category
            'C' => 20, // Manufacturer
            'D' => 15, // Batch Number
            'E' => 15, // Expiry Date
            'F' => 12, // Quantity
            'G' => 15, // Min Stock Level
            'H' => 15, // Max Stock Level
            'I' => 15, // Purchase Price
            'J' => 15, // Selling Price
            'K' => 20, // Supplier
            'L' => 10, // Status
            'M' => 20, // Created At
        ];
    }
}
