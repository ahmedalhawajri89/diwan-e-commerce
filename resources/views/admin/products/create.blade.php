{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  The dimension fields sit with the rest of the product, not in an "advanced"
  drawer: they are what the shop promises the customer.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.create') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.products.create') }}</h1>
        </div>
        <a href="{{ route('admin.products.index') }}" class="hm-link hm-link--sm">
            <x-icon name="arrow-left" />
            {{ __('admin.action.back') }}
        </a>
    </div>

    <form class="hm-hold-wide" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-8">
                <label class="hm-field @error('name') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.name') }}</span>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
                    @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>
            <div class="col-md-4">
                <label class="hm-field @error('slug') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.slug') }}</span>
                    <input type="text" name="slug" value="{{ old('slug') }}" required maxlength="255" dir="ltr">
                    @error('slug')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="col-md-6">
                <label class="hm-field @error('category_id') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.category') }}</span>
                    <select name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
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
                            <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="col-12">
                <label class="hm-field @error('description') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.description') }}</span>
                    <textarea name="description" required>{{ old('description') }}</textarea>
                    @error('description')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="col-md-4">
                <label class="hm-field @error('price') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.price') }}</span>
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required dir="ltr">
                    @error('price')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>
            <div class="col-md-4">
                <label class="hm-field @error('stock') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.stock') }}</span>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required dir="ltr">
                    @error('stock')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>
            <div class="col-md-4">
                <label class="hm-field @error('color') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('admin.products.colour') }}</span>
                    <input type="text" name="color" value="{{ old('color') }}" maxlength="50">
                    @error('color')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>
            </div>
        </div>

        {{-- ══ Dimensions — the fit system's source data ═══════════════ --}}
        <fieldset class="hm-mt-lg hm-pt-md hm-ruled-t">
            <legend class="hm-label hm-p-0">{{ __('admin.products.size') }}</legend>
            <p class="hm-field__hint hm-mb-sm">{{ __('admin.products.dimensions_hint') }}</p>

            <div class="row g-3">
                @foreach(['width_cm' => 'width', 'depth_cm' => 'depth', 'height_cm' => 'height'] as $field => $label)
                    <div class="col-md-4">
                        <label class="hm-field @error($field) hm-field--error @enderror hm-mb-0">
                            <span class="hm-field__label">
                                {{ __('admin.products.' . $label) }}
                                <span class="hm-muted">({{ __('diwan.room.unit') }})</span>
                            </span>
                            <input type="number" name="{{ $field }}" value="{{ old($field) }}"
                                   min="1" max="2000" inputmode="numeric" dir="ltr">
                            @error($field)<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <div class="hm-mt-lg hm-pt-md hm-ruled-t">
            <label class="hm-field">
                <span class="hm-field__label">{{ __('admin.products.images') }}</span>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif">
                <span class="hm-field__hint">JPEG · PNG · WebP · GIF — 2 MB</span>
            </label>
        </div>

        <div class="d-flex gap-2 hm-mt-lg">
            <button type="submit" class="hm-btn hm-btn--primary">{{ __('admin.action.create') }}</button>
            <a href="{{ route('admin.products.index') }}" class="hm-btn hm-btn--ghost">{{ __('admin.action.cancel') }}</a>
        </div>
    </form>
</x-app-layout>
