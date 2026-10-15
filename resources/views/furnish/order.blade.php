{{--
  One order, readable at any time rather than only in the seconds after buying.

  Names come from the order's own snapshots, so a piece renamed or delisted
  later cannot rewrite what somebody bought. The link back to the product is
  offered only when the product is still there; when it is gone the page says
  so instead of dangling a dead link.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.orders.number').' '.$order->number())

@php
    $tone = ['cancelled' => 'danger', 'delivered' => 'ok'][$order->status] ?? 'warn';
    $label = 'm-0 font-label text-xs font-medium uppercase tracking-label text-muted';
    $section = 'mt-12 pt-10 border-0 border-t border-rule';
@endphp

@section('content')
    <x-ui.page class="max-w-4xl pb-(--section-gap)">
        <nav class="pt-8" aria-label="{{ __('diwan.orders.back') }}">
            <a href="{{ route('orders.index') }}" class="group inline-flex items-center gap-2 text-sm text-muted no-underline hover:text-ink">
                <x-icon name="arrow-left" class="size-3.5 transition-transform group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
                {{ __('diwan.orders.back') }}
            </a>
        </nav>

        <x-ui.page-head :title="$order->number()" :lede="$order->created_at->translatedFormat('j F Y')" class="pt-6!">
            <x-slot:aside>
                <span class="flex flex-wrap items-center gap-3">
                    <x-ui.chip :tone="$tone">{{ __('diwan.orders.status_values.'.$order->status) }}</x-ui.chip>
                    @if($order->status !== 'cancelled')
                        {{-- The tax invoice once it is issued; the receipt until then. --}}
                        <a href="{{ route('orders.invoice', $order) }}" target="_blank" data-no-prefetch
                           class="inline-flex items-center gap-1.5 min-h-10 text-sm text-ink underline underline-offset-4 hover:text-accent-ink">
                            <x-icon name="receipt" class="size-4" />{{ $order->invoiced_at ? __('diwan.invoice.open_tax') : __('diwan.invoice.open_receipt') }}
                        </a>
                    @endif
                </span>
            </x-slot:aside>
        </x-ui.page-head>

        <x-order-timeline :order="$order" class="mt-10" />

        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-px m-0 mt-10 bg-rule border border-rule">
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.status') }}</dt>
                <dd class="m-0 mt-2 font-display text-2xl">{{ __('diwan.orders.status_values.'.$order->status) }}</dd>
            </div>
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.payment') }}</dt>
                <dd class="m-0 mt-2 font-display text-2xl">{{ __('diwan.orders.payment_values.'.$order->payment_status) }}</dd>
            </div>
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.total') }}</dt>
                <dd class="m-0 mt-2 font-mono text-2xl text-accent-ink">@money($order->total_price)</dd>
            </div>
        </dl>

        {{-- Once it has arrived, one question: did it get in? Asked once; the
             answer tells the shop whether its way-in check is right. --}}
        @if($order->status === 'delivered')
            <section class="mt-6 p-5 bg-paper-2" aria-labelledby="feedbackTitle">
                @if($order->delivery_feedback)
                    <p id="feedbackTitle" class="flex items-center gap-2 m-0 text-sm text-ink">
                        <x-icon name="check-lg" class="size-4 text-ok" />{{ __('diwan.orders.feedback.thanks') }}
                    </p>
                @else
                    <h2 id="feedbackTitle" class="m-0 font-display text-xl">{{ __('diwan.orders.feedback.question') }}</h2>
                    <form method="POST" action="{{ route('orders.feedback', $order) }}" class="flex flex-wrap gap-2 mt-4" x-data="submitOnce" @submit="lock">
                        @csrf
                        @foreach($order::FEEDBACK as $answer)
                            <button type="submit" name="answer" value="{{ $answer }}" :disabled="busy"
                                    class="inline-flex items-center min-h-11 px-4 rounded-pill border border-rule-2 bg-paper text-sm text-ink cursor-pointer transition-colors hover:border-ink disabled:opacity-60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                {{ __('diwan.orders.feedback.'.$answer) }}
                            </button>
                        @endforeach
                    </form>
                @endif
            </section>
        @endif

        {{-- Sending it back: a request with its reason while the shop's return
             period runs; "contact us" when the shop has set none; the request's
             state once asked. Nothing at all before it is delivered. --}}
        @php $window = App\Support\Returns::window($order); @endphp
        @if($window['state'] === App\Support\Returns::ASKED)
            <p class="flex flex-wrap items-center gap-2 m-0 mt-4 p-4 border border-rule text-sm">
                <x-icon name="arrow-return-left" class="size-4 text-accent-ink" />
                {{ __('diwan.returns.asked', ['reason' => __('diwan.returns.reasons.'.$order->returnRequest->reason)]) }}
                <x-ui.chip :tone="['approved' => 'ok', 'received' => 'ok', 'rejected' => 'danger'][$order->returnRequest->status] ?? 'warn'">{{ __('diwan.returns.status.'.$order->returnRequest->status) }}</x-ui.chip>
            </p>
        @elseif($window['state'] === App\Support\Returns::OPEN)
            <details class="group mt-4 border border-rule">
                <summary class="flex items-center justify-between gap-3 min-h-12 px-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden text-sm">
                    <span class="flex items-center gap-2"><x-icon name="arrow-return-left" class="size-4 text-accent-ink" />{{ __('diwan.returns.ask') }}</span>
                    <span class="text-xs text-muted">{{ __('diwan.returns.until', ['date' => $window['until']->translatedFormat('j F')]) }}</span>
                </summary>
                <form method="POST" action="{{ route('orders.return', $order) }}" class="grid gap-5 p-4 pt-1 motion-safe:group-open:animate-unfold" x-data="submitOnce" @submit="lock">
                    @csrf
                    <fieldset class="m-0 p-0 border-0">
                        <legend class="block mb-3 text-sm font-medium">{{ __('diwan.returns.why') }}</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach(App\Support\Returns::REASONS as $reason)
                                <label class="inline-flex items-center min-h-10 px-3.5 border border-rule-2 rounded-pill text-sm cursor-pointer transition-colors hover:border-ink has-checked:border-ink has-checked:bg-ink has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus">
                                    <input type="radio" name="reason" value="{{ $reason }}" required @checked(old('reason') === $reason) class="sr-only">
                                    {{ __('diwan.returns.reasons.'.$reason) }}
                                </label>
                            @endforeach
                        </div>
                        @error('reason')<p class="m-0 mt-2 text-sm text-danger">{{ $message }}</p>@enderror
                    </fieldset>
                    <x-ui.field name="note" type="textarea" :label="__('diwan.returns.note')" :value="old('note')" rows="2" maxlength="1000" dir="auto" />
                    <div><x-ui.button type="submit" ::disabled="busy">{{ __('diwan.returns.send') }}</x-ui.button></div>
                </form>
            </details>
        @elseif($window['state'] === App\Support\Returns::CONTACT)
            <p class="m-0 mt-4 text-sm text-muted">
                {{ __('diwan.returns.contact') }}
                <a href="{{ route('furnish.contact') }}" class="text-ink underline underline-offset-4">{{ __('diwan.returns.contact_link') }}</a>
            </p>
        @elseif($window['state'] === App\Support\Returns::CLOSED)
            <p class="m-0 mt-4 text-sm text-muted">{{ __('diwan.returns.closed', ['date' => $window['until']->translatedFormat('j F')]) }}</p>
        @endif

        {{-- ══ What was bought ═════════════════════════════════════════════ --}}
        <section class="{{ $section }}">
            <div class="flex items-baseline justify-between gap-4 mb-4">
                <h2 class="m-0 font-display text-2xl">{{ __('diwan.orders.items') }}</h2>
                <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}</span>
            </div>

            <ul class="m-0 p-0 list-none">
                @foreach($order->items as $item)
                    <li class="flex items-center gap-5 py-4 border-0 border-b border-rule">
                        <span class="relative block w-16 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                            <x-picture :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')" alt=""
                                       sizes="4rem" :width="128" :height="160" loading="lazy"
                                       class="absolute inset-0 size-full object-cover" />
                        </span>

                        <span class="flex-1 min-w-0">
                            {{-- The snapshot is the truth; the live product is only a link. --}}
                            <span class="block font-display text-lg">{{ $item->product_name_snapshot }}</span>
                            @if($item->product)
                                <a href="{{ route('furnish.product.show', $item->product->slug) }}" class="text-xs text-accent-ink underline underline-offset-4">{{ __('diwan.orders.again') }}</a>
                            @else
                                <span class="text-xs text-muted">{{ __('diwan.orders.gone') }}</span>
                            @endif
                        </span>

                        <bdi dir="ltr" class="font-mono text-sm text-muted">&times;{{ $item->quantity }}</bdi>
                        <span class="font-mono text-sm min-w-24 text-end">@money($item->price * $item->quantity)</span>
                    </li>
                @endforeach
            </ul>

            <div class="flex justify-between gap-4 pt-5 text-base">
                <span>{{ __('diwan.orders.total') }}</span>
                <span class="font-mono text-lg">@money($order->total_price)</span>
            </div>
            <x-vat-line :amount="$order->total_price" :rate="$order->vat_rate" :vat="$order->vat_amount" :order="true" class="mt-1" />
        </section>

        {{-- ══ Where it went ═══════════════════════════════════════════════ --}}
        <section class="{{ $section }}">
            <h2 class="m-0 mb-4 font-display text-2xl">{{ __('diwan.orders.delivery') }}</h2>

            @if($order->address)
                <address class="not-italic max-w-sm p-5 bg-paper-2 text-sm leading-body">
                    {{ $order->address->street }}<br>
                    {{ $order->address->city }}<br>
                    {{ $order->address->country }}

                    @if($order->address->phone)
                        <span class="block mt-3 pt-3 border-0 border-t border-rule font-mono" dir="ltr">{{ $order->address->phone }}</span>
                    @endif
                </address>
            @else
                <p class="m-0 text-sm text-muted">{{ __('diwan.orders.no_address') }}</p>
            @endif

            <x-delivery-summary :order="$order" class="max-w-sm mt-4" />

            @if($order->notes)
                <p class="m-0 mt-6 mb-2 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('diwan.orders.note') }}</p>
                <p class="m-0 max-w-sm p-5 bg-paper-2 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]" dir="auto">{{ $order->notes }}</p>
            @endif
        </section>
    </x-ui.page>
@endsection
