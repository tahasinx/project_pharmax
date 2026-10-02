<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public array $settings;

    public function __construct(Invoice $invoice, array $settings = [])
    {
        $this->invoice  = $invoice;
        $this->settings = $settings;
    }

    public function build()
    {
        $company = [
            'name'              => $this->settings['company_name'] ?? config('app.name', 'PharmaCare'),
            'email'             => $this->settings['company_email'] ?? null,
            'phone'             => $this->settings['company_phone'] ?? null,
            'address'           => $this->settings['company_address'] ?? null,
            'currency_symbol'   => $this->settings['currency_symbol'] ?? '$',
            'currency_position' => $this->settings['currency_position'] ?? 'before',
        ];

        $subject = 'Your Invoice #'.$this->invoice->invoice_no.' from '.$company['name'];

        return $this->subject($subject)
            ->view('emails.invoice-created')
            ->with([
                'invoice' => $this->invoice,
                'company' => $company,
            ]);
    }
}
