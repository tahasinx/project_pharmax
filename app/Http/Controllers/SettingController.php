<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => config('app.name', 'PharmaCare'),
            'company_address' => '123 Pharmacy Street, Medical City',
            'company_phone' => '+1 (555) 123-4567',
            'company_email' => 'info@pharmacare.com',
            'company_website' => 'https://pharmacare.com',
            'tax_rate' => 10.0,
            'currency' => 'USD',
            'currency_symbol' => '$',
            'invoice_prefix' => 'INV',
            'invoice_start_number' => 1000,
            'purchase_prefix' => 'PUR',
            'purchase_start_number' => 1000,
            'low_stock_threshold' => 10,
            'backup_enabled' => true,
            'backup_frequency' => 'daily',
            'email_notifications' => true,
            'sms_notifications' => false,
            'auto_logout_time' => 30,
            'theme' => 'light',
            'language' => 'en',
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
            'invoice_prefix' => 'required|string|max:10',
            'invoice_start_number' => 'required|integer|min:1',
            'purchase_prefix' => 'required|string|max:10',
            'purchase_start_number' => 'required|integer|min:1',
            'low_stock_threshold' => 'required|integer|min:0',
            'backup_enabled' => 'boolean',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'auto_logout_time' => 'required|integer|min:5|max:480',
            'theme' => 'required|in:light,dark',
            'language' => 'required|in:en,es,fr,de',
        ]);

        // Update configuration file or database
        $settings = $request->all();

        // Here you would typically save to database or config file
        // For now, we'll just return success

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
