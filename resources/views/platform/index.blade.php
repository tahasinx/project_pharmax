<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Epharma platform</title>
    <style>
        body { margin: 0; font-family: Georgia, serif; background: #f4f1ea; color: #1c2b24; }
        main { max-width: 720px; margin: 0 auto; padding: 48px 24px; }
        a { color: #1f3d32; }
        li { margin: 10px 0; font-family: "Segoe UI", sans-serif; }
    </style>
</head>
<body>
<main>
    <h1>Pharmacies</h1>
    <p>This host is the directory. Each pharmacy opens on its own address.</p>
    <ul>
        @forelse ($companies as $company)
            <li>
                <a href="https://{{ $company->slug }}.{{ config('database.tenant.base_domain') }}">{{ $company->name }}</a>
                <span>— {{ $company->database_name }}</span>
            </li>
        @empty
            <li>No pharmacies yet.</li>
        @endforelse
    </ul>
</main>
</body>
</html>
