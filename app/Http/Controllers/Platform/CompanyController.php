<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Platform\CompanyProvisioner;
use App\Services\Platform\HostProvisioner;
use App\Services\Platform\SchemaCompare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CompanyController extends Controller
{
    /** @var list<string> */
    private array $reserved = [
        'admin', 'adminx', 'www', 'staging', 'stagging', 'api', 'app', 'mail', 'platform', 'central',
    ];

    public function index(): Response
    {
        return Inertia::render('Platform/Companies/Index', [
            'companies' => Company::query()->orderBy('name')->get(),
            'baseDomain' => config('database.tenant.base_domain'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Platform/Companies/Create', [
            'prefix' => config('database.tenant.db_prefix'),
            'baseDomain' => config('database.tenant.base_domain'),
        ]);
    }

    public function store(Request $request, CompanyProvisioner $provisioner): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:32|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
        ]);

        if (in_array($data['slug'], $this->reserved, true)) {
            return back()->withErrors(['slug' => 'That address is reserved.'])->withInput();
        }

        if (Company::query()->where('slug', $data['slug'])->exists()) {
            return back()->withErrors(['slug' => 'That address is already used.'])->withInput();
        }

        $company = Company::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'database_name' => $provisioner->databaseName($data['slug']),
            'status' => 'locked',
            'provision_status' => 'pending',
            'admin_email' => 'admin@pharma.com',
        ]);

        $provisioner->startInBackground($company);

        return redirect()->route('platform.companies.provision', $company)
            ->with('success', 'Provisioning started.');
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Platform/Companies/Edit', ['company' => $company]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'admin_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
            'address' => 'nullable|string|max:2000',
            'status' => 'required|in:active,locked',
        ]);
        $company->fill($data)->save();

        return redirect()->route('platform.companies.show', $company)->with('success', 'Pharmacy updated.');
    }

    public function destroy(Request $request, Company $company, HostProvisioner $hosts, CompanyProvisioner $provisioner): RedirectResponse
    {
        $data = $request->validate([
            'password' => 'required|string',
            'confirm_text' => 'required|in:DELETE',
        ]);
        if (! Hash::check($data['password'], (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password did not match.']);
        }

        $notes = [];
        if ($hosts->enabled()) {
            try {
                $removed = $hosts->remove($company->slug);
                if (! $removed['ok']) {
                    $notes[] = 'Hostname remove failed.';
                }
            } catch (RuntimeException $e) {
                $notes[] = $e->getMessage();
            }
            try {
                if ($company->database_name && $company->database_name === $provisioner->databaseName($company->slug)) {
                    $hosts->dropDatabase($company->slug);
                }
            } catch (RuntimeException $e) {
                $notes[] = $e->getMessage();
            }
        }
        $company->delete();

        return redirect()->route('platform.companies.index')
            ->with('success', 'Pharmacy removed.'.($notes === [] ? '' : ' '.implode(' ', $notes)));
    }

    public function show(Company $company, SchemaCompare $schema): Response
    {
        return Inertia::render('Platform/Companies/Show', [
            'company' => $company,
            'host' => 'https://'.$company->host(),
            'subscription' => $company->subscriptions()->with('plan')->latest('id')->first(),
            'schema' => $schema->companies() ? collect($schema->companies())->firstWhere('company_id', $company->id) : null,
        ]);
    }

    public function provisionPage(Company $company, HostProvisioner $hosts): Response
    {
        return Inertia::render('Platform/Companies/Provision', [
            'company' => $company,
            'host' => 'https://'.$company->host(),
            'hostEnabled' => $hosts->enabled(),
        ]);
    }

    public function provisionLog(Company $company): JsonResponse
    {
        $company->refresh();

        return response()->json([
            'provision_status' => $company->provision_status,
            'provision_step' => $company->provision_step,
            'provision_error' => $company->provision_error,
            'vhost_status' => $company->vhost_status,
            'ssl_status' => $company->ssl_status,
            'log' => $company->provision_log ?? [],
            'host' => 'https://'.$company->host(),
        ]);
    }

    public function provision(Company $company, CompanyProvisioner $provisioner): RedirectResponse
    {
        $provisioner->startInBackground($company);

        return redirect()->route('platform.companies.provision', $company)->with('success', 'Provisioning started.');
    }

    public function provisionRetry(Company $company, CompanyProvisioner $provisioner): RedirectResponse
    {
        if (! in_array($company->provision_status, ['failed', 'degraded'], true)) {
            return back()->with('error', 'Retry is available after a failed or partial provision.');
        }
        $hostOnly = $company->provision_status === 'degraded';
        $provisioner->startInBackground($company, $hostOnly);

        return redirect()->route('platform.companies.provision', $company)->with('success', 'Retry started.');
    }

    public function lock(Request $request, Company $company): RedirectResponse
    {
        if (! Hash::check((string) $request->input('password'), (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password did not match.']);
        }

        $company->status = $company->status === 'active' ? 'locked' : 'active';
        $company->save();

        return back()->with('success', $company->status === 'locked'
            ? $company->name.' is locked.'
            : $company->name.' is unlocked.');
    }
}
