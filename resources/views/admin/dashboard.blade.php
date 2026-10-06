{{--
  The overview: a row of real figures, a note when pieces are missing their
  measurements (the fit system is only as good as that column), and the most
  recent orders as a list.
--}}
@php
    $tone = fn ($status) => ['pending' => 'warn', 'cancelled' => 'danger'][$status] ?? 'ok';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.dashboard.title') }}</x-slot>

    <x-admin.head :title="__('admin.dashboard.title')" :intro="__('admin.dashboard.intro')" />

    <x-admin.stats>
        <x-admin.stat :label="__('admin.dashboard.products')" :value="$stats['total_products']" />
        <x-admin.stat :label="__('admin.dashboard.orders')" :value="$stats['total_orders']" />
        <x-admin.stat :label="__('admin.dashboard.pending')" :value="$stats['pending_orders']" :flag="$stats['pending_orders'] > 0" />
        <x-admin.stat :label="__('admin.dashboard.customers')" :value="$stats['total_users']" />
        <x-admin.stat :label="__('admin.dashboard.unmeasured')" :value="$stats['unmeasured']" :flag="$stats['unmeasured'] > 0" />
    </x-admin.stats>

    @if($stats['unmeasured'] > 0)
        <x-ui.note icon="rulers" class="mb-10">
            {{ __('admin.dashboard.unmeasured_note') }}
            <x-ui.link :href="route('admin.products.index')" class="ms-1">{{ __('admin.dashboard.fix_them') }}</x-ui.link>
        </x-ui.note>
    @endif

    <div class="flex flex-wrap items-baseline justify-between gap-4 mb-4">
        <h2 class="m-0 font-display text-2xl">{{ __('admin.dashboard.recent') }}</h2>
        <x-ui.link :href="route('admin.orders.index')">{{ __('admin.orders.title') }}</x-ui.link>
    </div>

    @if($stats['recent_orders']->isNotEmpty())
        <x-admin.table>
            <tbody>
                @foreach($stats['recent_orders'] as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                        <td>{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                        <td><x-ui.chip :tone="$tone($order->status)">{{ __('admin.orders.status_values.'.$order->status) }}</x-ui.chip></td>
                        <td class="font-mono text-end">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</td>
                        <td class="font-mono text-xs text-muted text-end whitespace-nowrap">{{ $order->created_at->translatedFormat('j M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-ui.empty>{{ __('admin.dashboard.no_orders') }}</x-ui.empty>
    @endif
</x-app-layout>
