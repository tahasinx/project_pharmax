# Notification System Usage Guide

This document explains how to use the notification system for sending emails and SMS messages throughout the PharmaCare application.

## Quick Start

### 1. Send Email
```php
use App\Helpers\NotificationHelper;

$result = NotificationHelper::sendEmail(
    'customer@example.com',
    'Welcome to PharmaCare',
    'Thank you for choosing PharmaCare!'
);

if ($result['success']) {
    echo "Email sent successfully: " . $result['message'];
} else {
    echo "Email failed: " . $result['message'];
}
```

### 2. Send SMS
```php
use App\Helpers\NotificationHelper;

$result = NotificationHelper::sendSms(
    '+8801612345678',
    'Your order has been confirmed!'
);

if ($result['success']) {
    echo "SMS sent successfully: " . $result['message'];
} else {
    echo "SMS failed: " . $result['message'];
}
```

## Function Signatures

### NotificationHelper::sendEmail()
```php
public static function sendEmail(
    string $to,           // Email address
    string $subject,      // Email subject
    string $message,      // Email message
    array $options = []   // Additional options (future use)
): array
```

### NotificationHelper::sendSms()
```php
public static function sendSms(
    string $phone,        // Phone number
    string $message,      // SMS message
    array $options = []   // Additional options (future use)
): array
```

## Return Format

Both functions return an array with the following structure:

### Success Response
```php
[
    'success' => true,
    'message' => 'Email sent successfully', // or custom message from response mapping
    'to' => 'customer@example.com',        // For email
    'phone' => '+8801612345678',           // For SMS
    'provider' => 'custom',                // For SMS
    'raw_response' => '...',               // For SMS - raw API response
    'custom_message' => '...'              // For SMS - custom mapped message
]
```

### Error Response
```php
[
    'success' => false,
    'message' => 'Failed to send email: Error details',
    'error' => 'Detailed error message'
]
```

## Usage Examples

### 1. In Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        // Create order logic...
        
        // Notify customer
        $emailResult = NotificationHelper::sendEmail(
            $customer->email,
            'Order Confirmation',
            "Your order #{$order->id} has been placed successfully!"
        );
        
        $smsResult = NotificationHelper::sendSms(
            $customer->phone,
            "Order #{$order->id} confirmed! Total: {$order->total}"
        );
        
        return response()->json([
            'order' => $order,
            'notifications' => [
                'email' => $emailResult,
                'sms' => $smsResult
            ]
        ]);
    }
}
```

### 2. In Models (Events)

```php
<?php

namespace App\Models;

use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected static function booted()
    {
        static::created(function ($invoice) {
            // Send invoice notification
            NotificationHelper::sendEmail(
                $invoice->customer->email,
                'New Invoice Generated',
                "Invoice #{$invoice->invoice_no} has been generated for {$invoice->grand_total}"
            );
        });
    }
}
```

### 3. In Jobs (Queue)

```php
<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $medicine;
    protected $currentStock;

    public function handle()
    {
        $message = "Low Stock Alert: {$this->medicine->name} has only {$this->currentStock} units remaining.";
        
        NotificationHelper::sendEmail(
            'admin@pharmacare.com',
            'Low Stock Alert',
            $message
        );
    }
}
```

### 4. In Commands (Artisan)

```php
<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use Illuminate\Console\Command;

class SendDailyReport extends Command
{
    protected $signature = 'report:daily';
    protected $description = 'Send daily sales report';

    public function handle()
    {
        $report = $this->generateReport();
        
        NotificationHelper::sendEmail(
            'manager@pharmacare.com',
            'Daily Sales Report',
            $report
        );
        
        $this->info('Daily report sent successfully!');
    }
}
```

## Configuration

The notification system automatically uses the email and SMS configuration from the Settings page:

### Email Configuration
- Provider (SMTP, Mailgun, SES)
- SMTP settings (host, port, username, password)
- From name and address

### SMS Configuration
- Provider (Twilio, Nexmo, Custom API)
- Custom API settings (URL, parameters, response mappings)
- Response message mappings for user-friendly notifications

## Error Handling

Always check the `success` field in the response:

```php
$result = NotificationHelper::sendEmail($to, $subject, $message);

if (!$result['success']) {
    // Log error or show user message
    Log::error('Email sending failed', $result);
    
    // Or show user-friendly message
    return back()->with('error', 'Failed to send notification. Please try again.');
}
```

## Best Practices

1. **Always check success status** before proceeding
2. **Log errors** for debugging purposes
3. **Use appropriate notification type** (email for detailed info, SMS for urgent alerts)
4. **Keep messages concise** especially for SMS
5. **Test notifications** after configuration changes
6. **Handle failures gracefully** - don't break user experience

## Future Enhancements

- Template system for emails and SMS
- Queue support for bulk notifications
- Notification preferences per customer
- Delivery status tracking
- Notification history/logs
