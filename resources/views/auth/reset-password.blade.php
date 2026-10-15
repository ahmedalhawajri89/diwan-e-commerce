@extends('layouts.auth', [
    'heading' => __('diwan.password_reset.reset_title'),
    'body'    => __('diwan.password_reset.reset_body'),
])

@section('title', __('diwan.password_reset.reset_title'))

@section('form')
    <form method="POST" action="{{ route('password.store') }}" x-data="submitOnce" @submit="lock" class="grid gap-7">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email', $request->email)"
                    required autofocus autocomplete="username" dir="ltr" />

        <x-ui.field name="password" type="password" :label="__('diwan.auth.password')"
                    required autocomplete="new-password" dir="ltr" />

        <x-ui.field name="password_confirmation" type="password" :label="__('diwan.auth.password_confirm')"
                    required autocomplete="new-password" dir="ltr" />

        <x-ui.button type="submit" :block="true" ::disabled="busy">
            <span x-show="idle">{{ __('diwan.password_reset.reset') }}</span>
            <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
        </x-ui.button>
    </form>
@endsection

@section('after')
    @include('auth.partials.back-to-sign-in')
@endsection
