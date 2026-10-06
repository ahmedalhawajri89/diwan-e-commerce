{{--
  Product card. The unit the whole catalogue is built from.

  No frame: the photograph is the card. One stretched link over the title
  makes the whole card a target without nesting interactive elements; the
  category link and the save button sit above it as their own targets.

  What it says, all of it read from the piece's own rows: the fit against the
  visitor's room, how few are left when few are, how many colours it comes
  in, its price, its measurements. A second photograph of the same piece, if
  there is one, shows on hover. There is no "New" badge: in a catalogue that
  is young, every card would carry one, and a mark on everything marks
  nothing.

  @param  \App\Models\Product  $product
  @param  array|null  $room
  @param  int   $i      position in the grid
  @param  bool  $eager  the first row of a listing: its photograph is the
                        page's largest paint, so it must not wait to load
--}}
@props(['product', 'room' => null, 'i' => 0, 'eager' => false])

@php
    $variant = $product->primaryVariant();
    $price = $variant?->price;

    $stock = $product->variants->sum(fn ($v) => max(0, (int) $v->stock));
    $soldOut = $product->variants->isNotEmpty() && $stock === 0;
    $fewLeft = ! $soldOut && $stock > 0 && $stock <= 5;
    $colours = $product->variants->pluck('color')->filter()->unique()->count();

    $photos = $product->images->sortByDesc('is_primary')->values();
    $second = $photos->get(1);

    $sizes = '(max-width: 64rem) 46vw, (max-width: 80rem) 30vw, 22rem';
@endphp

<article class="group relative flex flex-col">
    <div class="relative aspect-[4/5] overflow-hidden bg-paper-2">
        <x-picture :src="$product->imageUrl()"
                   :alt="$product->name"
                   :sizes="$sizes"
                   :width="1600" :height="2000"
                   :loading="$eager ? 'eager' : 'lazy'" :fetchpriority="$eager && $i === 0 ? 'high' : 'auto'" decoding="async"
                   class="absolute inset-0 size-full object-cover transition-[scale,opacity] duration-(--dur-slow) ease-out group-hover:scale-[1.03] {{ $second ? 'group-hover:opacity-0' : '' }}" />

        @if($second)
            {{-- The same piece from another side: never another piece's photograph. --}}
            <x-picture :src="$second->url" alt="" :sizes="$sizes" :width="1600" :height="2000" loading="lazy" decoding="async"
                       class="absolute inset-0 size-full object-cover opacity-0 transition-opacity duration-(--dur-slow) ease-out group-hover:opacity-100" />
        @endif

        <div class="absolute top-3 start-3 z-10">
            <x-fit-badge :variant="$variant" :room="$room" :onCard="true" />
        </div>

        @if($soldOut || $fewLeft)
            <div class="absolute bottom-3 start-3 z-10">
                <x-ui.chip :tone="$soldOut ? 'danger' : 'warn'" class="bg-paper!">
                    {{ $soldOut ? __('diwan.product.out_of_stock') : __('diwan.product.last_n', ['count' => $stock]) }}
                </x-ui.chip>
            </div>
        @endif

        <button type="button"
                class="toggle-wishlist absolute top-1.5 end-1.5 z-10 inline-flex items-center justify-center size-11 p-0 border-0 rounded-pill bg-paper text-ink cursor-pointer transition-colors hover:bg-paper-2 aria-pressed:bg-ink aria-pressed:text-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus"
                data-product-id="{{ $product->id }}"
                aria-pressed="false"
                aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
            <x-icon name="bookmark" class="size-4" />
        </button>
    </div>

    <div class="flex flex-col gap-1 pt-4">
        @if($product->category)
            <a href="{{ route('furnish.category', $product->category->slug) }}"
               class="relative z-10 self-start -my-3.5 py-3.5 font-label text-xs uppercase tracking-label text-muted no-underline hover:text-ink">{{ $product->category->name }}</a>
        @endif

        <h3 class="m-0 font-display font-medium text-xl leading-snug [overflow-wrap:anywhere]">
            <a href="{{ route('furnish.product.show', $product->slug) }}"
               class="text-ink no-underline after:absolute after:inset-0 after:content-['']">{{ $product->name }}</a>
        </h3>

        <p class="m-0 flex flex-wrap items-baseline gap-x-3 gap-y-1">
            @if($price !== null)
                <span class="font-mono text-sm text-accent-ink">{{ __('diwan.common.currency') }}{{ number_format($price, 2) }}</span>
            @else
                {{-- No variant, no price. We do not print an invented figure. --}}
                <span class="text-sm text-muted">{{ __('diwan.product.no_price') }}</span>
            @endif
            @if($colours > 1)
                <span class="text-xs text-muted">{{ trans_choice('diwan.product.colour_count', $colours) }}</span>
            @endif
        </p>

        <div class="mt-2">
            <x-scale-rule :variant="$variant" :i="$i" />
        </div>
    </div>
</article>
