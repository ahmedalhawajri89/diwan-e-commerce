{{--
  An order as work to be done: where it stands on its way, the one thing to
  do next, who to call and where it goes, what is in it, and everything that
  has happened to it, with notes the staff leave for each other.

  The pieces are read from the order's own snapshots. A product renamed or
  deleted after the fact must not rewrite what somebody bought.
--}}
@php
    $money = fn ($v) => \App\Support\Money::format($v);
    $cancelled = $order->status === 'cancelled';
    $next = $order->nextStage();
    $dates = $order->stageDates();
    $at = array_search($order->status, $order::STAGES, true);
    $stageIcon = ['pending' => 'receipt', 'processing' => 'box-seam', 'shipped' => 'truck', 'delivered' => 'house-check'];
    $nextIcon = ['processing' => 'box-seam', 'shipped' => 'truck', 'delivered' => 'house-check'];

    $address = $order->address;
    $addressText = $address ? collect([$order->user?->name, $address->street, $address->city, $address->country, $address->phone])->filter()->implode("\n") : null;

    $card = 'bg-paper border border-rule';
    $label = 'm-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted';
@endphp

<x-app-layout canvas="linen">
    <x-slot name="title">{{ $order->number() }}</x-slot>

    <x-admin.head :title="$order->number()"
                  :intro="($order->user?->name ?? __('admin.orders.guest')).' · '.$order->created_at->translatedFormat(__('admin.format.datetime'))">
        <x-ui.button variant="ghost" :href="route('admin.orders.slip', $order->id)" target="_blank" data-no-prefetch>
            <x-icon name="printer" class="size-4" />{{ __('admin.orders.slip') }}
        </x-ui.button>
        @if($order->status !== 'cancelled')
            <x-ui.button variant="ghost" :href="route('admin.orders.invoice', $order->id)" target="_blank" data-no-prefetch>
                <x-icon name="receipt" class="size-4" />{{ $order->invoiced_at ? __('diwan.invoice.open_tax') : __('diwan.invoice.open_receipt') }}
            </x-ui.button>
        @endif
        <x-ui.link :href="route('admin.orders.edit', $order->id)" :arrow="false" tone="ink">{{ __('admin.orders.edit') }}</x-ui.link>
        <x-ui.link :href="route('admin.orders.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    {{-- ══ Where it stands, and the one thing to do next ═════════════════ --}}
    <section aria-labelledby="stageTitle" class="{{ $card }} p-5 sm:p-6 mb-4">
        <h2 id="stageTitle" class="sr-only">{{ __('admin.orders.stage') }}</h2>

        @if($cancelled)
            <x-ui.note tone="danger" class="mb-5">
                {{ __('admin.orders.was_cancelled') }}
                @if($dates['cancelled']) <span class="font-mono text-xs">· {{ $dates['cancelled']->translatedFormat(__('admin.format.datetime_short')) }}</span>@endif
            </x-ui.note>
        @endif

        {{-- The stages as a track: done in ink, the current one ringed, the
             rest waiting. Each says when it was reached, if it was. --}}
        <ol class="relative grid grid-cols-4 m-0 p-0 list-none {{ $cancelled ? 'opacity-50' : '' }}">
            @foreach($order::STAGES as $i => $stage)
                @php
                    $done = ! $cancelled && $at !== false && $i < $at;
                    $now = ! $cancelled && $at === $i;
                @endphp
                <li class="relative flex flex-col items-center text-center">
                    @if(! $loop->first)
                        <span class="absolute top-5 end-1/2 w-full h-0.5 {{ $done || $now ? 'bg-ink' : 'bg-rule' }}" aria-hidden="true"></span>
                    @endif
                    <span class="relative z-10 inline-flex items-center justify-center size-10 rounded-pill
                                 {{ $done ? 'bg-ink text-paper' : ($now ? 'bg-paper text-ink ring-2 ring-ink ring-offset-4 ring-offset-paper' : 'bg-paper-2 text-muted') }}">
                        <x-icon :name="$done ? 'check-lg' : $stageIcon[$stage]" class="size-4" />
                    </span>
                    <span class="mt-3 text-sm {{ $now ? 'font-medium text-ink' : ($done ? 'text-ink' : 'text-muted') }}">{{ __('admin.orders.status_values.'.$stage) }}</span>
                    <span class="mt-0.5 font-mono text-[0.6875rem] text-muted min-h-4">{{ $dates[$stage]?->translatedFormat(__('admin.format.stage')) }}</span>
                    @if($now)<span class="sr-only">{{ __('admin.orders.current') }}</span>@endif
                </li>
            @endforeach
        </ol>

        @unless($cancelled)
            <div class="flex flex-wrap items-center gap-3 mt-6 pt-5 border-0 border-t border-rule">
                @if($next)
                    <form method="POST" action="{{ route('admin.orders.advance', $order->id) }}" x-data="submitOnce" @submit="lock">
                        @csrf
                        <x-ui.button type="submit" ::disabled="busy">
                            <x-icon :name="$nextIcon[$next]" class="size-4" />{{ __('admin.orders.move_to', ['stage' => __('admin.orders.status_values.'.$next)]) }}
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.chip tone="ok">{{ __('admin.orders.done') }}</x-ui.chip>
                @endif

                @if($order->payment_status !== 'paid')
                    <form method="POST" action="{{ route('admin.orders.paid', $order->id) }}" x-data="submitOnce" @submit="lock">
                        @csrf
                        <x-ui.button type="submit" variant="ghost" ::disabled="busy">
                            <x-icon name="cash-coin" class="size-4" />{{ __('admin.orders.mark_paid') }}
                        </x-ui.button>
                    </form>
                @endif

                {{-- Cancelling returns the stock and tells the customer, so it asks first. --}}
                @if($order->status !== 'delivered')
                    <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}" class="delete-form ms-auto"
                          data-confirm-title="{{ __('admin.orders.cancel_title', ['number' => $order->number()]) }}"
                          data-confirm-message="{{ __('admin.orders.cancel_message') }}">
                        @csrf
                        <span class="inline-flex flex-wrap items-center gap-2">
                            <label for="cancelReason" class="sr-only">{{ __('admin.orders.cancel_reason') }}</label>
                            <select id="cancelReason" name="reason"
                                    class="h-11 ps-3 pe-8 border border-rule-2 rounded-control bg-paper text-sm text-ink cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus">
                                <option value="">{{ __('admin.orders.cancel_reason') }}</option>
                                @foreach($order::CANCEL_REASONS as $reason)
                                    <option value="{{ $reason }}">{{ __('admin.orders.cancel_reasons.'.$reason) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center gap-2 min-h-11 px-3 border-0 bg-transparent text-sm text-danger cursor-pointer hover:underline underline-offset-4">
                                <x-icon name="x-lg" class="size-3.5" />{{ __('admin.orders.cancel') }}
                            </button>
                        </span>
                    </form>
                @endif
            </div>
        @endunless
    </section>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="grid gap-4 content-start min-w-0">
            {{-- ══ What is in it ═══════════════════════════════════════════ --}}
            <section aria-labelledby="itemsTitle" class="{{ $card }} min-w-0">
                <div class="flex items-baseline justify-between gap-4 px-5 sm:px-6 pt-5">
                    <h2 id="itemsTitle" class="m-0 font-display text-xl">{{ __('admin.orders.items') }}</h2>
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}</span>
                </div>
                <ul class="m-0 mt-3 p-0 list-none divide-y divide-rule border-0 border-t border-rule">
                    @foreach($order->items as $item)
                        <li class="flex items-center gap-4 px-5 sm:px-6 py-3">
                            <span class="relative block w-12 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                                @if($item->imageUrl())
                                    <x-picture :src="$item->imageUrl()" alt="" sizes="3rem" :width="96" :height="120" loading="lazy" class="absolute inset-0 size-full object-cover" />
                                @endif
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block font-medium truncate">{{ $item->product_name_snapshot }}</span>
                                @if($item->variant?->colourLabel())
                                    <span class="block text-xs text-muted">{{ $item->variant->colourLabel() }}</span>
                                @endif
                            </span>
                            <span class="hidden sm:inline font-mono text-sm text-muted">{{ $money($item->price) }} <bdi dir="ltr">&times; {{ $item->quantity }}</bdi></span>
                            <span class="sm:hidden font-mono text-sm text-muted" dir="ltr">&times;{{ $item->quantity }}</span>
                            <span class="shrink-0 sm:w-24 font-mono text-end">{{ $money($item->price * $item->quantity) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="grid gap-1 m-0 px-5 sm:px-6 py-4 border-0 border-t border-rule text-sm">
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between"><dt class="text-muted">{{ __('admin.orders.discount') }}</dt><dd class="m-0 font-mono">−{{ $money($order->discount_amount) }}</dd></div>
                    @endif
                    <div class="flex justify-between items-baseline">
                        <dt class="font-medium">{{ __('admin.orders.total') }}</dt>
                        <dd class="m-0 font-mono text-xl">{{ $money($order->total_price) }}</dd>
                    </div>
                    <x-vat-line :amount="$order->total_price" :rate="$order->vat_rate" :vat="$order->vat_amount" :order="true" />
                    <div class="flex justify-between">
                        <dt class="text-muted">{{ __('admin.orders.payment') }}</dt>
                        <dd class="m-0"><x-ui.chip :tone="$order->payment_status === 'paid' ? 'ok' : ($order->payment_status === 'failed' ? 'danger' : 'warn')">{{ __('admin.orders.payment_values.'.$order->payment_status) }}</x-ui.chip></dd>
                    </div>
                </dl>
            </section>

            {{-- ══ What has happened, and notes for the staff ═══════════════ --}}
            <section id="history" aria-labelledby="historyTitle" class="{{ $card }} p-5 sm:p-6">
                <h2 id="historyTitle" class="m-0 mb-5 font-display text-xl">{{ __('admin.orders.history') }}</h2>

                <form method="POST" action="{{ route('admin.orders.notes', $order->id) }}" class="mb-6" x-data="submitOnce" @submit="lock">
                    @csrf
                    <label for="noteBody" class="sr-only">{{ __('admin.orders.note_label') }}</label>
                    <textarea id="noteBody" name="body" rows="2" required maxlength="2000" dir="auto"
                              placeholder="{{ __('admin.orders.note_placeholder') }}"
                              class="block w-full p-3 border border-rule-2 rounded-control bg-paper text-sm leading-body text-ink placeholder:text-muted focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus"></textarea>
                    <div class="flex items-center justify-between gap-3 mt-2">
                        <span class="text-xs text-muted">{{ __('admin.orders.note_private') }}</span>
                        <x-ui.button type="submit" variant="ghost" ::disabled="busy">{{ __('admin.orders.note_add') }}</x-ui.button>
                    </div>
                </form>

                <ol class="relative m-0 p-0 list-none grid gap-5 before:absolute before:top-2 before:bottom-2 before:start-[0.6875rem] before:w-px before:bg-rule">
                    @forelse($order->events->reverse() as $event)
                        @php
                            $dot = match ($event->kind) {
                                'note' => 'bg-accent-ink',
                                'feedback' => $event->to === 'easy' ? 'bg-ok' : 'bg-warn',
                                'return' => $event->to === 'rejected' ? 'bg-rule-2' : 'bg-warn',
                                'payment' => 'bg-ok',
                                'status' => $event->to === 'cancelled' ? 'bg-danger' : 'bg-ink',
                                default => 'bg-rule-2',
                            };
                        @endphp
                        <li class="relative ps-9">
                            <span class="absolute start-1.5 top-1.5 size-2.5 rounded-pill ring-4 ring-paper {{ $dot }}" aria-hidden="true"></span>
                            <p class="m-0 text-sm leading-snug">
                                @switch($event->kind)
                                    @case('placed') {{ __('admin.orders.event.placed') }} @break
                                    @case('status') {{ __('admin.orders.event.status', ['to' => __('admin.orders.status_values.'.$event->to)]) }} @break
                                    @case('payment') {{ __('admin.orders.event.payment', ['to' => __('admin.orders.payment_values.'.$event->to)]) }} @break
                                    @case('note') <span class="font-medium">{{ __('admin.orders.event.note') }}</span> @break
                                    @case('feedback') {{ __('admin.orders.event.feedback', ['answer' => __('admin.orders.feedback.'.$event->to)]) }} @break
                                    @case('return') {{ __('admin.orders.event.return_'.$event->to, ['reason' => $event->body ? __('diwan.returns.reasons.'.$event->body) : '']) }} @break
                                @endswitch
                                @if($event->kind === 'status' && $event->to === 'cancelled' && $event->body)
                                    <span class="text-muted">· {{ __('admin.orders.cancel_reasons.'.$event->body) }}</span>
                                @endif
                            </p>
                            @if($event->kind === 'note')
                                <p class="m-0 mt-1.5 p-3 bg-paper-2 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]" dir="auto">{{ $event->body }}</p>
                            @endif
                            <p class="m-0 mt-1 font-mono text-xs text-muted">
                                <span title="{{ $event->created_at->format('Y-m-d H:i') }}">{{ $event->created_at->diffForHumans() }}</span>
                                @if($event->user && $event->kind !== 'placed') · {{ $event->user->name }}@endif
                            </p>
                        </li>
                    @empty
                        <li class="relative ps-9 text-sm text-muted">
                            <span class="absolute start-1.5 top-1.5 size-2.5 rounded-pill ring-4 ring-paper bg-rule-2" aria-hidden="true"></span>
                            {{ __('admin.orders.event.placed') }}
                            <span class="block mt-1 font-mono text-xs">{{ $order->created_at->diffForHumans() }}</span>
                        </li>
                    @endforelse
                </ol>
            </section>
        </div>

        {{-- ══ Who, and where it goes ═══════════════════════════════════════ --}}
        <aside class="grid gap-4 content-start">
            <section aria-labelledby="customerTitle" class="{{ $card }} p-5">
                <h2 id="customerTitle" class="{{ $label }}">{{ __('admin.orders.customer') }}</h2>
                <p class="m-0 font-medium">
                    @if($order->user)
                        <a href="{{ route('admin.users.show', $order->user) }}" class="text-ink no-underline hover:underline underline-offset-4">{{ $order->user->name }}</a>
                    @else
                        {{ __('admin.orders.guest') }}
                    @endif
                </p>
                @if($order->user?->email)
                    <a href="mailto:{{ $order->user->email }}" class="block mt-1 font-mono text-xs text-muted no-underline hover:underline" dir="ltr">{{ $order->user->email }}</a>
                @endif

                @if($address?->phone)
                    {{-- The confirming call is the day's first job: the number is a button. --}}
                    <a href="tel:{{ $address->phone }}"
                       class="flex items-center justify-center gap-2 mt-4 min-h-11 px-4 bg-ink text-paper text-sm no-underline transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                        <x-icon name="telephone" class="size-4" />{{ __('admin.orders.call') }} <bdi dir="ltr" class="font-mono">{{ $address->phone }}</bdi>
                    </a>
                @endif
            </section>

            <section aria-labelledby="addressTitle" class="{{ $card }} p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="addressTitle" class="{{ $label }} mb-0">{{ __('admin.orders.address') }}</h2>
                    @if($addressText)
                        <button type="button" x-data="copyText" @click="copy" data-text="{{ $addressText }}"
                                data-copied="{{ __('admin.orders.address_copied') }}" data-failed="{{ __('admin.orders.copy_failed') }}"
                                class="inline-flex items-center gap-1.5 min-h-9 px-2 border-0 bg-transparent text-xs text-accent-ink cursor-pointer hover:underline underline-offset-4">
                            <x-icon name="clipboard" class="size-3.5" />{{ __('admin.orders.copy_address') }}
                        </button>
                    @endif
                </div>
                @if($address)
                    <address class="not-italic mt-3 text-sm leading-body" dir="auto">
                        {{ $address->street }}<br>{{ $address->city }}<br>{{ $address->country }}
                    </address>
                @else
                    <p class="m-0 mt-3 text-sm text-muted">{{ __('admin.orders.no_address') }}</p>
                @endif
                <x-delivery-summary :order="$order" class="mt-4 pt-4 border-0 border-t border-rule" />
            </section>

            {{-- The way into the customer's home, as they gave it when ordering,
                 and what that means for each piece: read before setting out. --}}
            @include('admin.orders.way-in', ['order' => $order])

            {{-- What the customer asked the courier to know. Read before calling. --}}
            @if($order->notes)
                <section aria-labelledby="customerNoteTitle" class="{{ $card }} p-5">
                    <h2 id="customerNoteTitle" class="{{ $label }}">{{ __('admin.orders.notes') }}</h2>
                    <p class="m-0 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]" dir="auto">{{ $order->notes }}</p>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
