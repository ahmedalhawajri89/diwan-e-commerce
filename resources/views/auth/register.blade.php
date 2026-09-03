{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 6/6 · left = form · right = photograph with a scale caption
  Direction flips against the sign-in page so the pair reads as two sides of the
  same spread rather than one screen with the labels swapped.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.auth.register_title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-dip--flip" style="align-items: center;">
                {{-- ══ Form ══════════════════════════════════════════════ --}}
                <div class="hm-dip__claim" style="max-width: 28rem; margin-inline-start: auto;">
                    <h1 class="hm-dip__title" style="font-size: var(--text-3xl);">{{ __('diwan.auth.register_lede') }}</h1>
                    <p class="hm-dip__body" style="font-size: var(--text-sm);">{{ __('diwan.auth.register_body') }}</p>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <label class="hm-field @error('name') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('diwan.auth.name') }}</span>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   autocomplete="name" maxlength="255">
                            @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>

                        <label class="hm-field @error('email') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
                            <input type="email" name="email" value="{{ old('email') }}" required
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
                            <input type="password" name="password_confirmation" required
                                   autocomplete="new-password" dir="ltr">
                            @error('password_confirmation')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>

                        <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block"
                                style="margin-top: var(--space-lg);">
                            {{ __('diwan.auth.submit_register') }}
                        </button>
                    </form>

                    <p class="hm-muted" style="font-size: var(--text-sm); margin-top: var(--space-lg);">
                        {{ __('diwan.auth.have_account') }}
                        <a href="{{ route('login') }}" class="hm-link hm-link--sm">{{ __('diwan.auth.sign_in_title') }}</a>
                    </p>
                </div>

                {{-- ══ Evidence ══════════════════════════════════════════ --}}
                <div class="hm-dip__proof">
                    <img class="hm-dip__img" src="{{ asset('assets/images/about.jpg') }}"
                         alt="{{ __('diwan.auth.side_alt') }}"
                         loading="lazy" decoding="async" width="1000" height="1250">
                    <p class="hm-dip__caption">{{ __('diwan.auth.side_caption') }}</p>
                </div>
            </div>
        </div>
    </section>

@endsection
