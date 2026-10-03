<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\GitHubDeployService;
use App\Services\Platform\PlatformSettingsStore;
use App\Services\Platform\SchemaCompare;
use App\Services\Platform\TenantBackup;
use App\Support\StagingDeployHost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OperationsController extends Controller
{
    public function settings(PlatformSettingsStore $settings): Response
    {
        return Inertia::render('Platform/Settings', [
            'settings' => $settings->all(),
            'tenancy'  => $settings->tenancySnapshot(),
        ]);
    }

    public function updateSettings(Request $request, PlatformSettingsStore $settings): RedirectResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'tagline'            => 'nullable|string|max:255',
            'support_email'      => 'nullable|email|max:255',
            'support_phone'      => 'nullable|string|max:64',
            'address'            => 'nullable|string|max:2000',
            'default_currency'   => 'required|string|max:8',
            'invoice_footer'     => 'nullable|string|max:2000',
            'theme_primary'      => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_shape'        => 'required|in:default,rounded,flat',
            'theme_font_family'  => 'required|string|max:60',
            'theme_font_href'    => 'nullable|string|max:500',
            'theme_font_size'    => 'required|integer|min:12|max:22',
            'theme_font_weight'  => 'required|integer|in:300,400,500,600,700,800,900',
            'email_enabled'      => 'nullable|boolean',
            'email_host'         => 'nullable|string|max:255',
            'email_port'         => 'nullable|integer|min:1|max:65535',
            'email_encryption'   => 'nullable|in:tls,ssl,',
            'email_username'     => 'nullable|string|max:255',
            'email_password'     => 'nullable|string|max:255',
            'email_from_address' => 'nullable|email|max:255',
            'email_from_name'    => 'nullable|string|max:255',
            'logo'               => 'nullable|image|max:2048',
            'favicon'            => 'nullable|image|max:1024',
        ]);
        $data['theme_font_href']  = $settings->stylesheet((string) ($data['theme_font_href'] ?? ''));
        $data['email_enabled']    = $request->boolean('email_enabled');
        $data['email_encryption'] = $data['email_encryption'] ?: 'tls';
        unset($data['logo'], $data['favicon']);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brand', 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('brand', 'public');
        }
        $settings->save($data);

        return back()->with('success', 'Platform settings saved.');
    }

    public function testEmail(Request $request, PlatformSettingsStore $settings): RedirectResponse
    {
        $data = $request->validate([
            'email_test_to' => 'required|email|max:255',
        ]);
        $mail = $settings->mailer();
        if (! $mail['enabled'] || $mail['host'] === '' || $mail['from_address'] === '') {
            return back()->with('error', 'Turn on outbound email, save the SMTP host, and set a from address before sending a test.');
        }

        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.transport'  => 'smtp',
            'mail.mailers.smtp.host'       => $mail['host'],
            'mail.mailers.smtp.port'       => $mail['port'] ?: 587,
            'mail.mailers.smtp.encryption' => $mail['encryption'] ?: null,
            'mail.mailers.smtp.username'   => $mail['username'],
            'mail.mailers.smtp.password'   => $mail['password'],
            'mail.from.address'            => $mail['from_address'],
            'mail.from.name'               => $mail['from_name'] ?: $settings->all()['name'],
        ]);

        try {
            Mail::purge('smtp');
            Mail::raw('This is a test from the Epharma platform mailer.', function ($message) use ($data) {
                $message->to($data['email_test_to'])->subject('Epharma mail test');
            });
        } catch (Throwable $e) {
            return back()->with('error', 'The test did not send. '.$e->getMessage());
        }

        return back()->with('success', 'Test email sent to '.$data['email_test_to'].'.');
    }

    public function commands(): Response
    {
        $catalog = collect(Artisan::all())
            ->map(fn ($command, $name) => [
                'name'        => (string) $name,
                'description' => trim((string) $command->getDescription()) ?: 'No description.',
            ])
            ->sortBy('name')
            ->values();

        return Inertia::render('Platform/Commands', [
            'catalog'  => $catalog,
            'output'   => session('artisan_output'),
            'ran'      => session('artisan_command'),
            'exitCode' => session('artisan_exit'),
        ]);
    }

    public function runCommand(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password'     => 'required|string',
            'command_line' => 'required|string|max:500',
        ]);
        if (! Hash::check($data['password'], (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password did not match.'])->withInput($request->except('password'));
        }

        try {
            [$name, $parameters] = $this->parse($data['command_line']);
            @set_time_limit(180);
            $code   = Artisan::call($name, $parameters);
            $output = trim(Artisan::output()) ?: ($code === 0 ? 'Command finished with no output.' : 'Command failed with no output.');
        } catch (InvalidArgumentException|CommandNotFoundException $e) {
            return back()->withInput()->withErrors(['command_line' => $e->getMessage()]);
        } catch (Throwable $e) {
            return back()->withInput()
                ->with('artisan_command', $data['command_line'])
                ->with('artisan_exit', 1)
                ->with('artisan_output', $e->getMessage());
        }

        return back()
            ->with('artisan_command', $data['command_line'])
            ->with('artisan_exit', $code)
            ->with('artisan_output', $output);
    }

    public function schema(SchemaCompare $compare): Response
    {
        $rows = $compare->companies();

        return Inertia::render('Platform/Schema', [
            'rows'    => $rows,
            'central' => $compare->central(),
            'summary' => [
                'total'        => count($rows),
                'in_sync'      => collect($rows)->where('status', 'in_sync')->count(),
                'needs_update' => collect($rows)->where('status', 'needs_update')->count(),
                'db_missing'   => collect($rows)->where('status', 'db_missing')->count(),
            ],
        ]);
    }

    public function upgradeSchema(Request $request, SchemaCompare $compare): RedirectResponse
    {
        $request->validate([
            'password'   => 'required|string',
            'company_id' => 'nullable|integer',
            'central'    => 'nullable|boolean',
            'all'        => 'nullable|boolean',
        ]);
        if (! Hash::check((string) $request->input('password'), (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password did not match.']);
        }

        try {
            if ($request->boolean('central')) {
                $compare->upgradeCentral();

                return back()->with('success', 'Central registry migrated.');
            }
            if ($request->boolean('all')) {
                foreach (Company::query()->get() as $company) {
                    if ($company->database_name) {
                        $compare->upgradeCompany($company);
                    }
                }

                return back()->with('success', 'Every pharmacy database was migrated.');
            }
            $company = Company::findByPublicIdOrFail($request->input('company_id'));
            $compare->upgradeCompany($company);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Schema updated.');
    }

    public function backups(TenantBackup $backups): Response
    {
        $rows = Company::query()->orderBy('name')->get()->map(function (Company $company) use ($backups) {
            $files = $company->database_name ? $backups->listFor($company) : [];

            return [
                'id'    => $company->id,
                'name'  => $company->name,
                'files' => $files,
            ];
        });

        return Inertia::render('Platform/Backups', ['rows' => $rows]);
    }

    public function storeBackup(Company $company, TenantBackup $backups): RedirectResponse
    {
        try {
            $file = $backups->create($company);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Backup '.$file['name'].' created.');
    }

    public function downloadBackup(Company $company, string $filename, TenantBackup $backups): BinaryFileResponse
    {
        try {
            return response()->download($backups->path($company, $filename));
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function destroyBackup(Company $company, string $filename, TenantBackup $backups): RedirectResponse
    {
        try {
            $backups->delete($company, $filename);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Backup deleted.');
    }

    public function deploy(GitHubDeployService $github): Response
    {
        abort_unless(StagingDeployHost::matches(), 404);

        return Inertia::render('Platform/Deploy', [
            'status'   => $this->deploySnapshot($github),
            'settings' => $github->settings(),
        ]);
    }

    public function deployStatus(GitHubDeployService $github): \Illuminate\Http\JsonResponse
    {
        abort_unless(StagingDeployHost::matches(), 404);

        return response()->json($this->deploySnapshot($github));
    }

    public function promote(Request $request, GitHubDeployService $github): RedirectResponse
    {
        abort_unless(StagingDeployHost::matches(), 404);
        $data = $request->validate([
            'password'       => 'required|string',
            'reason'         => 'nullable|string|max:200',
            'confirm_text'   => 'required|in:DEPLOY',
            'allow_redeploy' => 'nullable|boolean',
        ]);
        if (! Hash::check($data['password'], (string) $request->user()?->getAuthPassword())) {
            return back()->withErrors(['password' => 'Password incorrect.']);
        }

        try {
            $result = $github->promoteAndDeploy($data['reason'] ?? null, $request->boolean('allow_redeploy'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['deploy' => $e->getMessage()]);
        }

        return back()->with('success', $result['detail']);
    }

    /**
     * @return array<string, mixed>
     */
    private function deploySnapshot(GitHubDeployService $github): array
    {
        $status = $github->settings();
        try {
            if ($github->isConfigured()) {
                $status = array_merge($status, $github->status());
            } else {
                $status['message'] = 'Set GITHUB_TOKEN on this staging host, then reload.';
            }
        } catch (RuntimeException $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function parse(string $line): array
    {
        $line = trim(preg_replace('/^php\s+artisan\s+/i', '', trim($line)) ?? '');
        if ($line === '' || preg_match('/[;&|`$<>\\\\]/', $line)) {
            throw new InvalidArgumentException('Enter one Artisan command. Shell operators are not allowed.');
        }
        $tokens = preg_split('/\s+/', $line) ?: [];
        $name   = array_shift($tokens) ?? '';
        if ($name === '' || ! preg_match('/^[A-Za-z0-9:_-]+$/', $name)) {
            throw new InvalidArgumentException('Command name is not valid.');
        }
        if (in_array($name, ['tinker', 'serve', 'pail', 'queue:work', 'queue:listen', 'schedule:work'], true)) {
            throw new InvalidArgumentException($name.' stays running and cannot be used from this page.');
        }
        $parameters = [];
        $position   = 0;
        $count      = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (str_starts_with($token, '--')) {
                $body = substr($token, 2);
                if (str_contains($body, '=')) {
                    [$key, $value]         = explode('=', $body, 2);
                    $parameters['--'.$key] = $value;
                } elseif (isset($tokens[$i + 1]) && ! str_starts_with($tokens[$i + 1], '-')) {
                    $parameters['--'.$body] = $tokens[++$i];
                } else {
                    $parameters['--'.$body] = true;
                }

                continue;
            }
            $parameters[$position++] = $token;
        }

        return [$name, $parameters];
    }
}
