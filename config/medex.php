<?php

return [
    'enabled' => (bool) env('MEDEX_ENABLED', true),

    /** HTML page cache TTL in seconds (default 12 hours). */
    'cache_ttl' => (int) env('MEDEX_CACHE_TTL', 43200),

    'user_agent' => env('MEDEX_USER_AGENT', 'EpharmaCatalogBot/1.0 (+local pharmacy reference sync)'),

    'timeout' => (int) env('MEDEX_TIMEOUT', 25),
];
