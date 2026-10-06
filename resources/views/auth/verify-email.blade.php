@extends('layouts.auth', [
    'heading' => __('diwan.password_reset.verify_title'),
    'body'    => __('diwan.password_reset.verify_body'),
    'status'  => session('status') === 'verification-link-sent'
        ? __('diwan.password_reset.verify_sent')
        : null,
])

@section('title', __('diwan.password_reset.verify_title'))

@section('form')
    <div class="grid gap-3">
        <form method="POST" action="{{ route('verification.send') }}" x-data="submitOnce" @submit="lock">
            @csrf
            <x-ui.button type="submit" :block="true" ::disabled="busy">
                <span x-show="idle">{{ __('diwan.password_reset.verify_resend') }}</span>
                <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" :block="true">{{ __('diwan.account.sign_out') }}</x-ui.button>
        </form>
    </div>
@endsection
