<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f6f7f9;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: #ffffff;
            padding: 24px;
        }

        .title {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .subtitle {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #d1d5db;
        }

        .content {
            padding: 24px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .table th,
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .table th {
            background: #f3f4f6;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .total {
            font-weight: 700;
            font-size: 16px;
        }

        .footer {
            padding: 16px 24px 24px;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
    @php
        $sym = $company['currency_symbol'];
        $pos = $company['currency_position'];
        $money = function ($amt) use ($sym, $pos) {
            $val = number_format((float) $amt, 2);
            return $pos === 'before' ? $sym . $val : $val . $sym;
        };
    @endphp
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1 class="title">Invoice #{{ $invoice->invoice_no }}</h1>
                <p class="subtitle">{{ $company['name'] }} •
                    {{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</p>
            </div>
            <div class="content">
                <div class="row" style="margin-bottom: 12px;">
                    <div>
                        <div class="muted">Billed To</div>
                        <div><strong>{{ $invoice->customer->name ?? 'Customer' }}</strong></div>
                        @if (!empty($invoice->customer->email))
                            <div class="muted">{{ $invoice->customer->email }}</div>
                        @endif
                        @if (!empty($invoice->customer->mobile))
                            <div class="muted">{{ $invoice->customer->mobile }}</div>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <div class="muted">From</div>
                        <div><strong>{{ $company['name'] }}</strong></div>
                        @if (!empty($company['email']))
                            <div class="muted">{{ $company['email'] }}</div>
                        @endif
                        @if (!empty($company['phone']))
                            <div class="muted">{{ $company['phone'] }}</div>
                        @endif
                        @if (!empty($company['address']))
                            <div class="muted">{{ $company['address'] }}</div>
                        @endif
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Rate</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td>{{ $item->medicine->name ?? 'Item' }}</td>
                                <td style="text-align:right;">{{ $item->quantity }}</td>
                                <td style="text-align:right;">{{ $money($item->rate) }}</td>
                                <td style="text-align:right;">{{ $money($item->total_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals aligned to the right for better email client support -->
                <div style="margin-top:16px; text-align:right;">
                    <div class="muted">Subtotal</div>
                    <div>
                        {{ $money(($invoice->total_amount ?? 0) - ($invoice->total_tax ?? 0) + ($invoice->total_discount ?? 0)) }}
                    </div>
                    <div class="muted" style="margin-top:6px;">Tax</div>
                    <div>{{ $money($invoice->total_tax ?? 0) }}</div>
                    <div class="muted" style="margin-top:6px;">Discount</div>
                    <div>{{ $money($invoice->total_discount ?? 0) }}</div>
                    <div class="total" style="margin-top:8px;">Total {{ $money($invoice->total_amount ?? 0) }}</div>
                    <div class="muted" style="margin-top:6px;">Paid</div>
                    <div>{{ $money($invoice->paid_amount ?? 0) }}</div>
                    <div class="muted" style="margin-top:6px;">Due</div>
                    <div>{{ $money($invoice->due_amount ?? 0) }}</div>
                </div>
            </div>
            <div class="footer">
                <p class="muted">If you have any questions about this invoice, simply reply to this email.</p>
            </div>
        </div>
        <p class="muted" style="text-align:center; margin-top: 12px;">Thank you for your business!</p>
    </div>
</body>

</html>
