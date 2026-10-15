@extends('layouts.auth', [
    'heading' => __('diwan.password_reset.confirm_title'),
    'body'    => __('diwan.password_reset.confirm_body'),
])

@section('title', __('diwan.password_reset.confirm_title'))

@section('form')
    <form method="POST" action="{{ route('password.confirm') }}" x-data="submitOnce" @submit="lock" class="grid gap-7">
        @csrf

        <x-ui.field name="password" type="password" :label="__('diwan.auth.password')"
                    required autofocus autocomplete="current-password" dir="ltr" />

        <x-ui.button type="submit" :block="true" ::disabled="busy">
            <span x-show="idle">{{ __('diwan.password_reset.confirm') }}</span>
            <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
        </x-ui.button>
    </form>
@endsection
