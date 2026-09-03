{{-- Hallmark · macrostructure: 15 Split Studio · design-system: design.md --}}
@extends('layouts.auth-panel', [
    'heading' => __('diwan.password_reset.confirm_title'),
    'body'    => __('diwan.password_reset.confirm_body'),
    'image'   => 'assets/images/login-bg.jpg',
])

@section('form')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <label class="hm-field @error('password') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('diwan.auth.password') }}</span>
            <input type="password" name="password" required autofocus autocomplete="current-password" dir="ltr">
            @error('password')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block">
            {{ __('diwan.password_reset.confirm') }}
        </button>
    </form>
@endsection
