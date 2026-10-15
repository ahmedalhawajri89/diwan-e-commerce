@php
    $statusTone = fn ($s) => ['pending' => 'warn', 'cancelled' => 'danger'][$s] ?? 'ok';
    $paymentTone = fn ($s) => ['paid' => 'ok', 'failed' => 'danger'][$s] ?? 'neutral';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.title') }}</x-slot>

    <x-admin.head :title="__('admin.orders.title')" :intro="__('admin.orders.intro')" />

    <x-admin.toolbar :action="route('admin.orders.index')" :q="$q" :placeholder="__('admin.orders.search')"
                     :chips="$chips" :sorts="$sorts" :keep="['status' => $status, 'sort' => $sort]"
                     :export="route('admin.orders.export', request()->query())" />

    @if($orders->count() > 0)
        <div x-data="bulkSelect" data-selected="{{ __('admin.bulk.selected') }}" data-confirm="{{ __('admin.bulk.confirm_delete') }}">
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col" class="w-px"><input type="checkbox" data-select-all @change="all" class="size-4 accent-ink cursor-pointer" aria-label="{{ __('admin.bulk.select_all') }}"></th>
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
                        <td class="w-px"><input type="checkbox" name="ids[]" value="{{ $order->id }}" form="bulkForm" data-select-row @change="count" class="size-4 accent-ink cursor-pointer" aria-label="{{ __('admin.bulk.select_row', ['name' => $order->number()]) }}"></td>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $order->number() }}</a></td>
                        <td>{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                        <td class="font-mono">@money($order->total_price)</td>
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
        <x-admin.bulk-bar :action="route('admin.orders.bulk')" :options="$bulkOptions" />
        </div>

        <div class="mt-10">{{ $orders->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ $q || $status ? __('admin.list.no_match') : __('admin.orders.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
