{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.orders.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.orders.intro') }}</p>
        </div>
    </div>

    @if($orders->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.orders.number') }}</th>
                        <th scope="col">{{ __('admin.orders.customer') }}</th>
                        <th scope="col">{{ __('admin.orders.total') }}</th>
                        <th scope="col">{{ __('admin.orders.status') }}</th>
                        <th scope="col">{{ __('admin.orders.payment') }}</th>
                        <th scope="col">{{ __('admin.orders.placed') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.orders.view') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="hm-num hm-strong">
                                    #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                            <td class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</td>
                            <td>
                                <span class="hm-chip {{ $order->status === 'pending' ? 'hm-chip--warn' : ($order->status === 'cancelled' ? 'hm-chip--danger' : 'hm-chip--ok') }}">
                                    {{ __('admin.orders.status_values.' . $order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="hm-chip {{ $order->payment_status === 'paid' ? 'hm-chip--ok' : ($order->payment_status === 'failed' ? 'hm-chip--danger' : '') }}">
                                    {{ __('admin.orders.payment_values.' . $order->payment_status) }}
                                </span>
                            </td>
                            <td class="hm-num hm-muted hm-t-xs">
                                {{ $order->created_at->translatedFormat('j M Y') }}
                            </td>
                            <td>
                                <div class="hm-actions">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="hm-btn hm-btn--quiet"
                                       aria-label="{{ __('admin.orders.view') }} #{{ $order->id }}">
                                        <x-icon name="eye" />
                                    </a>
                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="hm-btn hm-btn--quiet"
                                       aria-label="{{ __('admin.orders.edit') }} #{{ $order->id }}">
                                        <x-icon name="pencil" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="hm-mt-xl">{{ $orders->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.orders.empty') }}</p>
    @endif
</x-app-layout>
