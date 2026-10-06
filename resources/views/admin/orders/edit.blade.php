{{--
  The two selects list exactly what the column accepts. Before the enum was
  aligned, "processing" and "refunded" passed validation here and were then
  rejected by the database.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.update') }}</x-slot>

    <x-admin.head :title="__('admin.orders.update')" :intro="'#'.str_pad($order->id, 6, '0', STR_PAD_LEFT)">
        <x-ui.link :href="route('admin.orders.show', $order->id)" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <form method="POST" action="{{ route('admin.orders.update', $order->id) }}" class="grid gap-7 max-w-md" x-data="submitOnce" @submit="lock">
        @csrf
        @method('PUT')

        <x-ui.select name="status" :label="__('admin.orders.status')" required>
            @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ __('admin.orders.status_values.'.$status) }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.select name="payment_status" :label="__('admin.orders.payment')" required>
            @foreach(['pending', 'paid', 'failed', 'refunded'] as $status)
                <option value="{{ $status }}" @selected(old('payment_status', $order->payment_status) === $status)>{{ __('admin.orders.payment_values.'.$status) }}</option>
            @endforeach
        </x-ui.select>

        <div class="flex flex-wrap gap-3 mt-3">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.orders.save') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.orders.show', $order->id)">{{ __('admin.action.cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
