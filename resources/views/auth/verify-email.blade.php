{{-- Hallmark · macrostructure: 15 Split Studio · design-system: design.md --}}
@extends('layouts.auth-panel', [
    'heading' => __('diwan.password_reset.verify_title'),
    'body'    => __('diwan.password_reset.verify_body'),
    'status'  => session('status') === 'verification-link-sent'
        ? __('diwan.password_reset.verify_sent')
        : null,
    'image'   => 'assets/images/about.jpg',
])

@section('form')
    <div class="d-flex flex-wrap gap-3 align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg">
                {{ __('diwan.password_reset.verify_resend') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="hm-link hm-link--sm">{{ __('diwan.account.sign_out') }}</button>
        </form>
    </div>
@endsection
