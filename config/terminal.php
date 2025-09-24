<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Command Paths
    |--------------------------------------------------------------------------
    |
    | Configure the paths to various command-line tools for different environments.
    | The system will automatically detect the environment and use appropriate paths.
    |
    */

    'environments' => [
        'cloud' => [
            'composer' => '/usr/local/bin/composer',
            'php'      => '/usr/local/bin/php',
            'node'     => '/usr/local/bin/node',
            'npm'      => '/usr/local/bin/npm',
            'git'      => '/usr/bin/git',
            'ls'       => 'ls',
            'pwd'      => 'pwd',
            'whoami'   => 'whoami',
            'date'     => 'date',
        ],

        'windows' => [
            'composer' => 'C:\\xampp\\php\\composer.phar',
            'php'      => 'C:\\xampp\\php\\php.exe',
            'node'     => 'C:\\Program Files\\nodejs\\node.exe',
            'npm'      => 'C:\\Program Files\\nodejs\\npm.cmd',
            'git'      => 'C:\\Program Files\\Git\\bin\\git.exe',
            'ls'       => 'dir',
            'pwd'      => 'cd',
            'whoami'   => 'whoami',
            'date'     => 'date',
        ],

        'linux' => [
            'composer' => '/usr/local/bin/composer',
            'php'      => '/usr/bin/php',
            'node'     => '/usr/local/bin/node',
            'npm'      => '/usr/local/bin/npm',
            'git'      => '/usr/bin/git',
            'ls'       => 'ls',
            'pwd'      => 'pwd',
            'whoami'   => 'whoami',
            'date'     => 'date',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Environment Detection
    |--------------------------------------------------------------------------
    |
    | Define indicators that help detect if the application is running in
    | a cloud/shared hosting environment.
    |
    */

    'cloud_indicators' => [
        'cpanel'         => function_exists('cpanel') || isset($_SERVER['CPANEL']),
        'shared_hosting' => isset($_SERVER['SHARED_HOSTING']),
        'cloudflare'     => isset($_SERVER['HTTP_CF_CONNECTING_IP']),
        'aws'            => isset($_SERVER['AWS_EXECUTION_ENV']),
        'heroku'         => isset($_SERVER['DYNO']),
        'digital_ocean'  => isset($_SERVER['DIGITALOCEAN']),
        'path_check'     => strpos(__DIR__, '/home/') === 0 || strpos(__DIR__, '/var/www/') === 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for a command to complete.
    |
    */

    'timeout' => 300, // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Allowed Commands
    |--------------------------------------------------------------------------
    |
    | List of commands that are allowed to be executed through the terminal.
    | This provides an additional layer of security.
    |
    */

    'allowed_commands' => [
        'composer' => ['install', 'update', 'dump-autoload', 'require', 'remove', 'show', 'outdated'],
        'npm'      => ['install', 'run', 'build', 'dev', 'prod', 'test', 'audit', 'update'],
        'php'      => ['artisan', '--version', '-v', '-m'],
        'artisan'  => [
            'migrate',
            'migrate:fresh',
            'migrate:rollback',
            'db:seed',
            'make:controller',
            'make:model',
            'make:migration',
            'make:seeder',
            'make:request',
            'make:middleware',
            'route:list',
            'config:cache',
            'config:clear',
            'cache:clear',
            'view:clear',
            'optimize',
            'optimize:clear',
            'key:generate',
            'storage:link',
            'queue:work',
            'queue:restart',
            'tinker'
        ],
        'git'    => ['status', 'pull', 'push', 'add', 'commit', 'log', 'branch'],
        'node'   => ['--version', '-v'],
        'ls'     => ['-la', '-l', '-a'],
        'dir'    => ['/w', '/p', '/a'],
        'pwd'    => [],
        'cd'     => [],
        'whoami' => [],
        'date'   => []
    ],
];
