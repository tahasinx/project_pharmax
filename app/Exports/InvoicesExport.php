<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Invoice::with(['customer', 'user', 'items']);

        // Apply filters
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        if (isset($this->filters['customer_id']) && !empty($this->filters['customer_id'])) {
            $query->where('customer_id', $this->filters['customer_id']);
        }

        if (isset($this->filters['payment_type']) && !empty($this->filters['payment_type'])) {
            $query->where('payment_type', $this->filters['payment_type']);
        }

        if (isset($this->filters['date_from']) && !empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to']) && !empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Invoice ID',
            'Invoice Number',
            'Customer Name',
            'Date',
            'Total Amount',
            'Total Tax',
            'Paid Amount',
            'Due Amount',
            'Total Discount',
            'Payment Type',
            'Status',
            'Created At',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_id,
            $invoice->invoice_no,
            $invoice->customer->name ?? '',
            $invoice->date,
            $invoice->total_amount,
            $invoice->total_tax,
            $invoice->paid_amount,
            $invoice->due_amount,
            $invoice->total_discount,
            ucfirst($invoice->payment_type),
            $invoice->status ? 'Active' : 'Inactive',
            $invoice->created_at->format('Y-m-d H:i:s'),
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
            'A' => 15, // Invoice ID
            'B' => 15, // Invoice Number
            'C' => 25, // Customer Name
            'D' => 15, // Date
            'E' => 15, // Total Amount
            'F' => 12, // Total Tax
            'G' => 15, // Paid Amount
            'H' => 15, // Due Amount
            'I' => 15, // Total Discount
            'J' => 15, // Payment Type
            'K' => 10, // Status
            'L' => 20, // Created At
        ];
    }
}
