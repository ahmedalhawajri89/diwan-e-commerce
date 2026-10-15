{{--
  Where an order is: placed, being prepared, on its way, delivered. Each stage
  is done, current, or still ahead. Only the placing has a date, because only
  that moment is recorded; the other stages are not given invented ones.

  A cancelled order is said plainly instead of drawn as a stalled line.

  @param  \App\Models\Order  $order
--}}
@props(['order'])

@php
    use App\Models\Order;

    $at = array_search($order->status, Order::STAGES, true);
@endphp

@if($order->status === 'cancelled')
    <x-ui.note tone="danger" {{ $attributes }}>{{ __('diwan.orders.cancelled_note') }}</x-ui.note>
@else
    <ol {{ $attributes->merge(['class' => 'm-0 p-0 list-none grid gap-0 sm:grid-cols-4']) }} aria-label="{{ __('diwan.orders.timeline') }}">
        @foreach(Order::STAGES as $i => $stage)
            @php $state = $i < $at ? 'done' : ($i === $at ? 'now' : 'ahead'); @endphp
            <li class="relative flex sm:flex-col gap-3 pb-6 sm:pb-0 sm:pe-4 last:pb-0" @if($state === 'now') aria-current="step" @endif>
                {{-- The connecting line: vertical on a phone, along the row on a desk. --}}
                @unless($loop->last)
                    <span aria-hidden="true" class="absolute start-[0.6875rem] top-6 bottom-0 w-px sm:start-6 sm:end-0 sm:top-[0.6875rem] sm:bottom-auto sm:w-auto sm:h-px {{ $i < $at ? 'bg-ink' : 'bg-rule' }}"></span>
                @endunless

                <span class="relative z-10 inline-flex items-center justify-center shrink-0 size-6 rounded-pill border
                             {{ $state === 'done' ? 'bg-ink border-ink text-on-accent' : ($state === 'now' ? 'bg-paper border-ink text-ink' : 'bg-paper border-rule-2 text-muted') }}">
                    @if($state === 'done')
                        <x-icon name="check-lg" class="size-3" />
                    @else
                        <span class="size-2 rounded-pill {{ $state === 'now' ? 'bg-ink' : 'bg-transparent' }}" aria-hidden="true"></span>
                    @endif
                </span>

                <span class="min-w-0">
                    <span class="block text-sm {{ $state === 'ahead' ? 'text-muted' : 'font-medium text-ink' }}">{{ __('diwan.orders.status_values.'.$stage) }}</span>
                    <span class="sr-only">— {{ __('diwan.orders.stage_'.$state) }}</span>
                    @if($i === 0)
                        <span class="block mt-0.5 text-xs text-muted">{{ $order->created_at->translatedFormat('j F Y') }}</span>
                    @endif
                </span>
            </li>
        @endforeach
    </ol>
@endif
