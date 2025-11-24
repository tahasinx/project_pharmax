@extends('install.layout')

@section('title', 'Admin Account')


@section('subtitle', 'Admin Setup')

@section('step-indicator')
    <div class="install-step-indicator">
        <div class="step-badge completed">1</div>
        <div class="step-badge completed">2</div>
        <div class="step-badge completed">3</div>
        <div class="step-badge active">4</div>
        <div class="step-badge">5</div>
    </div>
@endsection

@section('content')
    <h1 class="mb-1 fw-medium welcome-title">Create Admin Account</h1>
    <p class="mb-4 text-muted">Create your administrator account to access the application.</p>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('install.save.admin') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
            <span class="form-text">This will be your login email</span>
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" autocomplete="new-password" required>
            <div class="password-requirements" id="password-requirements">
                <div class="password-requirements-title">Password Requirements:</div>
                <div class="requirement-item">• At least 9 characters</div>
                <div class="requirement-item">• At least 1 uppercase letter</div>
                <div class="requirement-item">• At least 1 lowercase letter</div>
                <div class="requirement-item">• At least 1 number</div>
                <div class="requirement-item">• At least 1 special character</div>
            </div>
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm_password" autocomplete="new-password" required>
            @error('confirm_password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('install.app') }}" class="btn-secondary">Back</a>
            <button type="submit" class="btn-install">Continue to Installation</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Only show password requirements when password field is focused
            $('#password').on('focus', function(e) {
                e.stopPropagation();
                $('#password-requirements').addClass('show');
            });

            // Hide password requirements when password field loses focus (if empty)
            $('#password').on('blur', function(e) {
                e.stopPropagation();
                if ($(this).val() === '') {
                    $('#password-requirements').removeClass('show');
                }
            });

            // Prevent other inputs from triggering password requirements
            $('input:not(#password), select, textarea').on('focus', function(e) {
                e.stopPropagation();
                // Ensure password requirements are hidden when other fields are focused
                if ($('#password').val() === '') {
                    $('#password-requirements').removeClass('show');
                }
            });
        });
    </script>
@endpush
