<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\Platform\TenantRuntime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyLoginController extends Controller
{
    public function issue(Request $request, Company $company): RedirectResponse
    {
        if (! Hash::check((string) $request->input('password'), (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password did not match.']);
        }

        if (! $company->isActive() || ! TenantRuntime::databaseExists($company->database_name)) {
            return back()->withErrors(['login' => 'The pharmacy must be active and its database must exist.']);
        }

        $requestedUserId = (int) $request->input('user_id', 0);

        $adminId = TenantRuntime::runOn($company->database_name, function () use ($company, $requestedUserId) {
            if ($requestedUserId > 0) {
                $exists = User::query()->whereKey($requestedUserId)->value('id');
                if ($exists) {
                    return (int) $exists;
                }
            }

            $byEmail = $company->admin_email
                ? User::query()->where('email', $company->admin_email)->value('id')
                : null;

            return (int) ($byEmail ?: User::query()->orderBy('id')->value('id'));
        });

        if (! $adminId) {
            return back()->withErrors(['login' => 'No user was found in this pharmacy.']);
        }

        $token = Str::random(64);
        Cache::store('file')->put('company_login_as:'.$token, [
            'database' => $company->database_name,
            'user_id'  => $adminId,
        ], now()->addMinutes(2));

        return redirect()->away('https://'.$company->host().'/company-login/'.$token);
    }

    public function consume(string $token): RedirectResponse
    {
        $payload = Cache::store('file')->pull('company_login_as:'.$token);
        if (! is_array($payload)) {
            return redirect()->route('login')->withErrors(['email' => 'This pharmacy login link expired.']);
        }

        $database = (string) ($payload['database'] ?? '');
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($database === '' || $userId < 1 || ! TenantRuntime::databaseExists($database)) {
            return redirect()->route('login')->withErrors(['email' => 'The pharmacy login payload is invalid.']);
        }

        // Switch to the tenant DB for this request (tenant host middleware also does this).
        config(['database.connections.mysql.database' => $database]);
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');

        $user = User::query()->whereKey($userId)->first();
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'The pharmacy user was not found.']);
        }

        Auth::login($user);
        request()->session()->regenerate();
        request()->session()->put('auth_database', $database);

        return redirect()->route('dashboard');
    }
}
