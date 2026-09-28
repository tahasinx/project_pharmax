<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @php
        $brand = [
            'primary' => '#17342b',
            'font_family' => 'Inter',
            'font_href' => '',
            'font_size' => 16,
            'font_weight' => 400,
            'radius' => '10px',
        ];
        if (! request()->is('install*')) {
            try {
                $brand = app(\App\Services\Platform\PlatformSettingsStore::class)->theme();
            } catch (\Throwable) {
            }
        }
        $strong = min(900, (int) $brand['font_weight'] + 200);
    @endphp
    <style>
        :root {
            --pf-accent: {{ $brand['primary'] }};
            --pf-font: "{{ $brand['font_family'] }}", sans-serif;
            --pf-size: {{ (int) $brand['font_size'] }}px;
            --pf-weight: {{ (int) $brand['font_weight'] }};
            --pf-weight-strong: {{ $strong }};
            --pf-radius: {{ $brand['radius'] }};
        }
    </style>
    @if ($brand['font_href'] !== '')
        <link id="epharma-font" rel="stylesheet" href="{{ $brand['font_href'] }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
