{{--
  F6 · Product card. The unit the whole catalogue is built from.

  Card-internal links only, one stretched link over the media and title so the
  whole card is a target without nesting interactive elements. The save button
  raises its own z-index to opt back out of the stretched link.

  @param  \App\Models\Product  $product
  @param  array|null  $room
  @param  int  $i  DOM index, for the staggered entrance
--}}
@props(['product', 'room' => null, 'i' => 0])

@php
    $variant = $product->primaryVariant();
    $price = $variant?->price;
@endphp

<article class="hm-card hm-rise" style="--i: {{ $i }}">
    <div class="hm-card__media">
        <img src="{{ $product->imageUrl() }}"
             alt="{{ $product->name }}"
             loading="lazy" decoding="async"
             width="600" height="450"
             onload="this.classList.add('is-loaded')">

        <div class="hm-card__fit">
            <x-fit-badge :variant="$variant" :room="$room" :onCard="true" />
        </div>

        <button type="button"
                class="hm-card__save toggle-wishlist"
                data-product-id="{{ $product->id }}"
                aria-pressed="false"
                onclick="toggleWishlist({{ $product->id }}, this); event.preventDefault();"
                aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
            <i class="bi bi-bookmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="hm-card__body">
        @if($product->category)
            <span class="hm-card__cat">{{ $product->category->name }}</span>
        @endif

        <h3 class="hm-card__name">
            <a href="{{ route('furnish.product.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>

        <p class="hm-card__price hm-num">
            @if($price !== null)
                {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
            @else
                {{-- No variant, no price. We do not print an invented figure. --}}
                <span class="hm-muted" style="font-family: var(--font-body); font-weight: 400;">
                    {{ __('diwan.product.no_price') }}
                </span>
            @endif
        </p>

        <div class="hm-card__scale">
            <x-scale-rule :variant="$variant" :i="$i" />
        </div>
    </div>
</article>
