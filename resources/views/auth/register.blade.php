@extends('layouts.furnish')

@section('title', 'Create Account')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background-color: var(--bg-soft);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                
                <div class="text-center mb-5">
                    <a href="{{ route('furnish.index') }}" class="text-decoration-none">
                        <span class="fs-2 fw-bold text-dark letter-spacing-tight">FURNISH.</span>
                    </a>
                    <h1 class="h3 fw-bold mt-4 mb-2">Create an account</h1>
                    <p class="text-muted">Start your journey with us today.</p>
                </div>

                <div class="card card-modern border-0 p-4 p-md-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control-modern form-control w-100 @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="John Doe">
                            @error('name')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control-modern form-control w-100 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com">
                            @error('email')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Password</label>
                            <input type="password" name="password" id="password" class="form-control-modern form-control w-100 @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-5">
                            <label for="password_confirmation" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control-modern form-control w-100 @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                            @error('password_confirmation')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-modern btn-modern btn-lg w-100">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted small">Already have an account? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Sign in</a></p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
