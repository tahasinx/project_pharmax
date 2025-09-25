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
                'message' => 'Test email sent successfully!',
                'data' => [
                    'email' => $request->input('email'),
                    'provider' => $request->input('email_config')['email_provider'] ?? 'smtp'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'error' => $e->getMessage()
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

            // SMS Configuration
            'sms_provider'         => 'custom',
            'sms_api_url'          => '',
            'sms_http_method'      => 'POST',
            'sms_custom_params'    => [],
            'twilio_sid'           => '',
            'twilio_token'         => '',
            'twilio_from'          => '',
            'nexmo_key'            => '',
            'nexmo_secret'         => '',
            'nexmo_from'           => '',
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

                // SMS Configuration
                'sms_provider'         => $json['sms_provider'] ?? $settings['sms_provider'],
                'sms_api_url'          => $json['sms_api_url'] ?? $settings['sms_api_url'],
                'sms_http_method'     => $json['sms_http_method'] ?? $settings['sms_http_method'],
                'sms_custom_params'   => $json['sms_custom_params'] ?? $settings['sms_custom_params'],
                'twilio_sid'           => $json['twilio_sid'] ?? $settings['twilio_sid'],
                'twilio_token'         => $json['twilio_token'] ?? $settings['twilio_token'],
                'twilio_from'          => $json['twilio_from'] ?? $settings['twilio_from'],
                'nexmo_key'            => $json['nexmo_key'] ?? $settings['nexmo_key'],
                'nexmo_secret'         => $json['nexmo_secret'] ?? $settings['nexmo_secret'],
                'nexmo_from'           => $json['nexmo_from'] ?? $settings['nexmo_from'],
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

            // SMS Configuration
            'sms_provider'         => 'required|in:twilio,nexmo,custom',
            'sms_api_url'          => 'nullable|url|max:255',
            'sms_http_method'     => 'nullable|in:GET,POST,PUT',
            'sms_custom_params'   => 'nullable|array',
            'twilio_sid'           => 'nullable|string|max:255',
            'twilio_token'         => 'nullable|string|max:255',
            'twilio_from'           => 'nullable|string|max:20',
            'nexmo_key'            => 'nullable|string|max:255',
            'nexmo_secret'         => 'nullable|string|max:255',
            'nexmo_from'            => 'nullable|string|max:20',
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

            // SMS Configuration
            'sms_provider'         => $request->input('sms_provider'),
            'sms_api_url'          => $request->input('sms_api_url'),
            'sms_http_method'     => $request->input('sms_http_method'),
            'sms_custom_params'   => $request->input('sms_custom_params'),
            'twilio_sid'           => $request->input('twilio_sid'),
            'twilio_token'         => $request->input('twilio_token'),
            'twilio_from'           => $request->input('twilio_from'),
            'nexmo_key'            => $request->input('nexmo_key'),
            'nexmo_secret'         => $request->input('nexmo_secret'),
            'nexmo_from'            => $request->input('nexmo_from'),
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
     * Send test SMS to verify configuration
     */
    public function testSms(Request $request)
    {
        $request->validate([
            'phone'      => 'required|string',
            'sms_config' => 'required|array',
        ]);

        try {
            $smsConfig = $request->input('sms_config');
            $phone = $request->input('phone');

            $response = $this->sendSms($phone, 'Test SMS from PharmaCare system. Your SMS configuration is working correctly!', $smsConfig);

            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully!',
                'data' => [
                    'phone' => $phone,
                    'provider' => $smsConfig['sms_provider'] ?? 'custom',
                    'response' => $response
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test SMS: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS using configured provider
     */
    private function sendSms(string $phone, string $message, array $config): string
    {
        switch ($config['sms_provider']) {
            case 'twilio':
                return $this->sendTwilioSms($phone, $message, $config);
            case 'nexmo':
                return $this->sendNexmoSms($phone, $message, $config);
            case 'custom':
                return $this->sendCustomSms($phone, $message, $config);
            default:
                throw new \Exception('Unsupported SMS provider: ' . $config['sms_provider']);
        }
    }


    /**
     * Send SMS via Twilio
     */
    private function sendTwilioSms(string $phone, string $message, array $config): string
    {
        $sid = $config['twilio_sid'];
        $token = $config['twilio_token'];
        $from = $config['twilio_from'];

        $formattedPhone = $this->formatPhoneNumber($phone, $config['sms_country_code'] ?? '1');

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $data = [
            'From' => $from,
            'To'   => $formattedPhone,
            'Body' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Twilio API error: ' . $response);
        }

        return $response;
    }

    /**
     * Send SMS via Nexmo (Vonage)
     */
    private function sendNexmoSms(string $phone, string $message, array $config): string
    {
        $apiKey = $config['nexmo_key'];
        $apiSecret = $config['nexmo_secret'];
        $from = $config['nexmo_from'];

        $formattedPhone = $this->formatPhoneNumber($phone, $config['sms_country_code'] ?? '44');

        $url = 'https://rest.nexmo.com/sms/json';

        $data = [
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret,
            'to'         => $formattedPhone,
            'from'       => $from,
            'text'       => $message
        ];

        return $this->makeHttpRequest($url, $data);
    }

    /**
     * Send SMS via Custom API
     */
    private function sendCustomSms(string $phone, string $message, array $config): string
    {
        $url = $config['sms_api_url'];
        $httpMethod = $config['sms_http_method'] ?? 'POST';
        $customParams = $config['sms_custom_params'] ?? [];

        // Build data array from custom parameters
        $data = [];
        foreach ($customParams as $param) {
            if (!empty($param['name'])) {
                $value = $param['value'] ?? '';

                // Handle different parameter types
                switch ($param['type'] ?? 'static') {
                    case 'phone':
                        $value = $phone;
                        break;
                    case 'message':
                        $value = $message;
                        break;
                    case 'placeholder':
                        // Replace placeholders in custom placeholder values
                        $value = str_replace(['{phone}', '{message}'], [$phone, $message], $value);
                        break;
                    case 'static':
                    default:
                        // Use the value as-is
                        break;
                }

                $data[$param['name']] = $value;
            }
        }

        if ($httpMethod === 'GET') {
            return $this->makeHttpGetRequest($url, $data);
        } else {
            return $this->makeHttpRequest($url, $data);
        }
    }

    /**
     * Replace placeholders in parameter values
     */
    private function replacePlaceholders(array $data, string $phone, string $message): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = str_replace(['{phone}', '{message}'], [$phone, $message], $value);
        }
        return $data;
    }

    /**
     * Make HTTP GET request
     */
    private function makeHttpGetRequest(string $url, array $data): string
    {
        $queryString = http_build_query($data);
        $fullUrl = $url . '?' . $queryString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('HTTP GET request failed with code: ' . $httpCode . ', Response: ' . $response);
        }

        return $response;
    }

    /**
     * Format phone number with country code
     */
    private function formatPhoneNumber(string $phone, string $countryCode): string
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If phone starts with +, return as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // If phone starts with country code, add +
        if (str_starts_with($phone, $countryCode)) {
            return '+' . $phone;
        }

        // Otherwise, add country code
        return '+' . $countryCode . $phone;
    }

    /**
     * Make HTTP request
     */
    private function makeHttpRequest(string $url, array $data): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('HTTP request failed with code: ' . $httpCode . ', Response: ' . $response);
        }

        return $response;
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
