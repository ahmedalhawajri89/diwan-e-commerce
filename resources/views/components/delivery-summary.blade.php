{{--
  An order's delivery, in two lines: what it costs (or that it is agreed on
  the call) and when it comes (the booked day and part of it, or that the
  shop calls to agree one). The same on the customer's order, the thank-you
  page, the panel and the packing slip.

  @param  \App\Models\Order  $order
--}}
@props(['order'])

@php
    $row = 'flex justify-between gap-4';
@endphp

<dl {{ $attributes->merge(['class' => 'grid gap-1.5 m-0 text-sm']) }}>
    <div class="{{ $row }}">
        <dt class="text-muted">{{ __('diwan.delivery.fee') }}</dt>
        <dd class="m-0 {{ $order->delivery_fee !== null ? 'font-mono' : '' }}">
            @if($order->delivery_fee !== null)@money($order->delivery_fee)@else{{ __('diwan.bag.shipping_on_contact') }}@endif
        </dd>
    </div>
    <div class="{{ $row }}">
        <dt class="text-muted">{{ __('diwan.delivery.when') }}</dt>
        <dd class="m-0 text-end">
            @if($order->delivery_date)
                @if($order->delivery_window)
                    {{ __('diwan.delivery.booked', ['day' => $order->delivery_date->translatedFormat('l j F'), 'window' => __('diwan.checkout.windows.'.$order->delivery_window)]) }}
                @else
                    {{ $order->delivery_date->translatedFormat('l j F') }}
                @endif
            @else
                {{ __('diwan.delivery.to_agree') }}
            @endif
        </dd>
    </div>
</dl>
