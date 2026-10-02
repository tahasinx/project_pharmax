<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Customer::query();

        // Apply filters
        if (isset($this->filters['search']) && ! empty($this->filters['search'])) {
            $query->where('name', 'like', '%'.$this->filters['search'].'%')
                ->orWhere('mobile', 'like', '%'.$this->filters['search'].'%')
                ->orWhere('email', 'like', '%'.$this->filters['search'].'%');
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['city']) && ! empty($this->filters['city'])) {
            $query->where('city', 'like', '%'.$this->filters['city'].'%');
        }

        if (isset($this->filters['date_from']) && ! empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to']) && ! empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Mobile',
            'Email',
            'Phone',
            'Address',
            'City',
            'State',
            'ZIP',
            'Country',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->mobile,
            $customer->email,
            $customer->phone,
            $customer->address,
            $customer->city,
            $customer->state,
            $customer->zip,
            $customer->country,
            $customer->status ? 'Active' : 'Inactive',
            $customer->created_at->format('Y-m-d H:i:s'),
            $customer->updated_at->format('Y-m-d H:i:s'),
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
            'A' => 25, // Name
            'B' => 15, // Mobile
            'C' => 25, // Email
            'D' => 15, // Phone
            'E' => 30, // Address
            'F' => 15, // City
            'G' => 15, // State
            'H' => 10, // ZIP
            'I' => 15, // Country
            'J' => 10, // Status
            'K' => 20, // Created At
            'L' => 20, // Updated At
        ];
    }
}
