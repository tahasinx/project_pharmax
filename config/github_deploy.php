<?php

return [
    /*
    | Staging platform host only (adminx). Never enable on production admin.
    | Token stays in .env — never commit it.
    */
    'enabled' => filter_var(env('GITHUB_DEPLOY_ENABLED', false), FILTER_VALIDATE_BOOL),

    'owner' => env('GITHUB_OWNER', 'tahasinx'),
    'repo' => env('GITHUB_REPO', 'project_pharmax'),
    'token' => env('GITHUB_TOKEN'),

    'base_branch' => env('GITHUB_BASE_BRANCH', 'dev'),
    'prod_branch' => env('GITHUB_PROD_BRANCH', 'master'),
    'workflow' => env('GITHUB_PROD_WORKFLOW', 'production.yml'),

    'allowed_subdomain' => strtolower((string) env('GITHUB_DEPLOY_SUBDOMAIN', 'adminx')),

    'allowed_hosts' => array_values(array_filter(array_map(
        static fn ($host) => strtolower(trim((string) $host)),
        explode(',', (string) env('GITHUB_DEPLOY_ALLOWED_HOSTS', ''))
    ))),
];
