{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.edit') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.products.edit') }}</h1>
            <p class="hm-app__intro">{{ $product->name }}</p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <a href="{{ route('furnish.product.show', $product->slug) }}" class="hm-link hm-link--sm">
                {{ __('admin.orders.view') }}
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('admin.products.index') }}" class="hm-link hm-link--sm">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('admin.action.back') }}
            </a>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="hm-field @error('name') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.name') }}</span>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
                            @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="hm-field @error('slug') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.slug') }}</span>
                            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" required maxlength="255" dir="ltr">
                            @error('slug')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="hm-field @error('category_id') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.category') }}</span>
                            <select name="category_id" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="hm-field @error('status') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.status') }}</span>
                            <select name="status" required>
                                @foreach(['active', 'inactive', 'draft'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('status')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <div class="col-12">
                        <label class="hm-field @error('description') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.description') }}</span>
                            <textarea name="description" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <div class="col-md-4">
                        <label class="hm-field @error('price') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.price') }}</span>
                            <input type="number" name="price" value="{{ old('price', $variant?->price) }}"
                                   step="0.01" min="0" required dir="ltr">
                            @error('price')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="hm-field @error('stock') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.stock') }}</span>
                            <input type="number" name="stock" value="{{ old('stock', $variant?->stock ?? 0) }}"
                                   min="0" required dir="ltr">
                            @error('stock')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="hm-field @error('color') hm-field--error @enderror">
                            <span class="hm-field__label">{{ __('admin.products.colour') }}</span>
                            <input type="text" name="color" value="{{ old('color', $variant?->color) }}" maxlength="50">
                            @error('color')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                </div>

                <fieldset style="margin-top: var(--space-lg); padding-top: var(--space-md); border-top: var(--rule-hair) solid var(--color-rule);">
                    <legend class="hm-label" style="padding: 0;">{{ __('admin.products.size') }}</legend>
                    <p class="hm-field__hint" style="margin-bottom: var(--space-sm);">{{ __('admin.products.dimensions_hint') }}</p>

                    <div class="row g-3">
                        @foreach(['width_cm' => 'width', 'depth_cm' => 'depth', 'height_cm' => 'height'] as $field => $label)
                            <div class="col-md-4">
                                <label class="hm-field @error($field) hm-field--error @enderror" style="margin-bottom: 0;">
                                    <span class="hm-field__label">
                                        {{ __('admin.products.' . $label) }}
                                        <span class="hm-muted">({{ __('diwan.room.unit') }})</span>
                                    </span>
                                    <input type="number" name="{{ $field }}" value="{{ old($field, $variant?->{$field}) }}"
                                           min="1" max="2000" inputmode="numeric" dir="ltr">
                                    @error($field)<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div style="margin-top: var(--space-lg); padding-top: var(--space-md); border-top: var(--rule-hair) solid var(--color-rule);">
                    <label class="hm-field">
                        <span class="hm-field__label">{{ __('admin.products.add_images') }}</span>
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif">
                        <span class="hm-field__hint">JPEG · PNG · WebP · GIF — 2 MB</span>
                    </label>
                </div>

                <div class="d-flex gap-2" style="margin-top: var(--space-lg);">
                    <button type="submit" class="hm-btn hm-btn--primary">{{ __('admin.action.save') }}</button>
                    <a href="{{ route('admin.products.index') }}" class="hm-btn hm-btn--ghost">{{ __('admin.action.cancel') }}</a>
                </div>
            </form>
        </div>

        {{-- ══ Existing images ═══════════════════════════════════════════ --}}
        <div class="col-lg-4">
            <h2 class="hm-label" style="margin-bottom: var(--space-sm);">{{ __('admin.products.images') }}</h2>

            @forelse($product->images as $image)
                <div class="d-flex gap-3 align-items-center"
                     style="padding-block: var(--space-sm); border-bottom: var(--rule-hair) solid var(--color-rule);">
                    <img src="{{ $image->url }}" alt="" class="hm-thumb" loading="lazy" width="44" height="44">
                    <span style="flex: 1 1 auto; min-width: 0;">
                        @if($image->is_primary)
                            <span class="hm-chip hm-chip--info">{{ __('admin.products.primary') }}</span>
                        @endif
                    </span>
                    <button type="button" class="hm-btn hm-btn--quiet js-delete-image"
                            data-url="{{ route('admin.products.images.destroy', $image->id) }}"
                            style="color: var(--color-danger);"
                            aria-label="{{ __('admin.products.delete_image') }}">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </div>
            @empty
                <p class="hm-muted" style="font-size: var(--text-sm);">—</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script>
            // The image delete endpoint answers JSON, so the row is removed in
            // place rather than reloading the whole form and losing edits.
            document.querySelectorAll('.js-delete-image').forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('div');
                    fetch(this.dataset.url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                        .then(r => r.json())
                        .then(data => { if (data.success) row.remove(); })
                        .catch(() => {});
                });
            });
        </script>
    @endpush
</x-app-layout>
