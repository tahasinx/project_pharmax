<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Medicine;
use Illuminate\Http\Request;

class ExampleNotificationController extends Controller
{
    /**
     * Example: Send email notification
     */
    public function sendEmailExample(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = NotificationHelper::sendEmail(
            $request->email,
            $request->subject,
            $request->message
        );

        return response()->json($result);
    }

    /**
     * Example: Send SMS notification
     */
    public function sendSmsExample(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ]);

        $result = NotificationHelper::sendSms(
            $request->phone,
            $request->message
        );

        return response()->json($result);
    }

    /**
     * Example: Send notification to customer
     */
    public function notifyCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'type'        => 'required|in:email,sms,both',
            'message'     => 'required|string',
        ]);

        $customer = Customer::findByPublicId($request->customer_id);
        $results  = [];

        if ($request->type === 'email' || $request->type === 'both') {
            $emailResult = NotificationHelper::sendEmail(
                $customer->email,
                'Notification from PharmaCare',
                $request->message
            );
            $results['email'] = $emailResult;
        }

        if ($request->type === 'sms' || $request->type === 'both') {
            $smsResult = NotificationHelper::sendSms(
                $customer->phone,
                $request->message
            );
            $results['sms'] = $smsResult;
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent',
            'results' => $results,
        ]);
    }

    /**
     * Example: Send low stock alert
     */
    public function sendLowStockAlert(Request $request)
    {
        $request->validate([
            'medicine_id'   => 'required|exists:medicines,medicine_id',
            'current_stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
        ]);

        $medicine = Medicine::findByPublicId($request->medicine_id);

        $message = "Low Stock Alert: {$medicine->name} has only {$request->current_stock} units remaining. Minimum required: {$request->minimum_stock} units.";

        // Send to admin email (you can configure this)
        $result = NotificationHelper::sendEmail(
            'admin@pharmacare.com', // Configure this in settings
            'Low Stock Alert - '.$medicine->name,
            $message
        );

        return response()->json($result);
    }

    /**
     * Example: Send invoice notification
     */
    public function sendInvoiceNotification(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'type'       => 'required|in:email,sms',
        ]);

        $invoice = Invoice::with('customer')->find($request->invoice_id);

        $message = "Your invoice #{$invoice->invoice_no} for {$invoice->grand_total} has been generated. Thank you for your business!";

        if ($request->type === 'email') {
            $result = NotificationHelper::sendEmail(
                $invoice->customer->email,
                'Invoice #'.$invoice->invoice_no,
                $message
            );
        } else {
            $result = NotificationHelper::sendSms(
                $invoice->customer->phone,
                $message
            );
        }

        return response()->json($result);
    }
}
