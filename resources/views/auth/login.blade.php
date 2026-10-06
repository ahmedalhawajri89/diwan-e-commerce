@extends('layouts.auth', [
    'heading' => __('diwan.auth.sign_in_lede'),
    'body'    => __('diwan.auth.sign_in_body'),
    'status'  => session('status'),
    'tabs'    => true,
])

@section('title', __('diwan.auth.sign_in_title'))

@section('form')
    <form method="POST" action="{{ route('login') }}" x-data="submitOnce" @submit="lock" class="grid gap-7">
        @csrf

        <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email')"
                    required autofocus autocomplete="username" dir="ltr" />

        <x-ui.field name="password" type="password" :label="__('diwan.auth.password')"
                    required autocomplete="current-password" dir="ltr" />

        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
            <label class="inline-flex items-center gap-2.5 cursor-pointer text-neutral">
                <input type="checkbox" name="remember" class="size-4 m-0 accent-ink cursor-pointer">
                {{ __('diwan.auth.remember') }}
            </label>

            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="inline-flex items-center min-h-11 text-accent-ink no-underline hover:underline underline-offset-4">
                    {{ __('diwan.auth.forgot') }}
                </a>
            @endif
        </div>

        <x-ui.button type="submit" :block="true" ::disabled="busy">
            <span x-show="idle">{{ __('diwan.auth.submit_login') }}</span>
            <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
        </x-ui.button>
    </form>
@endsection

@section('after')
    {{ __('diwan.auth.no_account') }}
    <a href="{{ route('register') }}" class="inline-flex items-center min-h-11 text-ink underline underline-offset-4 decoration-rule-2 hover:decoration-ink">{{ __('diwan.auth.register_title') }}</a>
@endsection
