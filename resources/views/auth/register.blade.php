@extends('layouts.auth', [
    'heading' => __('diwan.auth.register_lede'),
    'body'    => __('diwan.auth.register_body'),
    'tabs'    => true,
])

@section('title', __('diwan.auth.register_title'))

@section('form')
    <form method="POST" action="{{ route('register') }}" x-data="submitOnce" @submit="lock" class="grid gap-7">
        @csrf

        <x-ui.field name="name" :label="__('diwan.auth.name')" :value="old('name')"
                    required autofocus autocomplete="name" maxlength="255" />

        <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email')"
                    required autocomplete="username" dir="ltr" />

        <x-ui.field name="password" type="password" :label="__('diwan.auth.password')"
                    required autocomplete="new-password" dir="ltr" />

        <x-ui.field name="password_confirmation" type="password" :label="__('diwan.auth.password_confirm')"
                    required autocomplete="new-password" dir="ltr" />

        <x-ui.button type="submit" :block="true" ::disabled="busy" class="mt-2">
            <span x-show="idle">{{ __('diwan.auth.submit_register') }}</span>
            <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
        </x-ui.button>
    </form>
@endsection

@section('after')
    {{ __('diwan.auth.have_account') }}
    <a href="{{ route('login') }}" class="text-ink underline underline-offset-4 decoration-rule-2 hover:decoration-ink">{{ __('diwan.auth.sign_in_title') }}</a>
@endsection
