@extends('install.layout')

@section('title', 'Ready to Install')

@section('subtitle', 'Ready to Install')

@section('step-indicator')
    <div class="install-step-indicator">
        <div class="step-badge completed">1</div>
        <div class="step-badge completed">2</div>
        <div class="step-badge completed">3</div>
        <div class="step-badge completed">4</div>
        <div class="step-badge active">5</div>
    </div>
@endsection

@section('content')
    <h1 class="mb-1 fw-medium welcome-title">Ready to Install</h1>
    <p class="mb-4 text-muted">Review your configuration and click Install Now to complete the installation.</p>

    <div class="install-summary">
        <h6>Installation Summary</h6>
        <div class="install-summary-item">
            <span>Database:</span>
            <span>{{ session('db_config.database', 'N/A') }}</span>
        </div>
        <div class="install-summary-item">
            <span>Application Name:</span>
            <span>{{ session('app_config.name', config('app.name', 'Laravel')) }}</span>
        </div>
        <div class="install-summary-item">
            <span>Application URL:</span>
            <span>{{ session('app_config.url', 'N/A') }}</span>
        </div>
        <div class="install-summary-item">
            <span>Environment:</span>
            <span>{{ ucfirst(session('app_config.env', 'production')) }}</span>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>ℹ Note:</strong> The installer will attempt to automatically run migrations. If this fails due to server permissions, you'll need to run the commands manually via SSH.
    </div>

    <form method="POST" action="{{ route('install.execute') }}" id="installForm">
        @csrf
        <div class="d-flex gap-3">
            <a href="{{ route('install.admin') }}" class="btn-secondary">Back</a>
            <button type="submit" class="btn-install">Install Now</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('installForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Installing...';
        });
    </script>
@endpush

