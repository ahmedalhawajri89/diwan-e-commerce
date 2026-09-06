{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md

  This route used to be registered against a method that rendered a view nobody
  had written, so it threw on every request. It was narrowed out while the panel
  was rebuilt; here it is, with something to show.

  The storefront page is for buying; this is for checking. It answers the two
  questions an operator actually has — are its measurements on file, and has
  anybody bought it — which the edit form cannot.
--}}
<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>

    @php $variant = $product->primaryVariant(); @endphp

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ $product->name }}</h1>
            <p class="hm-app__intro hm-num">{{ $product->slug }}</p>
        </div>

        <div class="d-flex gap-3 align-items-center">
            <a href="{{ route('admin.products.edit', $product) }}" class="hm-btn hm-btn--ghost">
                <x-icon name="pencil" />
                {{ __('admin.action.edit') }}
            </a>
            <a href="{{ route('furnish.product.show', $product->slug) }}" class="hm-link hm-link--sm">
                {{ __('admin.products.view_shop') }}
                <x-icon name="arrow-right" />
            </a>
            <a href="{{ route('admin.products.index') }}" class="hm-link hm-link--sm">
                <x-icon name="arrow-left" />
                {{ __('admin.action.back') }}
            </a>
        </div>
    </div>

    <dl class="hm-stats">
        <div class="hm-stat">
            <dt>{{ __('admin.products.status') }}</dt>
            <dd class="hm-t-lg">{{ $product->status }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.products.stock') }}</dt>
            <dd>{{ $variant?->stock ?? '—' }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.products.sold') }}</dt>
            <dd>{{ (int) $sold }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.products.revenue') }}</dt>
            <dd>{{ __('diwan.common.currency') }}{{ number_format((float) $revenue, 2) }}</dd>
        </div>
        {{-- The one number the operator can act on: the fit system is blind
             without it, and the shop then says so on every card. --}}
        <div class="hm-stat {{ $variant?->hasDimensions() ? '' : 'hm-stat--flag' }}">
            <dt>{{ __('admin.products.size') }}</dt>
            <dd class="hm-t-lg">
                {{ $variant?->hasDimensions() ? __('admin.products.measured') : __('admin.products.unmeasured') }}
            </dd>
        </div>
    </dl>

    <div class="row g-5">
        <div class="col-lg-7">
            <div class="hm-head">
                <h2 class="hm-head__title hm-t-lg">{{ __('admin.products.images') }}</h2>
            </div>

            @if($product->images->isNotEmpty())
                <div class="d-flex flex-wrap gap-3">
                    @foreach($product->images as $image)
                        <figure class="hm-m-0">
                            <x-picture class="hm-thumb hm-thumb--xl" :src="$image->url" alt="" sizes="8.75rem" :width="140" :height="140" loading="lazy" />
                            @if($image->is_primary)
                                <figcaption><span class="hm-chip hm-chip--info">{{ __('admin.products.primary') }}</span></figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="hm-muted hm-t-sm">{{ __('admin.products.no_images') }}</p>
            @endif

            <div class="hm-head hm-mt-2xl">
                <h2 class="hm-head__title hm-t-lg">{{ __('admin.products.reviews') }}</h2>
                <a href="{{ route('admin.reviews.index') }}" class="hm-head__aside hm-link hm-link--sm">
                    {{ __('admin.reviews.title') }}
                    <x-icon name="arrow-right" />
                </a>
            </div>

            @forelse($product->reviews as $review)
                <div class="hm-listrow hm-listrow--sm">
                    <div class="d-flex gap-2 align-items-baseline hm-mb-2xs">
                        <span class="hm-num hm-t-xs">
                            {{ str_repeat('★', $review->rating) }}<span class="hm-faint">{{ str_repeat('★', 5 - $review->rating) }}</span>
                        </span>
                        <span class="hm-muted hm-t-xs">{{ $review->user?->name }}</span>
                        <span class="hm-chip {{ $review->status === 'approved' ? 'hm-chip--ok' : ($review->status === 'rejected' ? 'hm-chip--danger' : 'hm-chip--warn') }}">
                            {{ __('admin.reviews.values.' . $review->status) }}
                        </span>
                    </div>
                    <p class="mb-0 hm-t-sm">{{ $review->comment }}</p>
                </div>
            @empty
                <p class="hm-muted hm-t-sm">{{ __('admin.products.no_reviews') }}</p>
            @endforelse
        </div>

        <div class="col-lg-5">
            <h2 class="hm-label hm-mb-sm">{{ __('admin.products.variants') }}</h2>

            <table class="hm-table">
                <tbody>
                    <tr>
                        <th scope="row">{{ __('admin.products.category') }}</th>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('admin.products.price') }}</th>
                        <td class="hm-num">
                            @if($variant)
                                {{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}
                            @else
                                <span class="hm-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('admin.products.colour') }}</th>
                        <td>{{ $variant?->color ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('admin.products.size') }}</th>
                        <td>
                            @if($variant?->width_cm && $variant?->depth_cm)
                                <span class="hm-num">
                                    {{ $variant->width_cm }}&times;{{ $variant->depth_cm }}@if($variant->height_cm)&times;{{ $variant->height_cm }}@endif
                                    {{ __('diwan.room.unit') }}
                                </span>
                            @else
                                <span class="hm-chip hm-chip--warn">{{ __('admin.products.unmeasured') }}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- The drawing the shopper sees, so the operator can check it
                 looks right before the piece goes live. --}}
            @if($variant?->hasDimensions())
                <div class="hm-mt-lg">
                    <x-scale-figure :variant="$variant" :name="$product->name" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
