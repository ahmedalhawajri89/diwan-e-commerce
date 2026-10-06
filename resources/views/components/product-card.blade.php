{{--
  Product card. The unit the whole catalogue is built from.

  No frame: the photograph is the card. One stretched link over the title
  makes the whole card a target without nesting interactive elements; the
  category link and the save button sit above it as their own targets.

  @param  \App\Models\Product  $product
  @param  array|null  $room
  @param  int  $i  position in the grid, for the scale rule's entrance
--}}
@props(['product', 'room' => null, 'i' => 0])

@php
    $variant = $product->primaryVariant();
    $price = $variant?->price;
@endphp

<article class="group relative flex flex-col">
    <div class="relative aspect-[4/5] overflow-hidden bg-paper-2">
        {{-- Twelve of these on a catalogue page, so it is the largest
             transfer on the site. Two columns on a phone, four on a desktop. --}}
        <x-picture :src="$product->imageUrl()"
                   :alt="$product->name"
                   sizes="(max-width: 64rem) 46vw, 20rem"
                   :width="1600" :height="2000"
                   loading="lazy" decoding="async"
                   class="absolute inset-0 size-full object-cover transition-transform duration-(--dur-slow) ease-out group-hover:scale-[1.03]" />

        <div class="absolute top-3 start-3 z-10">
            <x-fit-badge :variant="$variant" :room="$room" :onCard="true" />
        </div>

        <button type="button"
                class="toggle-wishlist absolute top-2 end-2 z-10 inline-flex items-center justify-center size-10 p-0 border-0 rounded-pill bg-paper text-ink cursor-pointer transition-colors hover:bg-paper-2 aria-pressed:bg-ink aria-pressed:text-paper"
                data-product-id="{{ $product->id }}"
                aria-pressed="false"
                aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
            <x-icon name="bookmark" class="size-4" />
        </button>
    </div>

    <div class="flex flex-col gap-1 pt-4">
        @if($product->category)
            <a href="{{ route('furnish.category', $product->category->slug) }}"
               class="relative z-10 self-start font-label text-xs uppercase tracking-label text-muted no-underline hover:text-ink">{{ $product->category->name }}</a>
        @endif

        <h3 class="m-0 font-display font-medium text-xl leading-snug">
            <a href="{{ route('furnish.product.show', $product->slug) }}"
               class="text-ink no-underline after:absolute after:inset-0 after:content-['']">{{ $product->name }}</a>
        </h3>

        <p class="m-0 font-mono text-sm text-accent-ink">
            @if($price !== null)
                {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
            @else
                {{-- No variant, no price. We do not print an invented figure. --}}
                <span class="font-body text-muted">{{ __('diwan.product.no_price') }}</span>
            @endif
        </p>

        <div class="mt-2">
            <x-scale-rule :variant="$variant" :i="$i" />
        </div>
    </div>
</article>
