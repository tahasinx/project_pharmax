<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class NotificationService
{
    /**
     * Send email using configured settings
     */
    public function sendEmail(string $to, string $subject, string $message, array $options = []): array
    {
        try {
            // Load email configuration from settings
            $emailConfig = $this->getEmailConfig();

            // Configure mail settings temporarily
            $this->configureMailSettings($emailConfig);

            // Send email
            Mail::raw($message, function ($mail) use ($to, $subject, $emailConfig) {
                $mail->to($to)
                    ->subject($subject);

                // Set from address if configured
                if (! empty($emailConfig['mail_from_address'])) {
                    $mail->from($emailConfig['mail_from_address'], $emailConfig['mail_from_name'] ?? '');
                }
            });

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'to'      => $to,
                'subject' => $subject,
            ];
        } catch (\Exception $e) {
            Log::error('Email sending failed: '.$e->getMessage(), [
                'to'      => $to,
                'subject' => $subject,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS using configured settings
     */
    public function sendSms(string $phone, string $message, array $options = []): array
    {
        try {
            // Load SMS configuration from settings
            $smsConfig = $this->getSmsConfig();

            if (empty($smsConfig['sms_provider'])) {
                throw new \Exception('SMS provider not configured');
            }

            // Send SMS based on provider
            $response = $this->sendSmsByProvider($phone, $message, $smsConfig);

            // Check for custom response message
            $customMessage = $this->getCustomResponseMessage($response, $smsConfig);

            return [
                'success'        => true,
                'message'        => $customMessage ?: 'SMS sent successfully',
                'phone'          => $phone,
                'provider'       => $smsConfig['sms_provider'],
                'raw_response'   => $response,
                'custom_message' => $customMessage,
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed: '.$e->getMessage(), [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: '.$e->getMessage(),
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS by provider
     */
    private function sendSmsByProvider(string $phone, string $message, array $config): string
    {
        switch ($config['sms_provider']) {
            case 'twilio':
                return $this->sendTwilioSms($phone, $message, $config);
            case 'nexmo':
                return $this->sendNexmoSms($phone, $message, $config);
            case 'custom':
                return $this->sendCustomSms($phone, $message, $config);
            default:
                throw new \Exception('Unsupported SMS provider: '.$config['sms_provider']);
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendTwilioSms(string $phone, string $message, array $config): string
    {
        $sid   = $config['twilio_sid'] ?? '';
        $token = $config['twilio_token'] ?? '';
        $from  = $config['twilio_from'] ?? '';

        if (empty($sid) || empty($token) || empty($from)) {
            throw new \Exception('Twilio configuration incomplete');
        }

        $formattedPhone = $this->formatPhoneNumber($phone, $config['sms_country_code'] ?? '1');

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $data = [
            'From' => $from,
            'To'   => $formattedPhone,
            'Body' => $message,
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
            throw new \Exception('Twilio API error: '.$response);
        }

        return $response;
    }

    /**
     * Send SMS via Nexmo (Vonage)
     */
    private function sendNexmoSms(string $phone, string $message, array $config): string
    {
        $apiKey    = $config['nexmo_key'] ?? '';
        $apiSecret = $config['nexmo_secret'] ?? '';
        $from      = $config['nexmo_from'] ?? '';

        if (empty($apiKey) || empty($apiSecret) || empty($from)) {
            throw new \Exception('Nexmo configuration incomplete');
        }

        $formattedPhone = $this->formatPhoneNumber($phone, $config['sms_country_code'] ?? '44');

        $url = 'https://rest.nexmo.com/sms/json';

        $data = [
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret,
            'to'         => $formattedPhone,
            'from'       => $from,
            'text'       => $message,
        ];

        return $this->makeHttpRequest($url, $data);
    }

    /**
     * Send SMS via Custom API
     */
    private function sendCustomSms(string $phone, string $message, array $config): string
    {
        $url          = $config['sms_api_url'] ?? '';
        $httpMethod   = $config['sms_http_method'] ?? 'POST';
        $customParams = $config['sms_custom_params'] ?? [];

        if (empty($url)) {
            throw new \Exception('Custom SMS API URL not configured');
        }

        // Build data array from custom parameters
        $data = [];
        foreach ($customParams as $param) {
            if (! empty($param['name'])) {
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
     * Get custom response message based on mappings
     */
    private function getCustomResponseMessage(string $remoteResponse, array $smsConfig): ?string
    {
        $mappings = $smsConfig['sms_response_mappings'] ?? [];

        if (empty($mappings) || empty($remoteResponse)) {
            return null;
        }

        // Convert response to string for comparison
        $responseStr = $remoteResponse;

        foreach ($mappings as $mapping) {
            if (empty($mapping['value']) || empty($mapping['message'])) {
                continue;
            }

            // Case 1: Empty key - match entire response value
            if (empty($mapping['key']) || trim($mapping['key']) === '') {
                if ($responseStr === $mapping['value'] || str_contains($responseStr, $mapping['value'])) {
                    return $mapping['message'];
                }
            }
            // Case 2: Has key - try to parse as JSON and match specific field
            else {
                try {
                    $parsed = json_decode($remoteResponse, true);

                    // Check if the key exists and value matches
                    if (isset($parsed[$mapping['key']])) {
                        $responseValue = $parsed[$mapping['key']];
                        if (
                            $responseValue === $mapping['value'] ||
                            $responseValue === (string) $mapping['value'] ||
                            (string) $responseValue === $mapping['value']
                        ) {
                            return $mapping['message'];
                        }
                    }
                } catch (\Exception $e) {
                    // If JSON parsing fails, try string matching with key
                    $keyPattern = '/"'.preg_quote($mapping['key'], '/').'"\\s*:\\s*"'.preg_quote($mapping['value'], '/').'"/i';
                    if (preg_match($keyPattern, $responseStr)) {
                        return $mapping['message'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get email configuration from settings
     */
    private function getEmailConfig(): array
    {
        try {
            $settings = json_decode(Storage::get('settings.json'), true) ?? [];

            return [
                'email_provider'    => $settings['email_provider'] ?? 'smtp',
                'smtp_host'         => $settings['smtp_host'] ?? '',
                'smtp_port'         => $settings['smtp_port'] ?? 587,
                'smtp_username'     => $settings['smtp_username'] ?? '',
                'smtp_password'     => $settings['smtp_password'] ?? '',
                'smtp_encryption'   => $settings['smtp_encryption'] ?? 'tls',
                'mailgun_domain'    => $settings['mailgun_domain'] ?? '',
                'mailgun_secret'    => $settings['mailgun_secret'] ?? '',
                'ses_key'           => $settings['ses_key'] ?? '',
                'ses_secret'        => $settings['ses_secret'] ?? '',
                'ses_region'        => $settings['ses_region'] ?? 'us-east-1',
                'mail_from_name'    => $settings['mail_from_name'] ?? '',
                'mail_from_address' => $settings['mail_from_address'] ?? '',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get SMS configuration from settings
     */
    private function getSmsConfig(): array
    {
        try {
            $settings = json_decode(Storage::get('settings.json'), true) ?? [];

            return [
                'sms_provider'          => $settings['sms_provider'] ?? 'custom',
                'sms_api_url'           => $settings['sms_api_url'] ?? '',
                'sms_http_method'       => $settings['sms_http_method'] ?? 'POST',
                'sms_custom_params'     => $settings['sms_custom_params'] ?? [],
                'sms_response_mappings' => $settings['sms_response_mappings'] ?? [],
                'sms_country_code'      => $settings['sms_country_code'] ?? '880',
                'twilio_sid'            => $settings['twilio_sid'] ?? '',
                'twilio_token'          => $settings['twilio_token'] ?? '',
                'twilio_from'           => $settings['twilio_from'] ?? '',
                'nexmo_key'             => $settings['nexmo_key'] ?? '',
                'nexmo_secret'          => $settings['nexmo_secret'] ?? '',
                'nexmo_from'            => $settings['nexmo_from'] ?? '',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Configure mail settings temporarily
     */
    private function configureMailSettings(array $emailConfig): void
    {
        config([
            'mail.default'                 => $emailConfig['email_provider'],
            'mail.mailers.smtp.host'       => $emailConfig['smtp_host'] ?? '',
            'mail.mailers.smtp.port'       => $emailConfig['smtp_port'] ?? 587,
            'mail.mailers.smtp.username'   => $emailConfig['smtp_username'] ?? '',
            'mail.mailers.smtp.password'   => $emailConfig['smtp_password'] ?? '',
            'mail.mailers.smtp.encryption' => $emailConfig['smtp_encryption'] ?? 'tls',
            'services.mailgun.domain'      => $emailConfig['mailgun_domain'] ?? '',
            'services.mailgun.secret'      => $emailConfig['mailgun_secret'] ?? '',
            'services.ses.key'             => $emailConfig['ses_key'] ?? '',
            'services.ses.secret'          => $emailConfig['ses_secret'] ?? '',
            'services.ses.region'          => $emailConfig['ses_region'] ?? 'us-east-1',
        ]);
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
            return '+'.$phone;
        }

        // Otherwise, add country code
        return '+'.$countryCode.$phone;
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
            throw new \Exception('HTTP request failed with code: '.$httpCode.', Response: '.$response);
        }

        return $response;
    }

    /**
     * Make HTTP GET request
     */
    private function makeHttpGetRequest(string $url, array $data): string
    {
        $queryString = http_build_query($data);
        $fullUrl     = $url.'?'.$queryString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('HTTP GET request failed with code: '.$httpCode.', Response: '.$response);
        }

        return $response;
    }
}
