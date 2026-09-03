{{-- Hallmark · macrostructure: 15 Split Studio · design-system: design.md --}}
@extends('layouts.auth-panel', [
    'heading' => __('diwan.password_reset.reset_title'),
    'body'    => __('diwan.password_reset.reset_body'),
    'image'   => 'assets/images/about.jpg',
])

@section('form')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label class="hm-field @error('email') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                   autocomplete="username" dir="ltr">
            @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('password') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('diwan.auth.password') }}</span>
            <input type="password" name="password" required autocomplete="new-password" dir="ltr">
            @error('password')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('password_confirmation') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('diwan.auth.password_confirm') }}</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password" dir="ltr">
            @error('password_confirmation')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block">
            {{ __('diwan.password_reset.reset') }}
        </button>
    </form>
@endsection
