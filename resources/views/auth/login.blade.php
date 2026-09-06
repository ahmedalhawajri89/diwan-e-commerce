{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 6/6 · left = form · right = photograph with a scale caption
  Same diptych as the marketing pages, so signing in does not feel like leaving
  the shop for a different product.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.auth.sign_in_title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even align-items-center">
                {{-- ══ Form ══════════════════════════════════════════════ --}}
                <div class="hm-dip__claim hm-hold-note">
                    <h1 class="hm-dip__title hm-t-3xl">{{ __('diwan.auth.sign_in_lede') }}</h1>
                    <p class="hm-dip__body hm-t-sm">{{ __('diwan.auth.sign_in_body') }}</p>

                    @if(session('status'))
                        <p class="hm-note hm-note--ok hm-mb-md" role="status">
                            <x-icon name="check-lg" />
                            <span>{{ session('status') }}</span>
                        </p>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <label class="hm-field @error('email') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   autocomplete="username" dir="ltr">
                            @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>

                        <label class="hm-field @error('password') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('diwan.auth.password') }}</span>
                            <input type="password" name="password" required autocomplete="current-password" dir="ltr">
                            @error('password')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 hm-mb-lg">
                            <label class="hm-check">
                                <input type="checkbox" name="remember">
                                <span>{{ __('diwan.auth.remember') }}</span>
                            </label>

                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="hm-link hm-link--sm">
                                    {{ __('diwan.auth.forgot') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block">
                            {{ __('diwan.auth.submit_login') }}
                        </button>
                    </form>

                    <p class="hm-muted hm-t-sm hm-mt-lg">
                        {{ __('diwan.auth.no_account') }}
                        <a href="{{ route('register') }}" class="hm-link hm-link--sm">{{ __('diwan.auth.register_title') }}</a>
                    </p>
                </div>

                {{-- ══ Evidence ══════════════════════════════════════════ --}}
                <div class="hm-dip__proof">
                    <x-picture class="hm-dip__img" :src="asset('assets/images/auth-panel.jpg')"
                               :alt="__('diwan.auth.side_alt')"
                               sizes="(max-width: 60rem) 92vw, 32rem"
                               :width="1000" :height="1250"
                               loading="lazy" decoding="async" />
                    <p class="hm-dip__caption">{{ __('diwan.auth.side_caption') }}</p>
                </div>
            </div>
        </div>
    </section>

@endsection
