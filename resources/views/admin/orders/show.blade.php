{{--
  Reads the order's own snapshots. A product renamed or deleted after the fact
  must not rewrite what somebody bought.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.show') }}</x-slot>

    <x-admin.head :title="'#'.str_pad($order->id, 6, '0', STR_PAD_LEFT)"
                  :intro="($order->user?->name ?? __('admin.orders.guest')).' · '.$order->created_at->translatedFormat('j F Y')">
        <x-ui.button variant="ghost" :href="route('admin.orders.edit', $order->id)">
            <x-icon name="pencil" class="size-4" />{{ __('admin.orders.edit') }}
        </x-ui.button>
        <x-ui.link :href="route('admin.orders.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <x-admin.stats class="lg:grid-cols-3!">
        <x-admin.stat :label="__('admin.orders.status')" :value="__('admin.orders.status_values.'.$order->status)" :flag="$order->status === 'pending'" />
        <x-admin.stat :label="__('admin.orders.payment')" :value="__('admin.orders.payment_values.'.$order->payment_status)" />
        <x-admin.stat :label="__('admin.orders.total')" :value="__('diwan.common.currency').number_format($order->total_price, 2)" />
    </x-admin.stats>

    <div class="grid gap-12 lg:grid-cols-[2fr_1fr]">
        <section class="min-w-0">
            <div class="flex items-baseline justify-between gap-4 mb-4">
                <h2 class="m-0 font-display text-2xl">{{ __('admin.orders.items') }}</h2>
                <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}</span>
            </div>

            <x-admin.table>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="font-medium">{{ $item->product_name_snapshot }}</td>
                            <td class="font-mono text-muted" dir="ltr">&times;{{ $item->quantity }}</td>
                            <td class="font-mono text-end">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.table>
        </section>

        <aside class="grid gap-8 content-start">
            @if($order->address)
            <div>
                <h2 class="m-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.orders.address') }}</h2>
                <address class="not-italic p-5 bg-paper-2 text-sm leading-body">
                    {{ $order->address->street }}<br>
                    {{ $order->address->city }}<br>
                    {{ $order->address->country }}

                    @if($order->address->phone)
                        <a href="tel:{{ $order->address->phone }}" dir="ltr"
                           class="block mt-3 pt-3 border-0 border-t border-rule font-mono text-ink">{{ $order->address->phone }}</a>
                    @endif
                </address>
            </div>
            @else
                {{-- Said, rather than an aside that silently is not there. --}}
                <p class="m-0 p-5 bg-paper-2 text-sm text-muted">{{ __('admin.orders.no_address') }}</p>
            @endif

            {{-- What the customer asked the courier to know. Read before calling. --}}
            @if($order->notes)
                <div>
                    <h2 class="m-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.orders.notes') }}</h2>
                    <p class="m-0 p-5 bg-paper-2 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]" dir="auto">{{ $order->notes }}</p>
                </div>
            @endif
        </aside>
    </div>
</x-app-layout>
