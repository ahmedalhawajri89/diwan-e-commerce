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

<article class="hm-card hm-rise hm-i-{{ $i }}">
    <div class="hm-card__media">
        {{-- Twelve of these on a catalogue page, so it is the largest
             transfer on the site. The grid is auto-fill from a 15rem minimum,
             which settles between 15rem and 20rem on a desktop. --}}
        <x-picture :src="$product->imageUrl()"
                   :alt="$product->name"
                   sizes="(max-width: 30rem) 92vw, (max-width: 60rem) 45vw, 18rem"
                   :width="600" :height="450"
                   loading="lazy" decoding="async" />

        <div class="hm-card__fit">
            <x-fit-badge :variant="$variant" :room="$room" :onCard="true" />
        </div>

        <button type="button"
                class="hm-card__save toggle-wishlist"
                data-product-id="{{ $product->id }}"
                aria-pressed="false"
                aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
            <x-icon name="bookmark" />
        </button>
    </div>

    <div class="hm-card__body">
        @if($product->category)
            {{-- Sits above the stretched title link, so it is its own target. --}}
            <a href="{{ route('furnish.category', $product->category->slug) }}"
               class="hm-card__cat hm-above-stretch">{{ $product->category->name }}</a>
        @endif

        <h3 class="hm-card__name">
            <a href="{{ route('furnish.product.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>

        <p class="hm-card__price hm-num">
            @if($price !== null)
                {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
            @else
                {{-- No variant, no price. We do not print an invented figure. --}}
                <span class="hm-muted hm-face-body hm-normal">
                    {{ __('diwan.product.no_price') }}
                </span>
            @endif
        </p>

        <div class="hm-card__scale">
            <x-scale-rule :variant="$variant" :i="$i" />
        </div>
    </div>
</article>
