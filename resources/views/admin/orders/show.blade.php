<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details #') }}{{ $order->id }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.orders.edit', $order->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Update Status
                </a>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    Back to Orders
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="col-span-1 md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold border-b pb-2 mb-4">Order Items</h3>
                            <table class="w-full whitespace-no-wrap border-collapse">
                                <thead>
                                    <tr class="text-left font-bold border-b text-sm text-gray-600">
                                        <th class="pb-2">Product</th>
                                        <th class="pb-2 text-center">Price</th>
                                        <th class="pb-2 text-center">Qty</th>
                                        <th class="pb-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr class="border-b">
                                        <td class="py-3">{{ $item->product ? $item->product->name : 'Unknown Product' }}</td>
                                        <td class="py-3 text-center">${{ number_format($item->price, 2) }}</td>
                                        <td class="py-3 text-center">{{ $item->quantity }}</td>
                                        <td class="py-3 text-right font-medium">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="py-3 text-right font-bold">Subtotal:</td>
                                        <td class="py-3 text-right font-bold">${{ number_format($order->total_price + $order->discount_amount, 2) }}</td>
                                    </tr>
                                    @if($order->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="py-1 text-right text-red-500">Discount:</td>
                                        <td class="py-1 text-right text-red-500">-${{ number_format($order->discount_amount, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="3" class="py-3 text-right font-bold text-lg">Total:</td>
                                        <td class="py-3 text-right font-bold text-lg">${{ number_format($order->total_price, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold border-b pb-2 mb-4">Customer Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="block text-sm text-gray-500">Name</span>
                                    <span class="font-medium">{{ $order->user ? $order->user->name : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-500">Email</span>
                                    <span class="font-medium">{{ $order->user ? $order->user->email : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold border-b pb-2 mb-4">Order Status</h3>
                            <div class="space-y-4">
                                <div>
                                    <span class="block text-sm text-gray-500 mb-1">Status</span>
                                    <span class="px-3 py-1 text-sm rounded-full font-medium
                                            {{ $order->status === 'completed' || $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'pending' || $order->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-500 mb-1">Payment Status</span>
                                    <span class="px-3 py-1 text-sm rounded-full font-medium
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->payment_status === 'failed' || $order->payment_status === 'refunded' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
