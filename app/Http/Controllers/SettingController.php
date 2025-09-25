<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        // Default settings for the UI form
        $defaults = [
            'company_name'        => config('app.name', 'PharmaCare'),
            'company_email'       => 'info@pharmacare.com',
            'company_phone'       => '+1 (555) 123-4567',
            'company_address'     => '123 Pharmacy Street, Medical City',
            'default_tax_rate'    => 10,
            'invoice_prefix'      => 'INV',
            'next_invoice_number' => 1000,
            'invoice_footer'      => 'Thank you for your business!',
            'currency_symbol'     => '$',
            'currency_position'   => 'before',
            'timezone'            => 'UTC',
            'date_format'         => 'Y-m-d',
            'items_per_page'      => 15,
            'enable_notifications' => true,
        ];

        // Load existing settings (only one row expected)
        $setting = Setting::query()->first();

        $settings = $defaults;
        if ($setting) {
            $settings = array_replace(
                $defaults,
                [
                    'company_name'    => $setting->title       ?? $defaults['company_name'],
                    'company_email'   => $setting->email       ?? $defaults['company_email'],
                    'company_phone'   => $setting->phone       ?? $defaults['company_phone'],
                    'company_address' => $setting->address     ?? $defaults['company_address'],
                    'invoice_footer'  => $setting->footer_text ?? $defaults['invoice_footer'],
                    'timezone'        => $setting->timezone    ?? $defaults['timezone'],
                ]
            );
        }

        // Load UI settings from settings.json
        try {
            if (\Illuminate\Support\Facades\Storage::exists('settings.json')) {
                $json = json_decode(\Illuminate\Support\Facades\Storage::get('settings.json'), true);
                if (is_array($json)) {
                    $settings = array_replace($settings, [
                        'default_tax_rate'    => $json['default_tax_rate']    ?? $settings['default_tax_rate'],
                        'invoice_prefix'      => $json['invoice_prefix']      ?? $settings['invoice_prefix'],
                        'next_invoice_number' => $json['next_invoice_number'] ?? $settings['next_invoice_number'],
                        'currency_symbol'     => $json['currency_symbol']     ?? $settings['currency_symbol'],
                        'currency_position'   => $json['currency_position']   ?? $settings['currency_position'],
                        'date_format'         => $json['date_format']         ?? $settings['date_format'],
                        'items_per_page'      => $json['items_per_page']      ?? $settings['items_per_page'],
                        'enable_notifications' => $json['enable_notifications'] ?? $settings['enable_notifications'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // noop: fallback to defaults
        }

        return Inertia::render('Settings/Index', [
            'settings'  => $settings,
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'        => 'required|string|max:255',
            'company_address'     => 'nullable|string',
            'company_phone'       => 'nullable|string|max:20',
            'company_email'       => 'nullable|email|max:255',
            'default_tax_rate'    => 'nullable|numeric|min:0|max:100',
            'currency_symbol'     => 'required|string|max:5',
            'currency_position'   => 'required|in:before,after',
            'invoice_prefix'      => 'required|string|max:10',
            'next_invoice_number' => 'required|integer|min:1',
            'invoice_footer'      => 'nullable|string',
            'timezone'            => 'required|string',
            'date_format'         => 'required|string',
            'items_per_page'      => 'required|integer|min:1|max:1000',
            'enable_notifications' => 'boolean',
        ]);

        // Upsert settings row
        $setting = Setting::query()->firstOrNew([]);
        $setting->title       = $request->input('company_name');
        $setting->menu_title  = $request->input('company_name');
        $setting->email       = $request->input('company_email');
        $setting->phone       = $request->input('company_phone');
        $setting->address     = $request->input('company_address');
        $setting->footer_text = $request->input('invoice_footer');
        $setting->timezone    = $request->input('timezone');

        if (!$setting->exists) {
            $setting->language      = 'en';
            $setting->currency      = 'USD';
            $setting->discount_type = 'percentage';
            $setting->rtl           = false;
        }

        $setting->save();

        // Persist UI-only fields to storage (no DB column required)
        $uiOnly = [
            'default_tax_rate'    => (float) $request->input('default_tax_rate', 0),
            'invoice_prefix'      => $request->input('invoice_prefix'),
            'next_invoice_number' => (int) $request->input('next_invoice_number'),
            'currency_symbol'     => $request->input('currency_symbol'),
            'currency_position'   => $request->input('currency_position'),
            'date_format'         => $request->input('date_format'),
            'items_per_page'      => (int) $request->input('items_per_page'),
            'enable_notifications' => (bool) $request->boolean('enable_notifications'),
        ];
        \Illuminate\Support\Facades\Storage::put('settings.json', json_encode($uiOnly, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Update runtime configs immediately
        config([
            'app.name'     => $setting->title,
            'app.timezone' => $setting->timezone,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
