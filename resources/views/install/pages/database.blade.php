@extends('install.layout')

@section('title', 'Database Configuration')

@section('subtitle', 'Database Setup')

@section('step-indicator')
    <div class="install-step-indicator">
        <div class="step-badge completed">1</div>
        <div class="step-badge active">2</div>
        <div class="step-badge">3</div>
        <div class="step-badge">4</div>
        <div class="step-badge">5</div>
    </div>
@endsection

@section('content')
    <h1 class="mb-1 fw-medium welcome-title">Database Configuration</h1>
    <p class="mb-4 text-muted">Enter your database credentials. The database will be created automatically if it doesn't exist.</p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('install.test.database') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Database Host</label>
            <input type="text" class="form-control" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
            @error('db_host')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Database Port</label>
            <input type="number" class="form-control" name="db_port" value="{{ old('db_port', '3306') }}" required>
            @error('db_port')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Database Name</label>
            <input type="text" class="form-control" name="db_database" value="{{ old('db_database', strtolower(str_replace(' ', '_', config('app.name', 'laravel')))) }}" required>
            <span class="form-text">Database will be created if it doesn't exist</span>
            @error('db_database')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Database Username</label>
            <input type="text" class="form-control" name="db_username" value="{{ old('db_username', 'root') }}" required>
            @error('db_username')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Database Password</label>
            <input type="password" class="form-control" name="db_password" value="{{ old('db_password') }}">
            @error('db_password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('install.index') }}" class="btn-secondary">Back</a>
            <button type="submit" class="btn-install">Test Connection & Continue</button>
        </div>
    </form>
@endsection

