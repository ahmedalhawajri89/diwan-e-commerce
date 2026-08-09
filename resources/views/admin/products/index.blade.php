<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
                {{ __('Products') }}
            </h2>
            <a href="{{ route('admin.products.create') }}" class="btn-modern btn-primary-modern text-decoration-none text-sm px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i> Add New Product
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Product</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4">Stock</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($products as $product)
                            @php
                                $variant = $product->variants->first();
                                $primaryImage = $product->images->where('is_primary', true)->first();
                            @endphp
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-muted">#{{ $product->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                            @if($primaryImage)
                                                <img src="{{ str_starts_with($primaryImage->image_url, 'http') ? $primaryImage->image_url : asset('storage/' . $primaryImage->image_url) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-main">{{ $product->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-muted">{{ $product->category ? $product->category->name : '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-main">${{ $variant ? number_format($variant->price, 2) : '0.00' }}</td>
                                <td class="px-6 py-4 text-muted">{{ $variant ? $variant->stock : 0 }} in stock</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill 
                                        {{ $product->status == 'active' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $product->status == 'inactive' ? 'bg-gray-200 text-gray-700' : '' }}
                                        {{ $product->status == 'draft' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ $product->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this product?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-muted" colspan="7">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-box-seam fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No products found.</p>
                                        <p class="text-sm">Start by adding a new product to your inventory.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
