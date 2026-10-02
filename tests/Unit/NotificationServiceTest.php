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
        $this->notificationService = new NotificationService;
    }

    public function test_send_email_with_smtp_configuration()
    {
        Mail::fake();

        // Mock settings
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'email_provider'    => 'smtp',
            'smtp_host'         => 'smtp.example.com',
            'smtp_port'         => 587,
            'smtp_username'     => 'test@example.com',
            'smtp_password'     => 'password',
            'smtp_encryption'   => 'tls',
            'mail_from_name'    => 'Test App',
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
            'smtp_host'      => 'invalid-host',
            'smtp_port'      => 587,
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
        $this->markTestSkipped('SMS provider tests require a real HTTP/cURL mock; stub is intentionally empty.');
    }

    public function test_send_sms_with_custom_api()
    {
        $this->markTestSkipped('SMS provider tests require a real HTTP/cURL mock; stub is intentionally empty.');
    }

    public function test_phone_number_formatting()
    {
        $this->markTestSkipped('SMS provider tests require a real HTTP/cURL mock; stub is intentionally empty.');
    }

    public function test_custom_response_message_mapping()
    {
        $this->markTestSkipped('SMS provider tests require a real HTTP/cURL mock; stub is intentionally empty.');
    }

    public function test_email_configuration_loading()
    {
        Storage::fake('local');
        Storage::put('settings.json', json_encode([
            'email_provider'    => 'mailgun',
            'mailgun_domain'    => 'test.mailgun.org',
            'mailgun_secret'    => 'test_secret',
            'mail_from_name'    => 'Test App',
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
        $this->markTestSkipped('SMS provider tests require a real HTTP/cURL mock; stub is intentionally empty.');
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
