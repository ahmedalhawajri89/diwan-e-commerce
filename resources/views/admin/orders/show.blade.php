{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  Reads the order's own snapshots. A product renamed or deleted after the fact
  must not rewrite what somebody bought.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.show') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title hm-num">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h1>
            <p class="hm-app__intro">
                {{ $order->user?->name ?? __('admin.orders.guest') }} ·
                <span class="hm-num">{{ $order->created_at->translatedFormat('j F Y') }}</span>
            </p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="hm-btn hm-btn--ghost">
                <x-icon name="pencil" />
                {{ __('admin.orders.edit') }}
            </a>
            <a href="{{ route('admin.orders.index') }}" class="hm-link hm-link--sm">
                <x-icon name="arrow-left" />
                {{ __('admin.action.back') }}
            </a>
        </div>
    </div>

    <dl class="hm-stats">
        <div class="hm-stat">
            <dt>{{ __('admin.orders.status') }}</dt>
            <dd class="hm-t-lg">{{ __('admin.orders.status_values.' . $order->status) }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.orders.payment') }}</dt>
            <dd class="hm-t-lg">{{ __('admin.orders.payment_values.' . $order->payment_status) }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.orders.total') }}</dt>
            <dd>{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</dd>
        </div>
    </dl>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="hm-head">
                <h2 class="hm-head__title hm-t-lg">{{ __('admin.orders.items') }}</h2>
                <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}</span>
            </div>

            @foreach($order->items as $item)
                <div class="d-flex gap-3 align-items-center hm-listrow hm-listrow--sm">
                    <div class="hm-fill">
                        <p class="hm-strong hm-m-0">{{ $item->product_name_snapshot }}</p>
                        <p class="hm-muted hm-num mb-0 hm-t-xs">&times;{{ $item->quantity }}</p>
                    </div>
                    <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            @if($order->address)
                <h2 class="hm-label hm-mb-sm">{{ __('admin.orders.address') }}</h2>
                <address class="hm-panel hm-roman hm-t-sm">
                    {{ $order->address->street }}<br>
                    {{ $order->address->city }}<br>
                    {{ $order->address->country }}

                    @if($order->address->phone)
                        <span class="d-block hm-num hm-mt-xs hm-pt-xs hm-ruled-t" dir="ltr">
                            <a href="tel:{{ $order->address->phone }}">{{ $order->address->phone }}</a>
                        </span>
                    @endif
                </address>
            @endif
        </div>
    </div>
</x-app-layout>
