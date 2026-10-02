<?php

namespace App\Services;

use App\Support\StagingDeployHost;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubDeployService
{
    public function isConfigured(): bool
    {
        $token = trim((string) config('github_deploy.token'));

        return (bool) config('github_deploy.enabled')
            && strlen($token) >= 20
            && filled(config('github_deploy.owner'))
            && filled(config('github_deploy.repo'))
            && StagingDeployHost::allowedHosts() !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return [
            'owner'      => (string) config('github_deploy.owner'),
            'repo'       => (string) config('github_deploy.repo'),
            'base'       => (string) config('github_deploy.base_branch', 'dev'),
            'prod'       => (string) config('github_deploy.prod_branch', 'master'),
            'workflow'   => (string) config('github_deploy.workflow', 'production.yml'),
            'enabled'    => (bool) config('github_deploy.enabled'),
            'configured' => $this->isConfigured(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $this->assertConfigured();
        [$owner, $repo, $base, $prod, $workflow] = $this->coords();

        $baseRef = $this->get("repos/{$owner}/{$repo}/git/ref/heads/{$base}");
        $prodRef = $this->get("repos/{$owner}/{$repo}/git/ref/heads/{$prod}");
        $baseSha = $baseRef['object']['sha'] ?? null;
        $prodSha = $prodRef['object']['sha'] ?? null;

        $aheadBy  = 0;
        $behindBy = 0;
        $commits  = [];
        if ($baseSha && $prodSha) {
            $comparison = $this->get("repos/{$owner}/{$repo}/compare/{$prod}...{$base}");
            $aheadBy    = (int) ($comparison['ahead_by'] ?? 0);
            $behindBy   = (int) ($comparison['behind_by'] ?? 0);
            $commits    = collect($comparison['commits'] ?? [])
                ->reverse()
                ->take(12)
                ->map(fn ($commit) => [
                    'sha'     => substr((string) ($commit['sha'] ?? ''), 0, 7),
                    'message' => trim(explode("\n", (string) ($commit['commit']['message'] ?? ''))[0]),
                    'author'  => $commit['commit']['author']['name'] ?? '—',
                    'date'    => $commit['commit']['author']['date'] ?? null,
                ])
                ->values()
                ->all();
        }

        try {
            $runs = $this->get("repos/{$owner}/{$repo}/actions/workflows/{$workflow}/runs", [
                'per_page' => 5,
                'branch'   => $prod,
            ]);
            $workflowRuns = $runs['workflow_runs'] ?? [];
        } catch (RuntimeException $e) {
            if (! str_contains($e->getMessage(), '404')) {
                throw $e;
            }
            $workflowRuns = [];
        }
        $recent = collect($workflowRuns)->take(5)->map(fn ($run) => $this->mapRun($run))->values()->all();
        $active = collect($workflowRuns)->map(fn ($run) => $this->mapRun($run))->first(
            fn ($run) => in_array($run['status'], ['queued', 'in_progress', 'waiting', 'pending'], true)
        );

        $inSync         = $baseSha && $prodSha && hash_equals((string) $baseSha, (string) $prodSha);
        $canFastForward = $aheadBy > 0 && $behindBy === 0;

        return [
            'configured'       => true,
            'base'             => $base,
            'prod'             => $prod,
            'base_sha'         => $baseSha ? substr($baseSha, 0, 7) : null,
            'prod_sha'         => $prodSha ? substr($prodSha, 0, 7) : null,
            'base_sha_full'    => $baseSha,
            'ahead_by'         => $aheadBy,
            'behind_by'        => $behindBy,
            'in_sync'          => $inSync,
            'can_fast_forward' => $canFastForward,
            'needs_merge'      => $aheadBy > 0 && $behindBy > 0,
            'commits'          => $commits,
            'runs'             => $recent,
            'run_busy'         => $active !== null,
            'message'          => $inSync
                ? 'Production branch is already at the staging tip.'
                : ($canFastForward
                    ? "{$aheadBy} commit(s) on {$base} can fast-forward onto {$prod}."
                    : ($behindBy > 0
                        ? "{$prod} has commits that are not in {$base}. Fast-forward is blocked."
                        : 'Unable to compare branches.')),
        ];
    }

    /**
     * Fast-forward only. Never force-push.
     *
     * @return array{detail: string}
     */
    public function promoteAndDeploy(?string $reason, bool $allowRedeploy): array
    {
        $this->assertConfigured();
        if (! StagingDeployHost::matches()) {
            throw new RuntimeException('GitHub deploy is not allowed on this host.');
        }

        $before = $this->status();
        if (! empty($before['run_busy'])) {
            throw new RuntimeException('A production workflow is still running.');
        }
        if ($before['needs_merge'] || ($before['behind_by'] ?? 0) > 0) {
            throw new RuntimeException('Production has unique commits. Merge on GitHub first.');
        }

        [$owner, $repo, $base, $prod, $workflow] = $this->coords();

        if ($before['in_sync']) {
            if (! $allowRedeploy) {
                throw new RuntimeException('Already in sync. Tick “Allow redeploy” to run production deploy again.');
            }
            $this->dispatch($owner, $repo, $workflow, $prod, $reason ?: 'Redeploy');

            return ['detail' => "Dispatched {$workflow} on {$prod}."];
        }

        if (! $before['can_fast_forward']) {
            throw new RuntimeException('Nothing to promote.');
        }

        $sha = (string) ($before['base_sha_full'] ?? '');
        if (! preg_match('/^[a-f0-9]{40}$/i', $sha)) {
            throw new RuntimeException('Refusing promote: invalid source SHA.');
        }
        $this->updateRef($owner, $repo, $prod, $sha);

        return ['detail' => "Fast-forwarded {$prod} to {$base} (".substr($sha, 0, 7).'). Production deploy runs from that push.'];
    }

    protected function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GitHub deploy is not configured (GITHUB_TOKEN / allowed host).');
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
     */
    protected function coords(): array
    {
        $owner    = $this->ident((string) config('github_deploy.owner'), 'owner');
        $repo     = $this->ident((string) config('github_deploy.repo'), 'repo');
        $base     = $this->ident((string) config('github_deploy.base_branch'), 'base branch');
        $prod     = $this->ident((string) config('github_deploy.prod_branch'), 'prod branch');
        $workflow = trim((string) config('github_deploy.workflow'));
        if ($base === $prod || ! preg_match('/^[A-Za-z0-9._-]+\.ya?ml$/', $workflow)) {
            throw new RuntimeException('Invalid GitHub deploy coordinates.');
        }

        return [$owner, $repo, $base, $prod, $workflow];
    }

    protected function ident(string $value, string $label): string
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^[A-Za-z0-9._-]+$/', $value) || str_contains($value, '..')) {
            throw new RuntimeException("Invalid GitHub {$label}.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query = []): array
    {
        $response = Http::withToken((string) config('github_deploy.token'))
            ->accept('application/vnd.github+json')
            ->get('https://api.github.com/'.$path, $query);

        if (! $response->successful()) {
            $hint = $response->status() === 404 ? ' (branch or file not found)' : '';
            throw new RuntimeException('GitHub API error: '.$response->status().$hint);
        }

        return $response->json() ?? [];
    }

    protected function updateRef(string $owner, string $repo, string $branch, string $sha): void
    {
        $response = Http::withToken((string) config('github_deploy.token'))
            ->accept('application/vnd.github+json')
            ->patch("https://api.github.com/repos/{$owner}/{$repo}/git/refs/heads/{$branch}", [
                'sha'   => $sha,
                'force' => false,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('GitHub refused the fast-forward ('.$response->status().').');
        }
    }

    protected function dispatch(string $owner, string $repo, string $workflow, string $branch, string $reason): void
    {
        $response = Http::withToken((string) config('github_deploy.token'))
            ->accept('application/vnd.github+json')
            ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/{$workflow}/dispatches", [
                'ref'    => $branch,
                'inputs' => ['reason' => $reason],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Could not start the production workflow ('.$response->status().').');
        }
    }

    /**
     * @param  array<string, mixed>  $run
     * @return array{name: string, status: string, conclusion: string, url: string, created_at: ?string}
     */
    protected function mapRun(array $run): array
    {
        return [
            'name'       => (string) ($run['name'] ?? 'Deploy'),
            'status'     => (string) ($run['status'] ?? ''),
            'conclusion' => (string) ($run['conclusion'] ?? ''),
            'url'        => (string) ($run['html_url'] ?? ''),
            'created_at' => $run['created_at'] ?? null,
        ];
    }
}
