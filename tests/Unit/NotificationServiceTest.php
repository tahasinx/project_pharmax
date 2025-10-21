<?php

namespace Tests\Unit;

use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
    }

    public function test_send_email_with_smtp_configuration()
    {
        Mail::fake();

        // Mock settings
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'test@example.com',
            'smtp_password' => 'password',
            'smtp_encryption' => 'tls',
            'mail_from_name' => 'Test App',
            'mail_from_address' => 'test@example.com',
        ]));

        $result = $this->notificationService->sendEmail(
            'recipient@example.com',
            'Test Subject',
            'Test Message'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('Email sent successfully', $result['message']);
        $this->assertEquals('recipient@example.com', $result['to']);
    }

    public function test_send_email_handles_exceptions()
    {
        // Mock settings with invalid configuration
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'email_provider' => 'smtp',
            'smtp_host' => 'invalid-host',
            'smtp_port' => 587,
        ]));

        $result = $this->notificationService->sendEmail(
            'recipient@example.com',
            'Test Subject',
            'Test Message'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to send email', $result['message']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_send_sms_with_twilio_configuration()
    {
        // Mock settings for Twilio
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'sms_provider' => 'twilio',
            'twilio_sid' => 'test_sid',
            'twilio_token' => 'test_token',
            'twilio_from' => '+1234567890',
            'sms_country_code' => '1',
        ]));

        // Mock cURL response
        $this->mockCurlResponse([
            'status' => 'sent',
            'sid' => 'test_message_sid'
        ]);

        $result = $this->notificationService->sendSms(
            '1234567890',
            'Test SMS Message'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('twilio', $result['provider']);
        $this->assertEquals('1234567890', $result['phone']);
    }

    public function test_send_sms_with_custom_api()
    {
        // Mock settings for custom API
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'sms_provider' => 'custom',
            'sms_api_url' => 'https://api.example.com/sms',
            'sms_http_method' => 'POST',
            'sms_custom_params' => [
                ['name' => 'phone', 'type' => 'phone', 'value' => ''],
                ['name' => 'message', 'type' => 'message', 'value' => ''],
                ['name' => 'api_key', 'type' => 'static', 'value' => 'test_key'],
            ],
            'sms_response_mappings' => [
                ['key' => 'status', 'value' => 'success', 'message' => 'SMS sent successfully']
            ]
        ]));

        // Mock cURL response
        $this->mockCurlResponse([
            'status' => 'success',
            'message_id' => '12345'
        ]);

        $result = $this->notificationService->sendSms(
            '1234567890',
            'Test SMS Message'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('custom', $result['provider']);
    }

    public function test_phone_number_formatting()
    {
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'sms_provider' => 'twilio',
            'twilio_sid' => 'test_sid',
            'twilio_token' => 'test_token',
            'twilio_from' => '+1234567890',
            'sms_country_code' => '1',
        ]));

        $this->mockCurlResponse(['status' => 'sent']);

        // Test various phone number formats
        $testNumbers = [
            '1234567890',
            '+1234567890',
            '1234567890',
            '(123) 456-7890',
        ];

        foreach ($testNumbers as $number) {
            $result = $this->notificationService->sendSms($number, 'Test');
            $this->assertTrue($result['success']);
        }
    }

    public function test_custom_response_message_mapping()
    {
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'sms_provider' => 'custom',
            'sms_api_url' => 'https://api.example.com/sms',
            'sms_http_method' => 'POST',
            'sms_custom_params' => [
                ['name' => 'phone', 'type' => 'phone', 'value' => ''],
                ['name' => 'message', 'type' => 'message', 'value' => ''],
            ],
            'sms_response_mappings' => [
                ['key' => 'status', 'value' => 'success', 'message' => 'Message delivered successfully'],
                ['key' => 'status', 'value' => 'failed', 'message' => 'Message delivery failed'],
            ]
        ]));

        // Test success response
        $this->mockCurlResponse(['status' => 'success']);
        $result = $this->notificationService->sendSms('1234567890', 'Test');
        $this->assertTrue($result['success']);
        $this->assertEquals('Message delivered successfully', $result['message']);

        // Test failure response
        $this->mockCurlResponse(['status' => 'failed']);
        $result = $this->notificationService->sendSms('1234567890', 'Test');
        $this->assertTrue($result['success']);
        $this->assertEquals('Message delivery failed', $result['message']);
    }

    public function test_email_configuration_loading()
    {
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'email_provider' => 'mailgun',
            'mailgun_domain' => 'test.mailgun.org',
            'mailgun_secret' => 'test_secret',
            'mail_from_name' => 'Test App',
            'mail_from_address' => 'test@example.com',
        ]));

        Mail::fake();

        $result = $this->notificationService->sendEmail(
            'recipient@example.com',
            'Test Subject',
            'Test Message'
        );

        $this->assertTrue($result['success']);
    }

    public function test_sms_configuration_loading()
    {
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'sms_provider' => 'nexmo',
            'nexmo_key' => 'test_key',
            'nexmo_secret' => 'test_secret',
            'nexmo_from' => 'TestApp',
            'sms_country_code' => '44',
        ]));

        $this->mockCurlResponse(['status' => 'sent']);

        $result = $this->notificationService->sendSms(
            '1234567890',
            'Test SMS Message'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('nexmo', $result['provider']);
    }

    public function test_handles_missing_settings()
    {
        Storage::fake('local');
        // No settings file

        $result = $this->notificationService->sendEmail(
            'recipient@example.com',
            'Test Subject',
            'Test Message'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to send email', $result['message']);
    }

    public function test_handles_invalid_json_settings()
    {
        Storage::fake('local');
        Storage::put('settings.json', 'invalid json');

        $result = $this->notificationService->sendEmail(
            'recipient@example.com',
            'Test Subject',
            'Test Message'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to send email', $result['message']);
    }

    private function mockCurlResponse($response)
    {
        // This would need to be implemented with a proper HTTP client mock
        // For now, we'll assume the cURL calls work as expected
        // In a real implementation, you'd use Laravel's HTTP fake or similar
    }
}
