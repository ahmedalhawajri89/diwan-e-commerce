{{--
  The storefront page is for buying; this is for checking. It answers the two
  questions an operator actually has — are its measurements on file, and has
  anybody bought it — which the edit form cannot.
--}}
<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>

    @php
        $variant = $product->primaryVariant();
        $reviewTone = fn ($status) => ['approved' => 'ok', 'rejected' => 'danger'][$status] ?? 'warn';
        $label = 'm-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted';
        $row = 'flex justify-between gap-4 py-3 border-0 border-b border-rule text-sm';
    @endphp

    <x-admin.head :title="$product->name" :intro="$product->slug">
        <x-ui.button variant="ghost" :href="route('admin.products.edit', $product)">
            <x-icon name="pencil" class="size-4" />{{ __('admin.action.edit') }}
        </x-ui.button>
        <x-ui.link :href="route('furnish.product.show', $product->slug)">{{ __('admin.products.view_shop') }}</x-ui.link>
        <x-ui.link :href="route('admin.products.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <x-admin.stats>
        <x-admin.stat :label="__('admin.products.status')" :value="__('admin.products.status_values.'.$product->status)" />
        <x-admin.stat :label="__('admin.products.stock')" :value="$variant?->stock ?? '—'" />
        <x-admin.stat :label="__('admin.products.sold')" :value="(int) $sold" />
        <x-admin.stat :label="__('admin.products.revenue')" :value="__('diwan.common.currency').number_format((float) $revenue, 2)" />
        {{-- The one number the operator can act on: the fit system is blind
             without it, and the shop then says so on every card. --}}
        <x-admin.stat :label="__('admin.products.size')" :flag="! $variant?->hasDimensions()"
                      :value="$variant?->hasDimensions() ? __('admin.products.measured') : __('admin.products.unmeasured')" />
    </x-admin.stats>

    <div class="grid gap-12 lg:grid-cols-[7fr_5fr]">
        <div class="min-w-0">
            <h2 class="{{ $label }}">{{ __('admin.products.images') }}</h2>
            @if($product->images->isNotEmpty())
                <ul class="grid grid-cols-3 sm:grid-cols-4 gap-3 m-0 p-0 list-none">
                    @foreach($product->images as $image)
                        <li class="relative aspect-[4/5] overflow-hidden bg-paper-2">
                            <x-picture :src="$image->url" alt="" sizes="9rem" :width="288" :height="360" loading="lazy"
                                       class="absolute inset-0 size-full object-cover" />
                            @if($image->is_primary)
                                <span class="absolute top-2 start-2"><x-ui.chip tone="ok">{{ __('admin.products.primary') }}</x-ui.chip></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="m-0 text-sm text-muted">{{ __('admin.products.no_images') }}</p>
            @endif

            <div class="flex items-baseline justify-between gap-4 mt-12">
                <h2 class="{{ $label }}">{{ __('admin.products.reviews') }}</h2>
                <x-ui.link :href="route('admin.reviews.index')">{{ __('admin.reviews.title') }}</x-ui.link>
            </div>

            @forelse($product->reviews as $review)
                <article class="py-4 border-0 border-b border-rule">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-star-rating :rating="$review->rating" class="m-0" />
                        <span class="text-xs text-muted">{{ $review->user?->name }}</span>
                        <x-ui.chip :tone="$reviewTone($review->status)">{{ __('admin.reviews.values.'.$review->status) }}</x-ui.chip>
                    </div>
                    <p class="m-0 mt-2 text-sm leading-body">{{ $review->comment }}</p>
                </article>
            @empty
                <p class="m-0 text-sm text-muted">{{ __('admin.products.no_reviews') }}</p>
            @endforelse
        </div>

        <div>
            <h2 class="{{ $label }}">{{ __('admin.products.variants') }}</h2>
            <dl class="m-0 border-0 border-t border-rule">
                <div class="{{ $row }}"><dt class="text-muted">{{ __('admin.products.category') }}</dt><dd class="m-0">{{ $product->category?->name ?? '—' }}</dd></div>
                <div class="{{ $row }}"><dt class="text-muted">{{ __('admin.products.price') }}</dt><dd class="m-0 font-mono">@if($variant){{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}@else—@endif</dd></div>
                <div class="{{ $row }}"><dt class="text-muted">{{ __('admin.products.colour') }}</dt><dd class="m-0">{{ $variant?->color ?? '—' }}</dd></div>
                <div class="{{ $row }}">
                    <dt class="text-muted">{{ __('admin.products.size') }}</dt>
                    <dd class="m-0">
                        @if($variant?->width_cm && $variant?->depth_cm)
                            <span class="font-mono" dir="ltr">{{ $variant->width_cm }}&times;{{ $variant->depth_cm }}@if($variant->height_cm)&times;{{ $variant->height_cm }}@endif {{ __('diwan.room.unit') }}</span>
                        @else
                            <x-ui.chip tone="warn">{{ __('admin.products.unmeasured') }}</x-ui.chip>
                        @endif
                    </dd>
                </div>
            </dl>

            {{-- The drawing the shopper sees, so the operator can check it looks
                 right before the piece goes live. --}}
            @if($variant?->hasDimensions())
                <div class="mt-8"><x-scale-figure :variant="$variant" :name="$product->name" /></div>
            @endif
        </div>
    </div>
</x-app-layout>
