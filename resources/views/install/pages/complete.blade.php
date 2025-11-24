@extends('install.layout')

@section('title', 'Installation Complete')

@section('subtitle', 'Installation Complete')

@section('content')
    <div class="success-icon">
        <svg fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" />
        </svg>
    </div>

    <h1 class="mb-1 fw-medium welcome-title text-center">Installation Complete!</h1>
    <p class="mb-4 text-muted text-center">{{ config('app.name') }} has been successfully installed on
        your server.
    </p>

    @if (!empty($results))
        <div class="alert alert-success">
            <strong>✅ Installation Completed Successfully!</strong>
            <div class="mt-2">
                @if (isset($results['key_generate']) && $results['key_generate']['success'])
                    <div class="result-item">
                        <div class="result-icon success">✓</div>
                        <span>Application key generated</span>
                    </div>
                @endif

                @if (isset($results['migrate']) && $results['migrate']['success'])
                    <div class="result-item">
                        <div class="result-icon success">✓</div>
                        <span>Database migrations completed</span>
                    </div>
                @endif

                @if (isset($results['admin_user']) && $results['admin_user']['success'])
                    <div class="result-item">
                        <div class="result-icon success">✓</div>
                        <span>Admin account created</span>
                    </div>
                @endif

            </div>
        </div>

        @if (session('admin_config'))
            <div class="alert alert-info">
                <strong>📧 Admin Account Details:</strong>
                <div class="mt-2">
                    <div class="result-item">
                        <span><strong>Email:</strong> {{ session('admin_config.email') }}</span>
                    </div>
                    <div class="result-item">
                        <span><strong>Name:</strong> {{ session('admin_config.name') }}</span>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="alert alert-info">
        <strong>ℹ Next Steps:</strong>
        <ul class="mb-0 mt-2">
            <li>Set <code>APP_DEBUG=false</code> in production</li>
            <li>Configure your application settings</li>
            <li>Start using your application</li>
        </ul>
    </div>

    <div class="d-flex gap-3 justify-content-center">
        <a href="{{ route('login') }}" class="btn-install">Sign In to Application</a>
    </div>
@endsection

