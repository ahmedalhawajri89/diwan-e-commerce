<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Overview') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Products -->
            <div class="card-modern p-6 flex flex-col justify-center relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="text-muted text-xs font-bold uppercase tracking-wider mb-2">Total Products</div>
                <div class="text-4xl font-extrabold text-main">{{ $stats['total_products'] }}</div>
                <i class="bi bi-box-seam absolute right-6 bottom-6 text-3xl text-primary/20"></i>
            </div>

            <!-- Total Orders -->
            <div class="card-modern p-6 flex flex-col justify-center relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="text-muted text-xs font-bold uppercase tracking-wider mb-2">Total Orders</div>
                <div class="text-4xl font-extrabold text-main">{{ $stats['total_orders'] }}</div>
                <i class="bi bi-cart absolute right-6 bottom-6 text-3xl text-primary/20"></i>
            </div>

            <!-- Pending Orders -->
            <div
                class="card-modern p-6 flex flex-col justify-center relative overflow-hidden group border-l-4 border-l-accent">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-accent/5 rounded-full group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="text-accent text-xs font-bold uppercase tracking-wider mb-2">Pending Orders</div>
                <div class="text-4xl font-extrabold text-main">{{ $stats['pending_orders'] }}</div>
                <i class="bi bi-hourglass-split absolute right-6 bottom-6 text-3xl text-accent/20"></i>
            </div>

            <!-- Total Users -->
            <div class="card-modern p-6 flex flex-col justify-center relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="text-muted text-xs font-bold uppercase tracking-wider mb-2">Total Users</div>
                <div class="text-4xl font-extrabold text-main">{{ $stats['total_users'] }}</div>
                <i class="bi bi-people absolute right-6 bottom-6 text-3xl text-primary/20"></i>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card-modern">
            <div class="px-6 py-5 border-b border-soft flex justify-between items-center">
                <h3 class="text-lg font-bold text-main">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}"
                    class="text-primary text-sm font-semibold hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse">
                    <thead>
                        <tr class="text-left bg-soft/50 text-muted text-xs uppercase tracking-wider font-bold">
                            <th class="px-6 py-4">Order ID</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($stats['recent_orders'] as $order)
                            <tr class="hover:bg-soft/50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-main">#{{ $order->id }}</td>
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($order->user ? $order->user->name : 'G', 0, 1)) }}
                                    </div>
                                    <span
                                        class="font-medium text-main">{{ $order->user ? $order->user->name : 'Guest' }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-main">${{ number_format($order->total_price, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full 
                                            {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted font-medium">
                                    {{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-8 text-center text-muted" colspan="5">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-inbox text-4xl mb-3 opacity-50"></i>
                                        <p>No recent orders found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>