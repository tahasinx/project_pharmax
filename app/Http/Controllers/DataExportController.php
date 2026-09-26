<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MedicinesExport;
use App\Exports\CustomersExport;
use App\Exports\InvoicesExport;
use App\Exports\StocksExport;
use App\Imports\MedicinesImport;
use App\Imports\CustomersImport;
use App\Imports\StocksImport;
use Carbon\Carbon;

class DataExportController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display data export/import page
     */
    public function index()
    {
        return inertia('DataExport/Index');
    }

    /**
     * Export medicines to Excel
     */
    public function exportMedicines(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,csv',
            'filters' => 'array',
        ]);

        $filters = $request->get('filters', []);
        $format = $request->get('format', 'excel');

        $filename = 'medicines_export_' . Carbon::now()->format('Y_m_d_H_i_s');

        if ($format === 'csv') {
            return Excel::download(new MedicinesExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new MedicinesExport($filters), $filename . '.xlsx');
    }

    /**
     * Export customers to Excel
     */
    public function exportCustomers(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,csv',
            'filters' => 'array',
        ]);

        $filters = $request->get('filters', []);
        $format = $request->get('format', 'excel');

        $filename = 'customers_export_' . Carbon::now()->format('Y_m_d_H_i_s');

        if ($format === 'csv') {
            return Excel::download(new CustomersExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new CustomersExport($filters), $filename . '.xlsx');
    }

    /**
     * Export invoices to Excel
     */
    public function exportInvoices(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,csv',
            'filters' => 'array',
        ]);

        $filters = $request->get('filters', []);
        $format = $request->get('format', 'excel');

        $filename = 'invoices_export_' . Carbon::now()->format('Y_m_d_H_i_s');

        if ($format === 'csv') {
            return Excel::download(new InvoicesExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new InvoicesExport($filters), $filename . '.xlsx');
    }

    /**
     * Export stocks to Excel
     */
    public function exportStocks(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,csv',
            'filters' => 'array',
        ]);

        $filters = $request->get('filters', []);
        $format = $request->get('format', 'excel');

        $filename = 'stocks_export_' . Carbon::now()->format('Y_m_d_H_i_s');

        if ($format === 'csv') {
            return Excel::download(new StocksExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new StocksExport($filters), $filename . '.xlsx');
    }

    /**
     * Import medicines from Excel/CSV
     */
    public function importMedicines(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'update_existing' => 'boolean',
        ]);

        try {
            $updateExisting = $request->get('update_existing', false);

            Excel::import(new MedicinesImport($updateExisting), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Medicines imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import customers from Excel/CSV
     */
    public function importCustomers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'update_existing' => 'boolean',
        ]);

        try {
            $updateExisting = $request->get('update_existing', false);

            Excel::import(new CustomersImport($updateExisting), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Customers imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import stocks from Excel/CSV
     */
    public function importStocks(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'update_existing' => 'boolean',
        ]);

        try {
            $updateExisting = $request->get('update_existing', false);

            Excel::import(new StocksImport($updateExisting), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Stocks imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download sample import file
     */
    public function downloadSample(Request $request)
    {
        $request->validate([
            'type' => 'required|in:medicines,customers,stocks',
            'format' => 'required|in:excel,csv',
        ]);

        $type = $request->get('type');
        $format = $request->get('format');

        $filename = $type . '_sample_' . Carbon::now()->format('Y_m_d_H_i_s');

        switch ($type) {
            case 'medicines':
                $export = new MedicinesExport([]);
                break;
            case 'customers':
                $export = new CustomersExport([]);
                break;
            case 'stocks':
                $export = new StocksExport([]);
                break;
        }

        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Get import validation rules
     */
    public function getValidationRules(Request $request)
    {
        $request->validate([
            'type' => 'required|in:medicines,customers,stocks',
        ]);

        $type = $request->get('type');

        $rules = [
            'medicines' => [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'manufacturer_id' => 'required|exists:manufacturers,id',
                'generic_name' => 'nullable|string|max:255',
                'strength' => 'nullable|string|max:100',
                'box_size' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'manufacturer_price' => 'required|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'details' => 'nullable|string',
                'status' => 'nullable|boolean',
            ],
            'customers' => [
                'name' => 'required|string|max:255',
                'mobile' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'zip' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'status' => 'nullable|boolean',
            ],
            'stocks' => [
                'medicine_id' => 'required|exists:medicines,id',
                'batch_number' => 'required|string|max:100',
                'expiry_date' => 'required|date|after:today',
                'quantity' => 'required|integer|min:0',
                'min_stock_level' => 'required|integer|min:0',
                'max_stock_level' => 'required|integer|min:0',
                'purchase_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'supplier' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $rules[$type],
        ]);
    }
}
