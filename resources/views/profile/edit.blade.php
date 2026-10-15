{{--
  The account page. A short index of its sections on the inline-start side,
  sticky on a desktop, and the sections themselves: the room (the fit system's
  own setting), details, password, recent orders, and closing the account.

  The forms are written out here rather than pulled from profile/partials/*,
  which belong to the admin's profile page.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.profile.title'))

@php
    // $user and $orders both come from ProfileController::edit.
    $tone = fn ($status) => ['cancelled' => 'danger', 'delivered' => 'ok'][$status] ?? 'warn';

    $sectionClass = 'py-12 border-0 border-b border-rule scroll-mt-36 first:pt-0';
    $heading = 'm-0 font-display text-2xl';
    $note = 'm-0 mt-2 mb-8 max-w-measure text-sm leading-body text-muted';

    $index = [
        'room' => __('diwan.profile.room'),
        'details' => __('diwan.profile.details'),
        'password' => __('diwan.profile.password'),
        'orders' => __('diwan.profile.orders'),
        'close' => __('diwan.profile.danger'),
    ];
@endphp

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.profile.title')" :lede="__('diwan.profile.intro')">
            <x-slot:aside>
                @if($user->isAdmin())
                    <x-ui.link :href="route('admin.dashboard')">{{ __('diwan.account.dashboard') }}</x-ui.link>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <x-ui.note tone="warn" class="mt-8">
                {{ __('diwan.profile.unverified') }}
                <button type="submit" form="resendVerification"
                        class="p-0 border-0 bg-transparent text-sm underline underline-offset-4 cursor-pointer">{{ __('diwan.profile.resend') }}</button>
            </x-ui.note>
            <form id="resendVerification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
        @endif

        <div class="grid gap-10 pt-12 lg:grid-cols-[14rem_1fr] lg:gap-20">
            <nav class="hidden lg:block" aria-label="{{ __('diwan.profile.title') }}">
                <ul class="sticky top-36 m-0 p-0 list-none border-0 border-s border-rule">
                    @foreach($index as $anchor => $title)
                        <li>
                            <a href="#{{ $anchor }}"
                               class="block -ms-px py-2 ps-4 border-0 border-s border-transparent text-sm text-muted no-underline transition-colors hover:border-ink hover:text-ink {{ $anchor === 'close' ? 'hover:text-danger hover:border-danger' : '' }}">{{ $title }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="min-w-0 max-w-3xl">
                {{-- ══ The room — the fit system's own setting ═════════════════ --}}
                <section id="room" class="{{ $sectionClass }}">
                    <h2 class="{{ $heading }}">{{ __('diwan.profile.room') }}</h2>
                    <p class="{{ $note }}">{{ __('diwan.profile.room_note') }}</p>

                    <div class="flex flex-wrap items-center justify-between gap-6 p-6 bg-paper-2">
                        @if($room)
                            <div>
                                <p class="m-0 font-mono text-2xl" dir="ltr">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</p>
                                <p class="m-0 mt-1 font-mono text-xs text-muted" dir="ltr">{{ number_format(($room['width'] * $room['length']) / 10000, 1) }} m&sup2;</p>
                            </div>
                        @else
                            <p class="m-0 text-muted">{{ __('diwan.room.unset') }}</p>
                        @endif

                        <x-ui.button variant="ghost" data-open-dialog="roomModal">
                            <x-icon name="rulers" class="size-4" />
                            {{ $room ? __('diwan.room.change') : __('diwan.room.set_prompt') }}
                        </x-ui.button>
                    </div>
                </section>

                {{-- ══ Details ═════════════════════════════════════════════════ --}}
                <section id="details" class="{{ $sectionClass }}">
                    <h2 class="{{ $heading }}">{{ __('diwan.profile.details') }}</h2>
                    <p class="{{ $note }}">{{ __('diwan.profile.details_note') }}</p>

                    <form method="POST" action="{{ route('profile.update') }}" class="grid gap-7 max-w-md">
                        @csrf
                        @method('PATCH')
                        <x-ui.field name="name" :label="__('diwan.auth.name')" :value="old('name', $user->name)" required autocomplete="name" maxlength="255" />
                        <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email', $user->email)" required autocomplete="username" dir="ltr" />
                        <div><x-ui.button type="submit">{{ __('diwan.profile.save') }}</x-ui.button></div>
                    </form>
                </section>

                {{-- ══ Password ════════════════════════════════════════════════ --}}
                <section id="password" class="{{ $sectionClass }}">
                    <h2 class="{{ $heading }}">{{ __('diwan.profile.password') }}</h2>
                    <p class="{{ $note }}">{{ __('diwan.profile.password_note') }}</p>

                    <form method="POST" action="{{ route('password.update') }}" class="grid gap-7 max-w-md">
                        @csrf
                        @method('PUT')
                        <x-ui.field name="current_password" type="password" bag="updatePassword" :label="__('diwan.profile.current')" autocomplete="current-password" dir="ltr" />
                        <x-ui.field name="password" type="password" bag="updatePassword" :label="__('diwan.profile.new')" autocomplete="new-password" dir="ltr" id="new-password" />
                        <x-ui.field name="password_confirmation" type="password" :label="__('diwan.profile.confirm')" autocomplete="new-password" dir="ltr" />
                        <div><x-ui.button type="submit">{{ __('diwan.profile.save') }}</x-ui.button></div>
                    </form>
                </section>

                {{-- ══ Orders ══════════════════════════════════════════════════ --}}
                <section id="orders" class="{{ $sectionClass }}">
                    <div class="flex flex-wrap items-baseline justify-between gap-4 mb-6">
                        <h2 class="{{ $heading }}">{{ __('diwan.profile.orders') }}</h2>
                        @if($orders->isNotEmpty())
                            <x-ui.link :href="route('orders.index')">{{ __('diwan.orders.back') }}</x-ui.link>
                        @endif
                    </div>

                    @forelse($orders as $order)
                        <a href="{{ route('orders.show', $order) }}"
                           class="group flex flex-wrap items-center gap-x-6 gap-y-2 py-4 border-0 border-b border-rule text-ink no-underline hover:bg-paper-2 px-2 -mx-2">
                            <span class="font-mono text-accent-ink" dir="ltr">{{ $order->number() }}</span>
                            <span class="text-xs text-muted">{{ $order->created_at->translatedFormat('j F Y') }}</span>
                            <x-ui.chip :tone="$tone($order->status)">{{ __('diwan.orders.status_values.'.$order->status) }}</x-ui.chip>
                            <span class="ms-auto flex items-center gap-3 font-mono text-sm">
                                @money($order->total_price)
                                <x-icon name="chevron-right" class="size-3.5 text-muted" />
                            </span>
                        </a>
                    @empty
                        <p class="m-0 text-sm text-muted">{{ __('diwan.profile.no_orders') }}</p>
                    @endforelse
                </section>

                {{-- ══ Close the account ═══════════════════════════════════════
                     Irreversible, so it asks for the password rather than an OK. --}}
                <section id="close" class="{{ $sectionClass }} border-b-0">
                    <h2 class="{{ $heading }} text-danger">{{ __('diwan.profile.danger') }}</h2>
                    <p class="{{ $note }}">{{ __('diwan.profile.danger_note') }}</p>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="grid gap-7 max-w-md p-6 border border-danger">
                        @csrf
                        @method('DELETE')
                        <x-ui.field name="password" type="password" bag="userDeletion" :label="__('diwan.auth.password')" required dir="ltr" id="delete-password" />
                        <div>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 min-h-12 px-7 border-0 rounded-control bg-danger text-paper font-label text-xs font-medium uppercase tracking-label cursor-pointer hover:opacity-90">
                                <x-icon name="trash" class="size-4" />{{ __('diwan.profile.delete') }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </x-ui.page>
@endsection
