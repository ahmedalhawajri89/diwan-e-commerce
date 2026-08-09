<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">Order ID</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Payment</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($orders as $order)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-main">#{{ $order->id }}</td>
                                <td class="px-6 py-4 text-muted">{{ $order->user ? $order->user->name : 'N/A' }}</td>
                                <td class="px-6 py-4 font-semibold text-main">${{ number_format($order->total_price, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill 
                                        {{ $order->status === 'completed' || $order->status === 'delivered' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $order->status === 'pending' || $order->status === 'processing' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill 
                                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $order->payment_status === 'failed' || $order->payment_status === 'refunded' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-muted text-xs">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Update">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-muted" colspan="7">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-cart-x fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No orders found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
