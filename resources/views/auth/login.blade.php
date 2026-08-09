@extends('layouts.furnish')

@section('title', 'Log in')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background-color: var(--bg-soft);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                
                <div class="text-center mb-5">
                    <a href="{{ route('furnish.index') }}" class="text-decoration-none">
                        <span class="fs-2 fw-bold text-dark letter-spacing-tight">FURNISH.</span>
                    </a>
                    <h1 class="h3 fw-bold mt-4 mb-2">Welcome back</h1>
                    <p class="text-muted">Enter your details to access your account.</p>
                </div>

                <div class="card card-modern border-0 p-4 p-md-5">
                    <!-- Session Status -->
                    @if(session('status'))
                        <div class="alert alert-success mb-4 rounded-3 border-0 small">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control-modern form-control w-100 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                            @error('email')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-0">Password</label>
                                @if (Route::has('password.request'))
                                    <a class="text-primary text-decoration-none small fw-semibold" href="{{ route('password.request') }}">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <input type="password" name="password" id="password" class="form-control-modern form-control w-100 @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-5 custom-checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                <label class="form-check-label text-muted small" for="remember_me">
                                    Keep me logged in
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-modern btn-modern btn-lg w-100">
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted small">Don't have an account? <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Sign up</a></p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
