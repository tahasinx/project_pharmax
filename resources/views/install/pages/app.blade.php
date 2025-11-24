@extends('install.layout')

@section('title', 'Application Configuration')

@section('subtitle', 'App Settings')

@section('step-indicator')
    <div class="install-step-indicator">
        <div class="step-badge completed">1</div>
        <div class="step-badge completed">2</div>
        <div class="step-badge active">3</div>
        <div class="step-badge">4</div>
        <div class="step-badge">5</div>
    </div>
@endsection

@section('content')
    <h1 class="mb-1 fw-medium welcome-title">Application Configuration</h1>
    <p class="mb-4 text-muted">Configure your application settings. You can change these later in the .env file.</p>

    <form method="POST" action="{{ route('install.save.app') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Application Name</label>
            <input type="text" class="form-control" name="app_name" value="{{ old('app_name', $appConfig['name'] ?? config('app.name', 'Laravel')) }}" required>
            @error('app_name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Application URL</label>
            <input type="url" class="form-control" name="app_url" value="{{ old('app_url', $appConfig['url'] ?? url('/')) }}" required>
            <span class="form-text">Full URL where your application will be accessed (e.g., https://yourdomain.com)</span>
            @error('app_url')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Environment</label>
            <select class="form-select" name="app_env" required>
                <option value="production" {{ old('app_env', $appConfig['env'] ?? 'production') === 'production' ? 'selected' : '' }}>Production</option>
                <option value="local" {{ old('app_env', $appConfig['env'] ?? 'production') === 'local' ? 'selected' : '' }}>Local/Development</option>
            </select>
            @error('app_env')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="app_debug" id="app_debug" value="1" {{ old('app_debug', isset($appConfig['debug']) && $appConfig['debug'] === 'true') ? 'checked' : '' }}>
            <label class="form-check-label" for="app_debug">
                Enable Debug Mode
                <small class="d-block text-muted">Only enable in development. Disable in production for security.</small>
            </label>
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('install.database') }}" class="btn-secondary">Back</a>
            <button type="submit" class="btn-install">Continue</button>
        </div>
    </form>
@endsection

