@extends('layouts.auth', [
    'heading' => __('diwan.password_reset.forgot_title'),
    'body'    => __('diwan.password_reset.forgot_body'),
    'status'  => session('status'),
])

@section('title', __('diwan.password_reset.forgot_title'))

@section('form')
    <form method="POST" action="{{ route('password.email') }}" x-data="submitOnce" @submit="lock" class="grid gap-7">
        @csrf

        <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email')"
                    required autofocus autocomplete="username" dir="ltr" />

        <x-ui.button type="submit" :block="true" ::disabled="busy">
            <span x-show="idle">{{ __('diwan.password_reset.send_link') }}</span>
            <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
        </x-ui.button>
    </form>
@endsection

@section('after')
    @include('auth.partials.back-to-sign-in')
@endsection
