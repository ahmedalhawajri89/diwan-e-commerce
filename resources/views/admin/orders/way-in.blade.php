{{--
  The way into the customer's home, kept with the order as they gave it, and
  each piece's verdict against it (App\Support\Passage, by the piece's data
  today). What the people delivering it need before they set out: whether it
  is the stairs, a hoist, or taking something apart. Then, once delivered,
  what the customer said about it.

  @param  \App\Models\Order  $order  with items.variant loaded
--}}
@php
    $way = $order->route;
    $label = 'm-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted';
    $size = fn (array $sides) => collect($sides)->every(fn ($v) => $v) ? implode('×', $sides) : __('admin.orders.way.unmeasured');
@endphp

<section aria-labelledby="wayInTitle" class="bg-paper border border-rule p-5">
    <h2 id="wayInTitle" class="{{ $label }}">{{ __('admin.orders.way.title') }}</h2>

    @if(! $way)
        <p class="m-0 text-sm text-muted">{{ __('admin.orders.way.none') }}</p>
    @else
        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 m-0 text-sm">
            <dt class="text-muted">{{ __('admin.orders.way.door') }}</dt>
            <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['door']['w'] ?? null, $way['door']['h'] ?? null]) }}</dd>

            <dt class="text-muted">{{ __('admin.orders.way.access') }}</dt>
            <dd class="m-0">{{ __('diwan.route.access_'.$way['access']) }}</dd>

            @if($way['lift'] ?? null)
                <dt class="text-muted">{{ __('admin.orders.way.lift_door') }}</dt>
                <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['lift']['door_w'], $way['lift']['door_h']]) }}</dd>
                <dt class="text-muted">{{ __('admin.orders.way.lift_car') }}</dt>
                <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['lift']['w'], $way['lift']['d'], $way['lift']['h']]) }}</dd>
            @endif

            @if($way['stairs'] ?? null)
                <dt class="text-muted">{{ __('admin.orders.way.stairs') }}</dt>
                <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['stairs']['w'], $way['stairs']['landing']]) }}</dd>
            @endif

            @if($way['inner'] ?? null)
                <dt class="text-muted">{{ __('admin.orders.way.inner') }}</dt>
                <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['inner']['w'], $way['inner']['landing']]) }}</dd>
            @endif

            @if($way['balcony'] ?? null)
                <dt class="text-muted">{{ __('admin.orders.way.balcony') }}</dt>
                <dd class="m-0 font-mono" dir="ltr">{{ $size([$way['balcony']['w'], $way['balcony']['h']]) }}</dd>
            @endif
        </dl>
        <p class="m-0 mt-2 text-xs text-muted">{{ __('admin.orders.way.unit') }}</p>

        <ul class="m-0 mt-4 pt-4 p-0 list-none grid gap-2.5 border-0 border-t border-rule">
            @foreach($order->items as $item)
                <li class="flex flex-wrap items-center justify-between gap-2">
                    <span class="min-w-0 text-sm truncate">{{ $item->product_name_snapshot }}</span>
                    <x-passage-badge :variant="$item->variant" :way-in="$way" class="border border-rule" />
                </li>
            @endforeach
        </ul>
    @endif

    @if($order->delivery_feedback)
        <p class="flex items-center gap-2 m-0 mt-4 pt-4 border-0 border-t border-rule text-sm">
            <x-icon :name="$order->delivery_feedback === 'easy' ? 'check-lg' : 'exclamation-triangle'"
                    class="size-4 {{ $order->delivery_feedback === 'easy' ? 'text-ok' : 'text-warn' }}" />
            {{ __('admin.orders.way.said', ['answer' => __('admin.orders.feedback.'.$order->delivery_feedback)]) }}
        </p>
    @endif
</section>
