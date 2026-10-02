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
        $companies = Company::query()->latest('id')->get();

        return Inertia::render('Platform/Dashboard', [
            'settings'   => $settings->all(),
            'pharmacies' => $companies->take(8)->map(fn (Company $company) => [
                'id'               => $company->id,
                'name'             => $company->name,
                'slug'             => $company->slug,
                'database_name'    => $company->database_name,
                'status'           => $company->status,
                'provision_status' => $company->provision_status,
            ])->values(),
            'baseDomain' => config('database.tenant.base_domain'),
            'metrics'    => [
                'pharmacies' => $companies->count(),
                'active'     => $companies->where('status', 'active')->where('provision_status', 'active')->count(),
                'locked'     => $companies->where('status', 'locked')->count(),
                'failed'     => $companies->where('provision_status', 'failed')->count(),
                'mrr'        => (float) PlatformSubscription::query()->where('status', 'active')->sum('amount'),
                'unpaid'     => (float) PlatformInvoice::query()->where('status', 'unpaid')->sum('amount'),
            ],
        ]);
    }

    public function billing(Request $request): Response
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $invoices = PlatformInvoice::query()->with('company:id,name,slug')
            ->when($from, fn ($q) => $q->whereDate('issued_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('issued_on', '<=', $to))
            ->orderByDesc('issued_on')
            ->get();

        return Inertia::render('Platform/Billing', [
            'from'     => $from,
            'to'       => $to,
            'invoices' => $invoices,
            'paid'     => (float) $invoices->where('status', 'paid')->sum('amount'),
            'unpaid'   => (float) $invoices->where('status', 'unpaid')->sum('amount'),
        ]);
    }
}
