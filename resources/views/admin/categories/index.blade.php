<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
                {{ __('Categories') }}
            </h2>
            <a href="{{ route('admin.categories.create') }}" class="btn-modern btn-primary-modern text-decoration-none text-sm px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i> Add New Category
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
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Parent</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($categories as $category)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 text-muted">#{{ $category->id }}</td>
                                <td class="px-6 py-4 font-bold text-main">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-muted">{{ $category->slug }}</td>
                                <td class="px-6 py-4 text-muted">
                                    @if($category->parent)
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">{{ $category->parent->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this category?">
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
                                <td class="px-6 py-12 text-center text-muted" colspan="5">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-tags fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No categories found.</p>
                                        <p class="text-sm">Create categories to organize your products.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
