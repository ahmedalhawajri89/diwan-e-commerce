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
        <x-admin.completeness :product="$product" />
        <x-ui.link :href="route('furnish.product.show', $product->slug)">{{ __('admin.products.view_shop') }}</x-ui.link>
        <x-ui.link :href="route('admin.products.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <x-admin.stats>
        <x-admin.stat :label="__('admin.products.status')" :value="__('admin.products.status_values.'.$product->status)" />
        <x-admin.stat :label="__('admin.products.stock')" :value="$product->totalStock()" :flag="$product->totalStock() <= \App\Support\Overview::LOW_STOCK" />
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
    {{-- ══ Stock movements ═══════════════════════════════════════════════
         Every sale, return and hand change, with what was left after it.
         The figure at the head opens the same editor as the list. --}}
    <section aria-labelledby="stockTitle" class="mt-14">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h2 id="stockTitle" class="m-0 font-display text-2xl">{{ __('admin.stock.history') }}</h2>
            <span class="inline-flex items-center gap-3 text-sm text-muted">
                {{ __('admin.products.stock') }}: <x-admin.stock-cell :product="$product" />
            </span>
        </div>

        @if($movements->isNotEmpty())
            <x-admin.table>
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.stock.when') }}</th>
                        <th scope="col">{{ __('admin.stock.colour') }}</th>
                        <th scope="col">{{ __('admin.stock.change') }}</th>
                        <th scope="col">{{ __('admin.stock.left') }}</th>
                        <th scope="col">{{ __('admin.stock.why') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $move)
                        @php
                            $signed = $move->signed();
                            $number = $move->reference_id ? '#'.str_pad((string) $move->reference_id, 6, '0', STR_PAD_LEFT) : null;
                        @endphp
                        <tr>
                            <td class="font-mono text-xs text-muted whitespace-nowrap" title="{{ $move->created_at->format('Y-m-d H:i') }}">{{ $move->created_at->diffForHumans() }}</td>
                            <td>{{ $move->variant?->colourLabel() ?? __('admin.stock.only_colour') }}</td>
                            <td class="font-mono {{ $signed > 0 ? 'text-ok' : 'text-danger' }}" dir="ltr">{{ $signed > 0 ? '+' : '−' }}{{ abs($signed) }}</td>
                            <td class="font-mono">{{ $move->stock_after ?? '—' }}</td>
                            <td class="text-sm">
                                @if(in_array($move->source_type, ['order', 'cancel'], true) && $move->reference_id)
                                    <a href="{{ route('admin.orders.show', $move->reference_id) }}" class="text-accent-ink underline underline-offset-4">{{ __('admin.stock.source.'.$move->source_type, ['number' => $number]) }}</a>
                                @else
                                    {{ __('admin.stock.source.'.$move->source_type, ['number' => $number]) }}
                                @endif
                                @if($move->user && ! in_array($move->source_type, ['order'], true))
                                    <span class="text-muted">{{ __('admin.stock.by', ['name' => $move->user->name]) }}</span>
                                @endif
                                @if($move->note)
                                    <span class="block text-xs text-muted">{{ $move->note }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.table>
        @else
            <x-ui.empty>{{ __('admin.stock.history_empty') }}</x-ui.empty>
        @endif
    </section>
</x-app-layout>
