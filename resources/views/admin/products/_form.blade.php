{{--
  The product form, shared by create and edit. The dimension fields sit with
  the rest of the product, not in an "advanced" drawer: they are what the shop
  promises the customer, and the fit system reads nothing else.

  @param  \App\Models\Product|null         $product   null when creating
  @param  \App\Models\ProductVariant|null  $variant
  @param  \Illuminate\Support\Collection   $categories
--}}
@inject('drafter', 'App\Support\Ai\DescriptionDrafter')
@php
    $product ??= null;
    $variant ??= null;
    $group = 'grid gap-7 pt-8 mt-8 border-0 border-t border-rule';
    $groupTitle = 'm-0 p-0 font-display text-xl';
@endphp

<div class="grid gap-7 sm:grid-cols-3">
    <div class="sm:col-span-2">
        <x-ui.field name="name" :label="__('admin.products.name')" :value="old('name', $product?->raw('name'))" required maxlength="255" dir="ltr" />
    </div>
    {{-- Raw values, never the locale-aware accessor: see SpeaksArabic. --}}
    <div class="sm:col-span-3">
        <x-ui.field name="name_ar" :label="__('admin.products.name_ar')" :value="old('name_ar', $product?->raw('name_ar'))" maxlength="255" dir="rtl" lang="ar" />
    </div>
    <x-ui.field name="slug" :label="__('admin.products.slug')" :value="old('slug', $product?->slug)" required maxlength="255" dir="ltr" />

    <div class="sm:col-span-3 grid gap-7 sm:grid-cols-2">
        <x-ui.select name="category_id" :label="__('admin.products.category')" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="status" :label="__('admin.products.status')" required>
            @foreach(['active', 'inactive', 'draft'] as $status)
                <option value="{{ $status }}" @selected(old('status', $product?->status ?? 'active') === $status)>{{ __('admin.products.status_values.'.$status) }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- A first draft of both descriptions, from the facts above and, on a
         saved piece, its photograph (descriptionDraft). Shown only when a
         Claude key is configured; the draft lands in the fields, never saved
         by itself. --}}
    @if($drafter->available())
        <div class="sm:col-span-3" x-data="descriptionDraft"
             data-url="{{ route('admin.products.draft') }}" data-product="{{ $product?->id }}"
             data-working="{{ __('admin.draft.working') }}" data-ready="{{ __('admin.draft.ready') }}"
             data-done="{{ __('admin.draft.done') }}" data-failed="{{ __('admin.draft.failed') }}"
             data-need-name="{{ __('admin.draft.need_name') }}">
            <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-accent-wash border-0 border-s-2 border-accent-ink">
                <p class="m-0 flex-1 min-w-56 text-sm leading-body text-neutral">{{ __('admin.draft.intro') }}</p>
                <button type="button" @click="write" :disabled="busy"
                        class="inline-flex items-center gap-2 h-10 px-4 rounded-control border border-accent-ink bg-paper text-sm text-ink cursor-pointer transition-colors hover:bg-accent-ink hover:text-paper disabled:opacity-60 disabled:cursor-wait focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    <x-icon name="stars" class="size-4" />{{ __('admin.draft.button') }}
                </button>
            </div>

            <div x-show="busy" x-cloak class="relative h-1 mt-3 overflow-hidden rounded-pill bg-paper-2 rtl:-scale-x-100" aria-hidden="true">
                <span class="absolute inset-y-0 left-0 w-1/3 rounded-pill bg-accent-ink motion-safe:animate-indeterminate"></span>
            </div>
            <p x-ref="status" role="status" aria-live="polite" class="m-0 mt-2 text-sm text-neutral empty:hidden"></p>

            {{-- When the fields already hold text, the draft waits beside them. --}}
            <div x-show="hasPreview" x-cloak class="grid gap-4 mt-3 p-4 border border-rule bg-paper sm:grid-cols-2">
                <div>
                    <p class="m-0 mb-1 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.products.description') }}</p>
                    <p x-ref="previewEn" class="m-0 text-sm leading-body" dir="ltr"></p>
                </div>
                <div>
                    <p class="m-0 mb-1 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.products.description_ar') }}</p>
                    <p x-ref="previewAr" class="m-0 text-sm leading-body" dir="rtl" lang="ar"></p>
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <button type="button" @click="apply" class="h-9 px-4 rounded-control border-0 bg-ink text-paper text-sm cursor-pointer hover:opacity-90">{{ __('admin.draft.replace') }}</button>
                    <button type="button" @click="discard" class="h-9 px-4 rounded-control border border-rule-2 bg-transparent text-sm text-ink cursor-pointer hover:border-ink">{{ __('admin.draft.discard') }}</button>
                </div>
            </div>
        </div>
    @endif

    <div class="sm:col-span-3">
        <x-ui.field name="description" type="textarea" :label="__('admin.products.description')" :value="old('description', $product?->raw('description'))" required dir="ltr" />
    </div>

    <div class="sm:col-span-3">
        <x-ui.field name="description_ar" type="textarea" :label="__('admin.products.description_ar')" :value="old('description_ar', $product?->raw('description_ar'))" dir="rtl" lang="ar" />
    </div>

    <x-ui.field name="price" type="number" :label="__('admin.products.price')" :value="old('price', $variant?->price)" step="0.01" min="0" required dir="ltr" />
    <x-ui.field name="stock" type="number" :label="__('admin.products.stock')" :value="old('stock', $variant?->stock ?? 0)" min="0" required dir="ltr" />
    <x-ui.field name="color" :label="__('admin.products.colour')" :value="old('color', $variant?->color)" maxlength="50" />
</div>

{{-- ══ Dimensions — the fit system's source data ═══════════════════════════ --}}
<fieldset class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
    <legend class="{{ $groupTitle }} float-start w-full">{{ __('admin.products.size') }}</legend>
    <p class="m-0 -mt-3 text-sm text-muted">{{ __('admin.products.dimensions_hint') }}</p>

    <div class="grid gap-7 sm:grid-cols-3">
        @foreach(['width_cm' => 'width', 'depth_cm' => 'depth', 'height_cm' => 'height'] as $field => $label)
            <x-ui.field :name="$field" type="number" :label="__('admin.products.'.$label).' ('.__('diwan.room.unit').')'"
                        :value="old($field, $variant?->{$field})" min="1" max="2000" inputmode="numeric" dir="ltr" />
        @endforeach
    </div>
</fieldset>

{{-- The photographs: previewed before they are sent, any one taken back, and
     a phone's large original shrunk in the browser first (imageUpload). --}}
<div class="{{ $group }}" x-data="imageUpload"
     data-preparing="{{ __('admin.upload.preparing') }}" data-ready="{{ __('admin.upload.ready') }}"
     data-from="{{ __('admin.upload.from') }}" data-remove="{{ __('admin.upload.remove') }}">
    <label class="block">
        <span class="block mb-3 font-display text-xl">{{ $product ? __('admin.products.add_images') : __('admin.products.images') }}</span>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" x-ref="input" @change="pick"
               class="block w-full text-sm text-muted file:me-4 file:h-10 file:px-4 file:border file:border-rule-2 file:rounded-control file:bg-paper file:text-ink file:text-sm file:cursor-pointer hover:file:border-ink">
        <span class="block mt-2 text-xs text-muted">{{ __('admin.upload.hint') }}</span>
    </label>

    <ul x-ref="list" class="m-0 mt-4 p-0 list-none grid grid-cols-3 sm:grid-cols-5 gap-2 empty:hidden"></ul>
    <p x-ref="status" class="m-0 mt-3 text-sm text-neutral empty:hidden" role="status" aria-live="polite"></p>

    {{-- While the form is on its way: an honest "sending", not a percentage
         the browser does not report for a plain form post. --}}
    <div x-show="uploading" x-cloak class="mt-4" role="status">
        <p class="m-0 mb-2 text-sm text-ink">{{ __('admin.upload.sending') }}</p>
        <div class="relative h-1 overflow-hidden rounded-pill bg-paper-2 rtl:-scale-x-100" aria-hidden="true">
            <span class="absolute inset-y-0 left-0 w-1/3 rounded-pill bg-accent-ink motion-safe:animate-indeterminate"></span>
        </div>
    </div>
</div>
