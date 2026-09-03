{{--
  Hallmark · genre: modern-minimal · macrostructure: 19 Map / Diagram · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  This is the one page where the fit system is the layout rather than a badge on
  it: the scale diagram sits beside the buy panel, above the fold, not buried in
  a specs accordion.
--}}
@extends('layouts.furnish')

@section('title', $product->name)

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
            <nav aria-label="{{ __('diwan.common.back') }}" style="margin-bottom: var(--space-lg);">
                <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    {{ __('diwan.product.back') }}
                </a>
            </nav>

            <div class="row g-5">
                {{-- ══ Gallery ═══════════════════════════════════════════ --}}
                <div class="col-lg-7">
                    <figure style="margin: 0;">
                        <img id="heroImage" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                             style="width: 100%; aspect-ratio: 4/3; object-fit: cover; background: var(--color-paper-3); border-radius: var(--radius-surface);"
                             fetchpriority="high" decoding="async" width="1200" height="900">
                    </figure>

                    @if($images->count() > 1)
                        <div class="d-flex gap-2 flex-wrap" style="margin-top: var(--space-sm);">
                            @foreach($images as $image)
                                <button type="button" class="hm-util" style="width: 4.5rem; height: 4.5rem; padding: 0; overflow: hidden; border: var(--rule-hair) solid var(--color-rule-2); border-radius: var(--radius-control);"
                                        onclick="document.getElementById('heroImage').src = this.dataset.src"
                                        data-src="{{ $image->url }}"
                                        aria-label="{{ $product->name }} — {{ $loop->iteration }}">
                                    <img src="{{ $image->url }}" alt="" loading="lazy"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- ══ The diagram — the reason this page exists ═════ --}}
                    <div style="margin-top: var(--space-xl);">
                        <h2 class="hm-label" style="margin-bottom: var(--space-sm);">{{ __('diwan.scale.human') }}</h2>
                        <x-scale-figure :variant="$variant" />
                    </div>
                </div>

                {{-- ══ Buy panel ═════════════════════════════════════════ --}}
                <div class="col-lg-5">
                    @if($product->category)
                        <p class="hm-label" style="margin-bottom: var(--space-2xs);">{{ $product->category->name }}</p>
                    @endif

                    <h1 style="font-size: var(--text-3xl); letter-spacing: var(--display-track); margin: 0 0 var(--space-sm);">
                        {{ $product->name }}
                    </h1>

                    <p class="hm-num" style="font-size: var(--text-xl); margin-bottom: var(--space-md);">
                        @if($price !== null)
                            {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
                        @else
                            <span class="hm-muted" style="font-family: var(--font-body); font-size: var(--text-md);">
                                {{ __('diwan.product.no_price') }}
                            </span>
                        @endif
                    </p>

                    {{-- Fit, stated with its reasoning rather than as a bare label. --}}
                    <div style="margin-bottom: var(--space-lg); padding: var(--space-md); background: var(--color-paper-2); border: var(--rule-hair) solid var(--color-rule); border-radius: var(--radius-surface);">
                        <p class="hm-label" style="margin-bottom: var(--space-xs);">{{ __('diwan.fit.legend') }}</p>
                        <x-fit-badge :variant="$variant" :room="$room" :withNote="true" />
                    </div>

                    <x-scale-rule :variant="$variant" />

                    @if($variant && $variant->stock !== null)
                        <p style="margin: var(--space-md) 0 0; font-size: var(--text-sm);">
                            @if($variant->stock <= 0)
                                <span style="color: var(--color-danger);">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i> {{ __('diwan.product.out_of_stock') }}
                                </span>
                            @elseif($variant->stock <= 5)
                                <span style="color: var(--color-warn);">
                                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                    {{ __('diwan.product.low_stock', ['count' => $variant->stock]) }}
                                </span>
                            @else
                                <span class="hm-muted">{{ __('diwan.product.in_stock', ['count' => $variant->stock]) }}</span>
                            @endif
                        </p>
                    @endif

                    <div style="margin-top: var(--space-lg);">
                        @auth
                            <form method="POST" action="{{ route('cart.add') }}" class="d-flex gap-2 align-items-end flex-wrap">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <label class="hm-field" style="margin: 0; width: 6rem;">
                                    <span class="hm-field__label">{{ __('diwan.product.quantity') }}</span>
                                    <input type="number" name="quantity" value="1" min="1"
                                           max="{{ $variant?->stock ?: 99 }}" inputmode="numeric" dir="ltr">
                                </label>

                                <button type="submit" class="hm-btn hm-btn--primary hm-btn--lg" style="flex: 1 1 12rem;"
                                        @disabled($variant && $variant->stock !== null && $variant->stock <= 0)>
                                    {{ __('diwan.product.add_to_bag') }}
                                </button>

                                <button type="button" class="hm-btn hm-btn--ghost toggle-wishlist"
                                        data-product-id="{{ $product->id }}" aria-pressed="false"
                                        onclick="toggleWishlist({{ $product->id }}, this); event.preventDefault();"
                                        aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
                                    <i class="bi bi-bookmark" aria-hidden="true"></i>
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
                    <div style="margin-top: var(--space-xl);">
                        <h2 class="hm-label" style="margin-bottom: var(--space-xs);">{{ __('diwan.product.specs') }}</h2>
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
                        <div style="margin-top: var(--space-xl);">
                            <h2 class="hm-label" style="margin-bottom: var(--space-xs);">{{ __('diwan.product.description') }}</h2>
                            <p class="hm-measure" style="font-size: var(--text-sm); color: var(--color-ink-2);">
                                {{ $product->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Reviews — real rows only ════════════════════════════════════ --}}
    <section class="hm-section--tight" style="border-top: var(--rule-hair) solid var(--color-rule);">
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
                        <article style="padding-block: var(--space-md); border-bottom: var(--rule-hair) solid var(--color-rule);">
                            <p class="hm-num hm-muted" style="font-size: var(--text-xs); margin-bottom: var(--space-2xs);"
                               aria-label="{{ __('diwan.testimonials.rating', ['rating' => $review->rating]) }}">
                                {{ str_repeat('★', $review->rating) }}<span style="color: var(--color-rule-2);">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </p>
                            <p style="font-size: var(--text-sm); margin-bottom: var(--space-2xs);">{{ $review->comment }}</p>
                            <p class="hm-muted mb-0" style="font-size: var(--text-xs);">
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
                            <h3 style="font-size: var(--text-lg); margin: 0 0 var(--space-md);">{{ __('diwan.product.write_review') }}</h3>

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
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <a href="{{ route('login') }}" class="hm-link hm-link--sm">{{ __('diwan.product.sign_in_review') }}</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="hm-section--tight" style="border-top: var(--rule-hair) solid var(--color-rule);">
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
