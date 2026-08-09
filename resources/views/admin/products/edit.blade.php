<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Edit Product') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern p-8">
            <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Name -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="name" :value="__('Product Name')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="name" class="form-control-modern" type="text" name="name" :value="old('name', $product->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Slug -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="slug" :value="__('Slug (URL Friendly)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="slug" class="form-control-modern" type="text" name="slug" :value="old('slug', $product->slug)" required />
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>

                    <!-- Price -->
                    <div class="col-span-1">
                        <x-input-label for="price" :value="__('Price ($)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="price" class="form-control-modern" type="number" step="0.01" min="0" name="price" :value="old('price', $variant ? $variant->price : '')" required />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <!-- Stock -->
                    <div class="col-span-1">
                        <x-input-label for="stock" :value="__('Stock Quantity')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="stock" class="form-control-modern" type="number" min="0" name="stock" :value="old('stock', $variant ? $variant->stock : 10)" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <!-- Color -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="color" :value="__('Color (Optional)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="color" class="form-control-modern" type="text" name="color" :value="old('color', $variant ? $variant->color : '')" />
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div class="col-span-1">
                        <x-input-label for="category_id" :value="__('Category')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <select id="category_id" name="category_id" class="form-control-modern" required>
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div class="col-span-1">
                        <x-input-label for="status" :value="__('Status')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <select id="status" name="status" class="form-control-modern" required>
                            <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="description" :value="__('Description')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <textarea id="description" name="description" class="form-control-modern" rows="4" required>{{ old('description', $product->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <!-- Existing Images -->
                    <div class="col-span-1 md:col-span-2 mb-4">
                        <x-input-label :value="__('Existing Images')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        @if($product->images->count() > 0)
                            <div class="flex flex-wrap gap-4 mt-2">
                                @foreach($product->images as $image)
                                    <div class="relative group rounded-xl overflow-hidden border border-soft shadow-sm" style="width: 120px; height: 120px;" id="image-container-{{ $image->id }}">
                                        <img src="{{ str_starts_with($image->image_url, 'http') ? $image->image_url : asset('storage/' . $image->image_url) }}" class="w-full h-full object-cover">
                                        @if($image->is_primary)
                                            <span class="absolute top-1 left-1 bg-main text-white text-[10px] font-bold px-2 py-0.5 rounded-full z-10">Primary</span>
                                        @endif
                                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" class="bg-red-500 hover:bg-red-600 text-white rounded-full p-2 transition-transform transform hover:scale-110" onclick="deleteImage({{ $image->id }})" title="Delete Image">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-muted italic">No images uploaded yet.</p>
                        @endif
                    </div>

                    <!-- Upload New Images -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="images" :value="__('Add New Images')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <input id="images" class="form-control-modern bg-white p-2" type="file" name="images[]" accept="image/*" multiple />
                        <p class="text-xs text-muted mt-2">You can select multiple images to add. Recommended size: 800x800 px (Max 2MB per image).</p>
                        <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8 border-t border-soft pt-6">
                    <a href="{{ route('admin.products.index') }}" class="btn-modern btn-outline-modern mr-4 text-decoration-none">
                        Cancel
                    </a>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX Script for Image Deletion -->
    <script>
        function deleteImage(imageId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this image?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'btn-modern btn-primary-modern bg-red-500 hover:bg-red-600 border-0',
                    cancelButton: 'btn-modern btn-outline-modern'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/product-images/${imageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const container = document.getElementById(`image-container-${imageId}`);
                            container.style.transition = "all 0.3s ease";
                            container.style.opacity = "0";
                            container.style.transform = "scale(0.8)";
                            setTimeout(() => container.remove(), 300);
                            
                            if (typeof flasher !== 'undefined') {
                                flasher.success('Image deleted successfully');
                            }
                        } else {
                            Swal.fire('Error!', 'Error deleting image.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Error deleting image.', 'error');
                    });
                }
            });
        }
    </script>
</x-app-layout>
