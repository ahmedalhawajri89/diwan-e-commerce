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

    @php
        $user = auth()->user();
        $orders = \App\Models\Order::where('user_id', $user->id)->latest()->take(5)->get();
    @endphp

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title">{{ __('diwan.profile.title') }}</h1>
                    <p class="hm-app__intro">{{ __('diwan.profile.intro') }}</p>
                </div>
                @if($user->roles()->whereIn('name', ['admin', 'Admin'])->exists())
                    <a href="{{ route('admin.dashboard') }}" class="hm-link hm-link--sm">
                        {{ __('diwan.account.dashboard') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="hm-note hm-note--warn" style="margin-bottom: var(--space-xl);">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
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
            <section style="padding-bottom: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
                <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs);">
                    {{ __('diwan.profile.room') }}
                </h2>
                <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.room_note') }}</p>

                <div class="d-flex flex-wrap gap-3 align-items-center">
                    @if($room)
                        <span class="hm-num" style="font-size: var(--text-lg);">
                            {{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}
                        </span>
                        <span class="hm-muted hm-num" style="font-size: var(--text-sm);">
                            {{ number_format(($room['width'] * $room['length']) / 10000, 1) }} m&sup2;
                        </span>
                    @else
                        <span class="hm-muted">{{ __('diwan.room.unset') }}</span>
                    @endif

                    <button type="button" class="hm-btn hm-btn--ghost" data-bs-toggle="modal" data-bs-target="#roomModal">
                        <i class="bi bi-rulers" aria-hidden="true"></i>
                        {{ $room ? __('diwan.room.change') : __('diwan.room.set_prompt') }}
                    </button>
                </div>
            </section>

            {{-- ══ Details ════════════════════════════════════════════ --}}
            <section style="padding-block: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
                <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs);">
                    {{ __('diwan.profile.details') }}
                </h2>
                <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.details_note') }}</p>

                <form method="POST" action="{{ route('profile.update') }}" style="max-width: 30rem;">
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
            <section style="padding-block: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
                <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs);">
                    {{ __('diwan.profile.password') }}
                </h2>
                <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.password_note') }}</p>

                <form method="POST" action="{{ route('password.update') }}" style="max-width: 30rem;">
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
            <section style="padding-block: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
                <div class="hm-head">
                    <h2 class="hm-head__title" style="font-size: var(--text-lg);">
                        {{ __('diwan.profile.orders') }}
                    </h2>
                    @if($orders->isNotEmpty())
                        <a href="{{ route('orders.index') }}" class="hm-head__aside hm-link hm-link--sm">
                            {{ __('diwan.orders.back') }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>

                @forelse($orders as $order)
                    {{-- These used to be dead text: five orders you could see
                         and not open. Each row is the order now. --}}
                    <a href="{{ route('orders.show', $order) }}"
                       class="d-flex flex-wrap gap-3 align-items-center"
                       style="padding-block: var(--space-sm); border-bottom: var(--rule-hair) solid var(--color-rule);">
                        <span class="hm-num" style="flex: 0 0 6rem; color: var(--color-accent-ink);">
                            #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="hm-muted hm-num" style="flex: 1 1 8rem; font-size: var(--text-xs);">
                            {{ $order->created_at->translatedFormat('j F Y') }}
                        </span>
                        <span class="hm-chip {{ $order->status === 'cancelled' ? 'hm-chip--danger' : ($order->status === 'delivered' ? 'hm-chip--ok' : 'hm-chip--warn') }}">
                            {{ __('diwan.orders.status_values.' . $order->status) }}
                        </span>
                        <span class="hm-num" style="margin-inline-start: auto;">
                            {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
                        </span>
                        <i class="bi bi-chevron-right hm-muted" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.no_orders') }}</p>
                @endforelse
            </section>

            {{-- ══ Close the account ══════════════════════════════════
                 design.md reserves the modal for genuinely irreversible
                 actions, and asks for the name typed rather than an OK
                 clicked. Breeze already requires the password, which is the
                 same discipline, so the password field stands in for it. --}}
            <section style="padding-block: var(--space-xl);">
                <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs); color: var(--color-danger);">
                    {{ __('diwan.profile.danger') }}
                </h2>
                <p class="hm-muted hm-measure" style="font-size: var(--text-sm);">{{ __('diwan.profile.danger_note') }}</p>

                <form method="POST" action="{{ route('profile.destroy') }}" style="max-width: 30rem;">
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
