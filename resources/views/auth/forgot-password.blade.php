{{-- Hallmark · macrostructure: 15 Split Studio · design-system: design.md --}}
@extends('layouts.auth-panel', [
    'heading' => __('diwan.password_reset.forgot_title'),
    'body'    => __('diwan.password_reset.forgot_body'),
    'status'  => session('status'),
    'image'   => 'assets/images/login-bg.jpg',
])

@section('form')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <label class="hm-field @error('email') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" dir="ltr">
            @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block">
            {{ __('diwan.password_reset.send_link') }}
        </button>
    </form>
@endsection
