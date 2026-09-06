{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  An account page is an app page: no hero, no display type above --text-2xl,
  no enrichment. It was the last view still referencing card-modern and
  btn-modern, which the storefront layout has not defined since the redesign,
  so it rendered unstyled.

  The three forms are written out here rather than pulled from
  profile/partials/*, which are Tailwind components built for the Breeze
  layout. admin-edit still includes those partials, so they are left alone.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.profile.title'))

@section('content')

    {{-- $user and $orders both come from ProfileController::edit. This page
         used to re-query for them here and shadow what it had been given. --}}

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title">{{ __('diwan.profile.title') }}</h1>
                    <p class="hm-app__intro">{{ __('diwan.profile.intro') }}</p>
                </div>
                @if($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hm-link hm-link--sm">
                        {{ __('diwan.account.dashboard') }}
                        <x-icon name="arrow-right" />
                    </a>
                @endif
            </div>

            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="hm-note hm-note--warn hm-mb-xl">
                    <x-icon name="exclamation-triangle" />
                    <span>
                        {{ __('diwan.profile.unverified') }}
                        <button type="submit" form="resendVerification" class="hm-link hm-link--sm">
                            {{ __('diwan.profile.resend') }}
                        </button>
                    </span>
                </p>
                <form id="resendVerification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
            @endif

            {{-- ══ The room — the fit system's own setting ═════════════ --}}
            <section class="hm-pb-xl hm-ruled-b">
                <h2 class="hm-head__title hm-t-lg hm-mb-2xs">
                    {{ __('diwan.profile.room') }}
                </h2>
                <p class="hm-muted hm-t-sm">{{ __('diwan.profile.room_note') }}</p>

                <div class="d-flex flex-wrap gap-3 align-items-center">
                    @if($room)
                        <span class="hm-num hm-t-lg">
                            {{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}
                        </span>
                        <span class="hm-muted hm-num hm-t-sm">
                            {{ number_format(($room['width'] * $room['length']) / 10000, 1) }} m&sup2;
                        </span>
                    @else
                        <span class="hm-muted">{{ __('diwan.room.unset') }}</span>
                    @endif

                    <button type="button" class="hm-btn hm-btn--ghost" data-bs-toggle="modal" data-bs-target="#roomModal">
                        <x-icon name="rulers" />
                        {{ $room ? __('diwan.room.change') : __('diwan.room.set_prompt') }}
                    </button>
                </div>
            </section>

            {{-- ══ Details ════════════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl">
                <h2 class="hm-head__title hm-t-lg hm-mb-2xs">
                    {{ __('diwan.profile.details') }}
                </h2>
                <p class="hm-muted hm-t-sm">{{ __('diwan.profile.details_note') }}</p>

                <form class="hm-hold-panel" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <label class="hm-field @error('name') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.auth.name') }}</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               autocomplete="name" maxlength="255">
                        @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <label class="hm-field @error('email') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               autocomplete="username" dir="ltr">
                        @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <button type="submit" class="hm-btn hm-btn--primary">{{ __('diwan.profile.save') }}</button>
                </form>
            </section>

            {{-- ══ Password ═══════════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl">
                <h2 class="hm-head__title hm-t-lg hm-mb-2xs">
                    {{ __('diwan.profile.password') }}
                </h2>
                <p class="hm-muted hm-t-sm">{{ __('diwan.profile.password_note') }}</p>

                <form class="hm-hold-panel" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <label class="hm-field @error('current_password', 'updatePassword') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.profile.current') }}</span>
                        <input type="password" name="current_password" autocomplete="current-password" dir="ltr">
                        @error('current_password', 'updatePassword')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <label class="hm-field @error('password', 'updatePassword') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.profile.new') }}</span>
                        <input type="password" name="password" autocomplete="new-password" dir="ltr">
                        @error('password', 'updatePassword')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <label class="hm-field">
                        <span class="hm-field__label">{{ __('diwan.profile.confirm') }}</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" dir="ltr">
                    </label>

                    <button type="submit" class="hm-btn hm-btn--primary">{{ __('diwan.profile.save') }}</button>
                </form>
            </section>

            {{-- ══ Orders ═════════════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl">
                <div class="hm-head">
                    <h2 class="hm-head__title hm-t-lg">
                        {{ __('diwan.profile.orders') }}
                    </h2>
                    @if($orders->isNotEmpty())
                        <a href="{{ route('orders.index') }}" class="hm-head__aside hm-link hm-link--sm">
                            {{ __('diwan.orders.back') }}
                            <x-icon name="arrow-right" />
                        </a>
                    @endif
                </div>

                @forelse($orders as $order)
                    {{-- These used to be dead text: five orders you could see
                         and not open. Each row is the order now. --}}
                    <a href="{{ route('orders.show', $order) }}"
                       class="d-flex flex-wrap gap-3 align-items-center hm-listrow hm-listrow--sm">
                        <span class="hm-num hm-col-fig hm-accent-ink">
                            #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="hm-muted hm-num hm-fill-sm hm-t-xs">
                            {{ $order->created_at->translatedFormat('j F Y') }}
                        </span>
                        <span class="hm-chip {{ $order->status === 'cancelled' ? 'hm-chip--danger' : ($order->status === 'delivered' ? 'hm-chip--ok' : 'hm-chip--warn') }}">
                            {{ __('diwan.orders.status_values.' . $order->status) }}
                        </span>
                        <span class="hm-num hm-mis-auto">
                            {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
                        </span>
                        <x-icon name="chevron-right" class="hm-muted" />
                    </a>
                @empty
                    <p class="hm-muted hm-t-sm">{{ __('diwan.profile.no_orders') }}</p>
                @endforelse
            </section>

            {{-- ══ Close the account ══════════════════════════════════
                 design.md reserves the modal for genuinely irreversible
                 actions, and asks for the name typed rather than an OK
                 clicked. Breeze already requires the password, which is the
                 same discipline, so the password field stands in for it. --}}
            <section class="hm-py-xl">
                <h2 class="hm-head__title hm-t-lg hm-mb-2xs hm-danger">
                    {{ __('diwan.profile.danger') }}
                </h2>
                <p class="hm-muted hm-measure hm-t-sm">{{ __('diwan.profile.danger_note') }}</p>

                <form class="hm-hold-panel" method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <label class="hm-field @error('password', 'userDeletion') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.auth.password') }}</span>
                        <input type="password" name="password" required dir="ltr">
                        @error('password', 'userDeletion')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <button type="submit" class="hm-btn hm-btn--danger">{{ __('diwan.profile.delete') }}</button>
                </form>
            </section>

        </div>
    </section>

@endsection
