<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Models\PlatformPlan;
use App\Models\PlatformSubscription;
use App\Services\Platform\PlatformSettingsStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function plans(): Response
    {
        return Inertia::render('Platform/Plans/Index', [
            'plans' => PlatformPlan::query()->withCount('subscriptions')->orderBy('name')->get(),
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:40|regex:/^[a-z0-9-]+$/|unique:platform_plans,code',
            'monthly_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:8',
        ]);
        PlatformPlan::query()->create($data + ['status' => 'active']);

        return back()->with('success', 'Plan saved.');
    }

    public function editPlan(PlatformPlan $plan): Response
    {
        return Inertia::render('Platform/Plans/Edit', ['plan' => $plan]);
    }

    public function updatePlan(Request $request, PlatformPlan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'monthly_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:8',
            'status' => 'required|in:active,archived',
        ]);
        $plan->update($data);

        return redirect()->route('platform.plans')->with('success', 'Plan updated.');
    }

    public function archivePlan(PlatformPlan $plan): RedirectResponse
    {
        $plan->update(['status' => $plan->status === 'active' ? 'archived' : 'active']);

        return back()->with('success', 'Plan updated.');
    }

    public function subscriptions(): Response
    {
        return Inertia::render('Platform/Subscriptions/Index', [
            'subscriptions' => PlatformSubscription::query()->with(['company:id,name,slug', 'plan:id,name,currency'])->latest('id')->get()->map(fn ($row) => [
                'id' => $row->id,
                'amount' => $row->amount,
                'starts_on' => optional($row->starts_on)->toDateString(),
                'ends_on' => optional($row->ends_on)->toDateString(),
                'platform_plan_id' => $row->platform_plan_id,
                'company' => $row->company,
                'plan' => $row->plan,
            ]),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'plans' => PlatformPlan::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'platform_plan_id' => 'required|exists:platform_plans,id',
            'starts_on' => 'required|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
        ]);
        $plan = PlatformPlan::query()->findOrFail($data['platform_plan_id']);
        PlatformSubscription::query()->create($data + [
            'status' => 'active',
            'amount' => $plan->monthly_amount,
        ]);

        return back()->with('success', 'Subscription saved.');
    }

    public function upgradeSubscription(Request $request, PlatformSubscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'platform_plan_id' => 'required|exists:platform_plans,id',
        ]);
        $plan = PlatformPlan::query()->findOrFail($data['platform_plan_id']);
        $subscription->update([
            'platform_plan_id' => $plan->id,
            'amount' => $plan->monthly_amount,
            'status' => 'active',
        ]);

        return back()->with('success', 'Subscription moved to '.$plan->name.'.');
    }

    public function invoiceSubscription(PlatformSubscription $subscription, PlatformSettingsStore $settings): RedirectResponse
    {
        PlatformInvoice::query()->create([
            'company_id' => $subscription->company_id,
            'platform_subscription_id' => $subscription->id,
            'number' => 'EP-'.now()->format('YmdHis'),
            'amount' => $subscription->amount,
            'currency' => $settings->all()['default_currency'] ?? 'BDT',
            'status' => 'unpaid',
            'issued_on' => now()->toDateString(),
        ]);

        return redirect()->route('platform.invoices')->with('success', 'Invoice raised from the subscription.');
    }

    public function updateExpiry(Request $request, PlatformSubscription $subscription): RedirectResponse
    {
        $data = $request->validate(['ends_on' => 'nullable|date']);
        $subscription->update(['ends_on' => $data['ends_on'] ?? null]);

        return back()->with('success', 'Expiry updated.');
    }

    public function invoices(PlatformSettingsStore $settings): Response
    {
        return Inertia::render('Platform/Invoices/Index', [
            'invoices' => PlatformInvoice::query()->with('company:id,name,slug')->latest('id')->get(),
            'subscriptions' => PlatformSubscription::query()->with('company:id,name')->where('status', 'active')->get(),
            'currency' => $settings->all()['default_currency'] ?? 'BDT',
        ]);
    }

    public function storeInvoice(Request $request, PlatformSettingsStore $settings): RedirectResponse
    {
        $data = $request->validate([
            'platform_subscription_id' => 'required|exists:platform_subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'issued_on' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        $subscription = PlatformSubscription::query()->findOrFail($data['platform_subscription_id']);
        PlatformInvoice::query()->create([
            'company_id' => $subscription->company_id,
            'platform_subscription_id' => $subscription->id,
            'number' => 'EP-'.now()->format('YmdHis'),
            'amount' => $data['amount'],
            'currency' => $settings->all()['default_currency'] ?? 'BDT',
            'status' => 'unpaid',
            'issued_on' => $data['issued_on'],
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Invoice created.');
    }

    public function markInvoice(PlatformInvoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            $invoice->update(['status' => 'unpaid', 'paid_at' => null]);
        } else {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return back()->with('success', 'Invoice updated.');
    }

    public function destroyInvoice(PlatformInvoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return back()->with('success', 'Invoice deleted.');
    }
}
