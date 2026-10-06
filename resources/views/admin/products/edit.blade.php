{{--
  Editing a product: the shared form beside the photographs already on file.
  Removing a photograph happens in place (initImageDelete in admin.js), so
  whatever has been typed into the form is not lost.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.edit') }}</x-slot>

    <x-admin.head :title="__('admin.products.edit')" :intro="$product->name">
        <x-ui.link :href="route('furnish.product.show', $product->slug)">{{ __('admin.orders.view') }}</x-ui.link>
        <x-ui.link :href="route('admin.products.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <div class="grid gap-12 lg:grid-cols-[1fr_18rem]">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="min-w-0" x-data="submitOnce" @submit="lock">
            @csrf
            @method('PUT')

            @include('admin.products._form', ['product' => $product, 'variant' => $variant, 'categories' => $categories])

            <div class="flex flex-wrap gap-3 mt-10">
                <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.save') }}</x-ui.button>
                <x-ui.button variant="ghost" :href="route('admin.products.index')">{{ __('admin.action.cancel') }}</x-ui.button>
            </div>
        </form>

        {{-- ══ Photographs on file ═══════════════════════════════════════════ --}}
        <aside>
            <h2 class="m-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.products.images') }}</h2>

            <ul class="grid grid-cols-2 gap-3 m-0 p-0 list-none">
                @forelse($product->images as $image)
                    <li data-image-row class="relative">
                        <span class="relative block aspect-[4/5] overflow-hidden bg-paper-2">
                            <x-picture :src="$image->url" alt="" sizes="9rem" :width="288" :height="360" loading="lazy"
                                       class="absolute inset-0 size-full object-cover" />
                        </span>
                        @if($image->is_primary)
                            <span class="absolute top-2 start-2"><x-ui.chip tone="ok">{{ __('admin.products.primary') }}</x-ui.chip></span>
                        @endif
                        <button type="button" class="js-delete-image absolute top-2 end-2 inline-flex items-center justify-center size-8 p-0 border-0 rounded-pill bg-paper text-danger cursor-pointer hover:bg-danger hover:text-paper"
                                data-url="{{ route('admin.products.images.destroy', $image->id) }}"
                                aria-label="{{ __('admin.products.delete_image') }}">
                            <x-icon name="trash" class="size-3.5" />
                        </button>
                    </li>
                @empty
                    <li class="col-span-2 text-sm text-muted">—</li>
                @endforelse
            </ul>
        </aside>
    </div>
</x-app-layout>
