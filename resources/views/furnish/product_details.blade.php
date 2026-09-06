{{--
  Hallmark · genre: modern-minimal · macrostructure: 19 Map / Diagram · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  This is the one page where the fit system is the layout rather than a badge on
  it: the scale diagram sits beside the buy panel, above the fold, not buried in
  a specs accordion.
--}}
@extends('layouts.furnish')

@section('title', $product->name)
@section('description', Str::limit(strip_tags($product->description), 160))
@section('og_image', $product->imageUrl())
@section('og_type', 'product')

@section('content')

    @php
        $variant = $product->primaryVariant();
        $price = $variant?->price;
        $images = $product->images;
        $reviews = $product->reviews;
        $avg = $reviews->count() ? round($reviews->avg('rating'), 1) : null;
    @endphp

    <section class="hm-section--flush">
        <div class="hm-page">
            <nav class="hm-mb-lg" aria-label="{{ __('diwan.common.back') }}">
                <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                    <x-icon name="arrow-left" />
                    {{ __('diwan.product.back') }}
                </a>
            </nav>

            <div class="row g-5">
                {{-- ══ Gallery ═══════════════════════════════════════════ --}}
                <div class="col-lg-7">
                    <figure class="hm-m-0">
                        {{-- Deliberately a plain <img>. The gallery swaps this
                             element's `src` when a thumbnail is pressed, and a
                             <source> set would keep winning over the swap. One
                             photograph pays full price so the gallery works. --}}
                        <img class="hm-media hm-media--4x3" id="heroImage" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                             fetchpriority="high" decoding="async" width="1200" height="900">
                    </figure>

                    @if($images->count() > 1)
                        <div class="d-flex gap-2 flex-wrap hm-mt-sm">
                            @foreach($images as $image)
                                <button type="button" class="hm-util hm-util--thumb"
                                        data-hero-src="{{ $image->url }}"
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-label="{{ $product->name }} — {{ $loop->iteration }}">
                                    <x-picture class="hm-media hm-media--fill" :src="$image->url" alt=""
                                               sizes="4.5rem" loading="lazy" />
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- ══ The diagram — the reason this page exists ═════ --}}
                    <div class="hm-mt-xl">
                        <h2 class="hm-label hm-mb-sm">{{ __('diwan.scale.human') }}</h2>
                        <x-scale-figure :variant="$variant" :name="$product->name" />
                    </div>
                </div>

                {{-- ══ Buy panel ═════════════════════════════════════════ --}}
                <div class="col-lg-5">
                    @if($product->category)
                        <p class="hm-mb-2xs">
                            <a href="{{ route('furnish.category', $product->category->slug) }}" class="hm-label">
                                {{ $product->category->name }}
                            </a>
                        </p>
                    @endif

                    <h1 class="hm-t-3xl hm-track-display hm-m-0 hm-mt-0 hm-mb-sm">
                        {{ $product->name }}
                    </h1>

                    <p class="hm-num hm-t-xl hm-mb-md">
                        @if($price !== null)
                            {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
                        @else
                            <span class="hm-muted hm-face-body hm-t-md">
                                {{ __('diwan.product.no_price') }}
                            </span>
                        @endif
                    </p>

                    {{-- Fit, stated with its reasoning rather than as a bare label. --}}
                    <div class="hm-well hm-mb-lg">
                        <p class="hm-label hm-mb-xs">{{ __('diwan.fit.legend') }}</p>
                        <x-fit-badge :variant="$variant" :room="$room" :withNote="true" />
                    </div>

                    <x-scale-rule :variant="$variant" />

                    @if($variant && $variant->stock !== null)
                        <p class="hm-m-0 hm-mt-md hm-mb-0 hm-t-sm">
                            @if($variant->stock <= 0)
                                <span class="hm-danger">
                                    <x-icon name="x-lg" /> {{ __('diwan.product.out_of_stock') }}
                                </span>
                            @elseif($variant->stock <= 5)
                                <span class="hm-warn">
                                    <x-icon name="exclamation-triangle" />
                                    {{ __('diwan.product.low_stock', ['count' => $variant->stock]) }}
                                </span>
                            @else
                                <span class="hm-muted">{{ __('diwan.product.in_stock', ['count' => $variant->stock]) }}</span>
                            @endif
                        </p>
                    @endif

                    <div class="hm-mt-lg">
                        @auth
                            <form method="POST" action="{{ route('cart.add') }}" class="d-flex gap-2 align-items-end flex-wrap">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <label class="hm-field hm-m-0 hm-field--qty">
                                    <span class="hm-field__label">{{ __('diwan.product.quantity') }}</span>
                                    <input type="number" name="quantity" value="1" min="1"
                                           max="{{ $variant?->stock ?: 99 }}" inputmode="numeric" dir="ltr">
                                </label>

                                <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg hm-fill-lg"
                                        @disabled($variant && $variant->stock !== null && $variant->stock <= 0)>
                                    {{ __('diwan.product.add_to_bag') }}
                                </button>

                                <button type="button" class="hm-btn hm-btn--ghost toggle-wishlist"
                                        data-product-id="{{ $product->id }}" aria-pressed="false"
                                        aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
                                    <x-icon name="bookmark" />
                                    {{ __('diwan.product.save') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="hm-btn hm-btn--primary hm-btn--lg hm-btn--block">
                                {{ __('diwan.product.sign_in_first') }}
                            </a>
                        @endauth
                    </div>

                    {{-- ══ Specification — real columns only ═════════════ --}}
                    <div class="hm-mt-xl">
                        <h2 class="hm-label hm-mb-xs">{{ __('diwan.product.specs') }}</h2>
                        <table class="hm-table">
                            <tbody>
                                @if($variant?->width_cm)
                                    <tr>
                                        <th scope="row">{{ __('diwan.scale.label') }}</th>
                                        <td class="hm-num">
                                            {{ $variant->width_cm }} &times; {{ $variant->depth_cm }}@if($variant->height_cm) &times; {{ $variant->height_cm }}@endif
                                            {{ __('diwan.room.unit') }}
                                        </td>
                                    </tr>
                                    @if($variant->footprint())
                                        <tr>
                                            <th scope="row">{{ __('diwan.fit.footprint') }}</th>
                                            <td class="hm-num">{{ number_format($variant->footprint(), 2) }} m&sup2;</td>
                                        </tr>
                                    @endif
                                @else
                                    <tr>
                                        <th scope="row">{{ __('diwan.scale.label') }}</th>
                                        <td class="hm-muted">{{ __('diwan.scale.not_recorded') }}</td>
                                    </tr>
                                @endif

                                @if($variant?->color)
                                    <tr>
                                        <th scope="row">{{ __('diwan.product.colour') }}</th>
                                        <td>{{ $variant->color }}</td>
                                    </tr>
                                @endif

                                @if($product->category)
                                    <tr>
                                        <th scope="row">{{ __('diwan.product.category') }}</th>
                                        <td>{{ $product->category->name }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th scope="row">{{ __('diwan.product.sku') }}</th>
                                    <td class="hm-num">{{ $product->slug }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($product->description)
                        <div class="hm-mt-xl">
                            <h2 class="hm-label hm-mb-xs">{{ __('diwan.product.description') }}</h2>
                            <p class="hm-measure hm-t-sm hm-ink-2">
                                {{ $product->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Reviews — real rows only ════════════════════════════════════ --}}
    <section class="hm-section--tight hm-ruled-t">
        <div class="hm-page">
            <div class="hm-head">
                <h2 class="hm-head__title">{{ __('diwan.product.reviews') }}</h2>
                @if($avg)
                    <span class="hm-head__aside hm-num">
                        {{ $avg }} / 5 · {{ __('diwan.product.review_count', ['count' => $reviews->count()]) }}
                    </span>
                @endif
            </div>

            <div class="row g-5">
                <div class="col-lg-7">
                    @forelse($reviews as $review)
                        <article class="hm-listrow">
                            <p class="hm-num hm-muted hm-t-xs hm-mb-2xs"
                               aria-label="{{ __('diwan.testimonials.rating', ['rating' => $review->rating]) }}">
                                {{ str_repeat('★', $review->rating) }}<span class="hm-faint">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </p>
                            <p class="hm-t-sm hm-mb-2xs">{{ $review->comment }}</p>
                            <p class="hm-muted mb-0 hm-t-xs">
                                {{ $review->user?->name }} · {{ $review->created_at->translatedFormat('j F Y') }}
                            </p>
                        </article>
                    @empty
                        <p class="hm-muted">{{ __('diwan.product.no_reviews') }}</p>
                    @endforelse
                </div>

                <div class="col-lg-5">
                    @auth
                        <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="hm-panel">
                            @csrf
                            <h3 class="hm-t-lg hm-m-0 hm-mt-0 hm-mb-md">{{ __('diwan.product.write_review') }}</h3>

                            <label class="hm-field">
                                <span class="hm-field__label">{{ __('diwan.product.your_rating') }}</span>
                                <select name="rating" required>
                                    @for($n = 5; $n >= 1; $n--)
                                        <option value="{{ $n }}">{{ $n }} / 5</option>
                                    @endfor
                                </select>
                            </label>

                            <label class="hm-field">
                                <span class="hm-field__label">{{ __('diwan.product.your_review') }}</span>
                                <textarea name="comment" required maxlength="1000"></textarea>
                            </label>

                            <button type="submit" class="hm-btn hm-btn--ghost hm-btn--block">
                                {{ __('diwan.product.submit_review') }}
                            </button>
                        </form>
                    @else
                        <p class="hm-note">
                            <x-icon name="person" />
                            <a href="{{ route('login') }}" class="hm-link hm-link--sm">{{ __('diwan.product.sign_in_review') }}</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="hm-section--tight hm-ruled-t">
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.product.related') }}</h2>
                </div>
                <div class="hm-grid">
                    @foreach($related as $index => $item)
                        <x-product-card :product="$item" :room="$room" :i="$index" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
