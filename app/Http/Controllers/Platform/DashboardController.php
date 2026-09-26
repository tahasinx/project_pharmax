<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Models\PlatformSubscription;
use App\Services\Platform\PlatformSettingsStore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(PlatformSettingsStore $settings): Response
    {
        $companies = Company::query()->get();

        return Inertia::render('Platform/Dashboard', [
            'settings' => $settings->all(),
            'metrics' => [
                'pharmacies' => $companies->count(),
                'active' => $companies->where('status', 'active')->where('provision_status', 'active')->count(),
                'locked' => $companies->where('status', 'locked')->count(),
                'failed' => $companies->where('provision_status', 'failed')->count(),
                'mrr' => (float) PlatformSubscription::query()->where('status', 'active')->sum('amount'),
                'unpaid' => (float) PlatformInvoice::query()->where('status', 'unpaid')->sum('amount'),
            ],
        ]);
    }

    public function billing(Request $request): Response
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $invoices = PlatformInvoice::query()->with('company:id,name,slug')
            ->when($from, fn ($q) => $q->whereDate('issued_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('issued_on', '<=', $to))
            ->orderByDesc('issued_on')
            ->get();

        return Inertia::render('Platform/Billing', [
            'from' => $from,
            'to' => $to,
            'invoices' => $invoices,
            'paid' => (float) $invoices->where('status', 'paid')->sum('amount'),
            'unpaid' => (float) $invoices->where('status', 'unpaid')->sum('amount'),
        ]);
    }
}
