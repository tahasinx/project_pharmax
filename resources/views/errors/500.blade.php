<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | {{ config('app.name', 'PharmaCare') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .error-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 1rem;
            line-height: 1;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            color: #374151;
            border-left: 4px solid #ef4444;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        @media (max-width: 640px) {
            .error-container {
                padding: 2rem 1.5rem;
            }

            .error-code {
                font-size: 4rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title">Internal Server Error</h1>
        <p class="error-message">
            Something went wrong on our end. We're working to fix this issue.
        </p>

        @if (config('app.debug') && isset($error))
            <div class="error-details">
                {{ $error }}
            </div>
        @endif

        <div class="action-buttons">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Go Back</a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</body>

</html>
