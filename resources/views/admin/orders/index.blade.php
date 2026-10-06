@php
    $statusTone = fn ($s) => ['pending' => 'warn', 'cancelled' => 'danger'][$s] ?? 'ok';
    $paymentTone = fn ($s) => ['paid' => 'ok', 'failed' => 'danger'][$s] ?? 'neutral';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.title') }}</x-slot>

    <x-admin.head :title="__('admin.orders.title')" :intro="__('admin.orders.intro')" />

    @if($status)
        <x-ui.note icon="funnel" class="mb-6">
            {{ __('admin.filter.showing') }} <strong class="font-medium">{{ __('admin.orders.status_values.'.$status) }}</strong>
            <x-ui.link :href="route('admin.orders.index')" class="ms-1">{{ __('admin.filter.clear') }}</x-ui.link>
        </x-ui.note>
    @endif

    @if($orders->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.orders.number') }}</th>
                    <th scope="col">{{ __('admin.orders.customer') }}</th>
                    <th scope="col">{{ __('admin.orders.total') }}</th>
                    <th scope="col">{{ __('admin.orders.status') }}</th>
                    <th scope="col">{{ __('admin.orders.payment') }}</th>
                    <th scope="col">{{ __('admin.orders.placed') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.orders.view') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $order->number() }}</a></td>
                        <td>{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                        <td class="font-mono">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</td>
                        <td><x-ui.chip :tone="$statusTone($order->status)">{{ __('admin.orders.status_values.'.$order->status) }}</x-ui.chip></td>
                        <td><x-ui.chip :tone="$paymentTone($order->payment_status)">{{ __('admin.orders.payment_values.'.$order->payment_status) }}</x-ui.chip></td>
                        <td class="font-mono text-xs text-muted whitespace-nowrap">{{ $order->created_at->translatedFormat('j M Y') }}</td>
                        <td>
                            <x-admin.row-actions :name="'#'.$order->id" :edit="route('admin.orders.edit', $order->id)">
                                <x-ui.button variant="quiet" :href="route('admin.orders.show', $order->id)" aria-label="{{ __('admin.orders.view') }} {{ $order->number() }}">
                                    <x-icon name="eye" class="size-4" />
                                </x-ui.button>
                            </x-admin.row-actions>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $orders->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.orders.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
