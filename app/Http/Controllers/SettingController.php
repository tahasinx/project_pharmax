<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Display the settings page with current configuration
     */
    public function index()
    {
        $defaults = $this->getDefaultSettings();
        $settings = $this->loadDatabaseSettings($defaults);
        $settings = $this->loadJsonSettings($settings);

        return Inertia::render('Settings/Index', [
            'settings'  => $settings,
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $this->validateSettings($request);

        $this->updateDatabaseSettings($request);
        $this->updateJsonSettings($request);
        $this->updateEnvFile($request->all());
        $this->updateRuntimeConfig($request);

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Send test email to verify configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email'         => 'required|email',
            'email_config'  => 'required|array',
        ]);

        try {
            $this->configureMailSettings($request->input('email_config'));
            $this->sendTestEmail($request->input('email'), $request->input('email_config'));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default settings configuration
     */
    private function getDefaultSettings(): array
    {
        return [
            // Company Information
            'company_name'         => config('app.name', 'PharmaCare'),
            'company_email'        => 'info@pharmacare.com',
            'company_phone'        => '+1 (555) 123-4567',
            'company_address'      => '123 Pharmacy Street, Medical City',

            // Invoice Settings
            'default_tax_rate'     => 10,
            'invoice_prefix'       => 'INV',
            'next_invoice_number'  => 1000,
            'invoice_footer'       => 'Thank you for your business!',

            // Currency Settings
            'currency_symbol'      => '$',
            'currency_position'    => 'before',

            // System Settings
            'timezone'             => 'UTC',
            'date_format'          => 'Y-m-d',
            'items_per_page'       => 15,
            'enable_notifications' => true,

            // Email Configuration
            'email_provider'       => 'smtp',
            'smtp_host'            => '',
            'smtp_port'            => 587,
            'smtp_username'        => '',
            'smtp_password'        => '',
            'smtp_encryption'      => 'tls',
            'mailgun_domain'       => '',
            'mailgun_secret'       => '',
            'ses_key'              => '',
            'ses_secret'           => '',
            'ses_region'           => 'us-east-1',
            'mail_from_name'       => '',
            'mail_from_address'    => '',
        ];
    }

    /**
     * Load settings from database
     */
    private function loadDatabaseSettings(array $defaults): array
    {
        $setting = Setting::query()->first();

        if (!$setting) {
            return $defaults;
        }

        return array_replace($defaults, [
            'company_name'    => $setting->title ?? $defaults['company_name'],
            'company_email'   => $setting->email ?? $defaults['company_email'],
            'company_phone'   => $setting->phone ?? $defaults['company_phone'],
            'company_address' => $setting->address ?? $defaults['company_address'],
            'invoice_footer'  => $setting->footer_text ?? $defaults['invoice_footer'],
            'timezone'        => $setting->timezone ?? $defaults['timezone'],
        ]);
    }

    /**
     * Load settings from JSON file
     */
    private function loadJsonSettings(array $settings): array
    {
        try {
            if (!Storage::exists('settings.json')) {
                return $settings;
            }

            $json = json_decode(Storage::get('settings.json'), true);

            if (!is_array($json)) {
                return $settings;
            }

            return array_replace($settings, [
                // Invoice Settings
                'default_tax_rate'     => $json['default_tax_rate'] ?? $settings['default_tax_rate'],
                'invoice_prefix'       => $json['invoice_prefix'] ?? $settings['invoice_prefix'],
                'next_invoice_number'  => $json['next_invoice_number'] ?? $settings['next_invoice_number'],

                // Currency Settings
                'currency_symbol'      => $json['currency_symbol'] ?? $settings['currency_symbol'],
                'currency_position'    => $json['currency_position'] ?? $settings['currency_position'],

                // System Settings
                'date_format'          => $json['date_format'] ?? $settings['date_format'],
                'items_per_page'       => $json['items_per_page'] ?? $settings['items_per_page'],
                'enable_notifications' => $json['enable_notifications'] ?? $settings['enable_notifications'],

                // Email Configuration
                'email_provider'       => $json['email_provider'] ?? $settings['email_provider'],
                'smtp_host'            => $json['smtp_host'] ?? $settings['smtp_host'],
                'smtp_port'            => $json['smtp_port'] ?? $settings['smtp_port'],
                'smtp_username'        => $json['smtp_username'] ?? $settings['smtp_username'],
                'smtp_password'        => $json['smtp_password'] ?? $settings['smtp_password'],
                'smtp_encryption'      => $json['smtp_encryption'] ?? $settings['smtp_encryption'],
                'mailgun_domain'       => $json['mailgun_domain'] ?? $settings['mailgun_domain'],
                'mailgun_secret'       => $json['mailgun_secret'] ?? $settings['mailgun_secret'],
                'ses_key'              => $json['ses_key'] ?? $settings['ses_key'],
                'ses_secret'           => $json['ses_secret'] ?? $settings['ses_secret'],
                'ses_region'           => $json['ses_region'] ?? $settings['ses_region'],
                'mail_from_name'       => $json['mail_from_name'] ?? $settings['mail_from_name'],
                'mail_from_address'    => $json['mail_from_address'] ?? $settings['mail_from_address'],
            ]);
        } catch (\Throwable $e) {
            return $settings;
        }
    }

    /**
     * Validate settings request
     */
    private function validateSettings(Request $request): void
    {
        $request->validate([
            // Company Information
            'company_name'         => 'required|string|max:255',
            'company_address'      => 'nullable|string',
            'company_phone'        => 'nullable|string|max:20',
            'company_email'        => 'nullable|email|max:255',

            // Invoice Settings
            'default_tax_rate'     => 'nullable|numeric|min:0|max:100',
            'invoice_prefix'       => 'required|string|max:10',
            'next_invoice_number'  => 'required|integer|min:1',
            'invoice_footer'       => 'nullable|string',

            // Currency Settings
            'currency_symbol'      => 'required|string|max:5',
            'currency_position'    => 'required|in:before,after',

            // System Settings
            'timezone'             => 'required|string',
            'date_format'          => 'required|string',
            'items_per_page'       => 'required|integer|min:1|max:1000',
            'enable_notifications' => 'boolean',

            // Email Configuration
            'email_provider'       => 'required|in:smtp,mailgun,ses,sendmail',
            'smtp_host'            => 'nullable|string|max:255',
            'smtp_port'            => 'nullable|integer|min:1|max:65535',
            'smtp_username'        => 'nullable|string|max:255',
            'smtp_password'        => 'nullable|string|max:255',
            'smtp_encryption'      => 'nullable|in:tls,ssl,',
            'mailgun_domain'       => 'nullable|string|max:255',
            'mailgun_secret'       => 'nullable|string|max:255',
            'ses_key'              => 'nullable|string|max:255',
            'ses_secret'           => 'nullable|string|max:255',
            'ses_region'           => 'nullable|string|max:255',
            'mail_from_name'       => 'nullable|string|max:255',
            'mail_from_address'    => 'nullable|email|max:255',
        ]);
    }

    /**
     * Update database settings
     */
    private function updateDatabaseSettings(Request $request): void
    {
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
    }

    /**
     * Update JSON settings file
     */
    private function updateJsonSettings(Request $request): void
    {
        $uiOnly = [
            // Invoice Settings
            'default_tax_rate'     => (float) $request->input('default_tax_rate', 0),
            'invoice_prefix'       => $request->input('invoice_prefix'),
            'next_invoice_number'  => (int) $request->input('next_invoice_number'),

            // Currency Settings
            'currency_symbol'      => $request->input('currency_symbol'),
            'currency_position'    => $request->input('currency_position'),

            // System Settings
            'date_format'          => $request->input('date_format'),
            'items_per_page'       => (int) $request->input('items_per_page'),
            'enable_notifications' => (bool) $request->boolean('enable_notifications'),

            // Email Configuration
            'email_provider'       => $request->input('email_provider'),
            'smtp_host'            => $request->input('smtp_host'),
            'smtp_port'            => (int) $request->input('smtp_port', 587),
            'smtp_username'        => $request->input('smtp_username'),
            'smtp_password'        => $request->input('smtp_password'),
            'smtp_encryption'      => $request->input('smtp_encryption'),
            'mailgun_domain'       => $request->input('mailgun_domain'),
            'mailgun_secret'       => $request->input('mailgun_secret'),
            'ses_key'              => $request->input('ses_key'),
            'ses_secret'           => $request->input('ses_secret'),
            'ses_region'           => $request->input('ses_region'),
            'mail_from_name'       => $request->input('mail_from_name'),
            'mail_from_address'    => $request->input('mail_from_address'),
        ];

        Storage::put('settings.json', json_encode($uiOnly, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Update runtime configuration
     */
    private function updateRuntimeConfig(Request $request): void
    {
        config([
            'app.name'     => $request->input('company_name'),
            'app.timezone' => $request->input('timezone'),
        ]);
    }

    /**
     * Configure mail settings for testing
     */
    private function configureMailSettings(array $emailConfig): void
    {
        config([
            'mail.default'                    => $emailConfig['email_provider'],
            'mail.mailers.smtp.host'          => $emailConfig['smtp_host'] ?? '',
            'mail.mailers.smtp.port'          => $emailConfig['smtp_port'] ?? 587,
            'mail.mailers.smtp.username'     => $emailConfig['smtp_username'] ?? '',
            'mail.mailers.smtp.password'     => $emailConfig['smtp_password'] ?? '',
            'mail.mailers.smtp.encryption'   => $emailConfig['smtp_encryption'] ?? 'tls',
            'mail.from.name'                  => $emailConfig['mail_from_name'] ?? config('app.name'),
            'mail.from.address'               => $emailConfig['mail_from_address'] ?? $emailConfig['smtp_username'] ?? 'noreply@example.com',
        ]);
    }

    /**
     * Send test email
     */
    private function sendTestEmail(string $email, array $emailConfig): void
    {
        $fromAddress = $emailConfig['mail_from_address'] ?? $emailConfig['smtp_username'] ?? 'noreply@example.com';
        $fromName = $emailConfig['mail_from_name'] ?? config('app.name');

        Mail::raw(
            'This is a test email from your PharmaCare system. Your email configuration is working correctly!',
            function ($message) use ($email, $fromAddress, $fromName) {
                $message->to($email)
                    ->subject('Test Email from PharmaCare')
                    ->from($fromAddress, $fromName);
            }
        );
    }

    /**
     * Update .env file with email configuration
     */
    private function updateEnvFile(array $emailConfig): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        $envMappings = $this->getEnvMappings($emailConfig);

        foreach ($envMappings as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Get environment variable mappings
     */
    private function getEnvMappings(array $emailConfig): array
    {
        return [
            // SMTP Configuration
            'MAIL_MAILER'          => $emailConfig['email_provider'],
            'MAIL_HOST'             => $emailConfig['smtp_host'] ?? '',
            'MAIL_PORT'             => $emailConfig['smtp_port'] ?? 587,
            'MAIL_USERNAME'         => $emailConfig['smtp_username'] ?? '',
            'MAIL_PASSWORD'         => $emailConfig['smtp_password'] ?? '',
            'MAIL_ENCRYPTION'       => $emailConfig['smtp_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS'     => $emailConfig['mail_from_address'] ?? $emailConfig['smtp_username'] ?? '',
            'MAIL_FROM_NAME'        => $emailConfig['mail_from_name'] ?? config('app.name'),

            // Mailgun Configuration
            'MAILGUN_DOMAIN'        => $emailConfig['mailgun_domain'] ?? '',
            'MAILGUN_SECRET'        => $emailConfig['mailgun_secret'] ?? '',

            // AWS SES Configuration
            'AWS_ACCESS_KEY_ID'     => $emailConfig['ses_key'] ?? '',
            'AWS_SECRET_ACCESS_KEY' => $emailConfig['ses_secret'] ?? '',
            'AWS_DEFAULT_REGION'    => $emailConfig['ses_region'] ?? 'us-east-1',
        ];
    }
}
