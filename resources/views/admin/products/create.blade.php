<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern p-8">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Name -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="name" :value="__('Product Name')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="name" class="form-control-modern" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Modern Sofa" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Slug -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="slug" :value="__('Slug (URL Friendly)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="slug" class="form-control-modern" type="text" name="slug" :value="old('slug')" required placeholder="e.g. modern-sofa" />
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>

                    <!-- Price -->
                    <div class="col-span-1">
                        <x-input-label for="price" :value="__('Price ($)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="price" class="form-control-modern" type="number" step="0.01" min="0" name="price" :value="old('price')" required placeholder="99.99" />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <!-- Stock -->
                    <div class="col-span-1">
                        <x-input-label for="stock" :value="__('Stock Quantity')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="stock" class="form-control-modern" type="number" min="0" name="stock" :value="old('stock', 10)" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <!-- Color -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="color" :value="__('Color (Optional)')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <x-text-input id="color" class="form-control-modern" type="text" name="color" :value="old('color')" placeholder="e.g. Black, White, Oak" />
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div class="col-span-1">
                        <x-input-label for="category_id" :value="__('Category')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <select id="category_id" name="category_id" class="form-control-modern" required>
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="description" :value="__('Description')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <textarea id="description" name="description" class="form-control-modern" rows="4" required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <!-- Product Images -->
                    <div class="col-span-1 md:col-span-2">
                        <x-input-label for="images" :value="__('Product Images')" class="text-muted text-xs font-bold uppercase tracking-wider mb-2" />
                        <input id="images" class="form-control-modern bg-white p-2" type="file" name="images[]" accept="image/*" multiple />
                        <p class="text-xs text-muted mt-2">You can select multiple images. Recommended size: 800x800 px (Max 2MB per image).</p>
                        <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8 border-t border-soft pt-6">
                    <a href="{{ route('admin.products.index') }}" class="btn-modern btn-outline-modern mr-4 text-decoration-none">
                        Cancel
                    </a>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
