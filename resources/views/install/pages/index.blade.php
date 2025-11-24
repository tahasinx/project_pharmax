@extends('install.layout')

@section('title', 'Installation')

@section('subtitle', 'Installation Wizard')

@section('step-indicator')
    <div class="install-step-indicator">
        <div class="step-badge active">1</div>
        <div class="step-badge">2</div>
        <div class="step-badge">3</div>
        <div class="step-badge">4</div>
        <div class="step-badge">5</div>
    </div>
@endsection

@section('content')
    <h1 class="mb-1 fw-medium welcome-title">Welcome to {{ config('app.name') }} Installation</h1>
    <p class="mb-2 text-muted">Let's get you started! We'll check your system requirements and guide you
        through the installation process.</p>

    <div class="mb-4">
        <h6 class="mb-3 fw-medium">System Requirements</h6>
        @foreach ($requirements as $key => $requirement)
            <div class="requirement-item">
                <span>{{ $requirement['name'] }}</span>
                <span class="status-badge {{ $requirement['status'] ? 'status-ok' : 'status-fail' }}">
                    {{ $requirement['status'] ? '✓ OK' : '✗ Failed' }}
                </span>
            </div>
        @endforeach
    </div>

    @if (!$allRequirementsMet)
        <div class="alert alert-warning mb-4">
            <strong>⚠ Please fix the requirements above before proceeding.</strong>
            <p class="mb-0 mt-2">
                @if (file_exists(base_path('.env')))
                    <strong>Note:</strong> A .env file already exists. It will be overwritten during
                    installation.
                @endif
            </p>
        </div>
    @else
        <div class="alert alert-success mb-4">
            <strong>✓ All requirements met!</strong> You can proceed with installation.
        </div>
    @endif

    <div class="d-flex gap-3">
        <form method="GET" action="{{ route('install.database') }}">
            <button type="submit" class="btn-install" {{ !$allRequirementsMet ? 'disabled' : '' }}>
                Continue Installation
            </button>
        </form>
    </div>
@endsection

