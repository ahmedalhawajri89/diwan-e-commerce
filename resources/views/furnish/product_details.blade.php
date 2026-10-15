{{--
  This is the one page where the fit system is the layout rather than a badge on
  it: the verdict and the measurements sit in the buy panel, above the fold, and
  the drawing against a person sits under the photograph, not in an accordion.
--}}
@extends('layouts.furnish')

@section('title', $product->name)
@section('description', Str::limit(strip_tags($product->description), 160))
@section('og_image', $product->imageUrl())
@section('og_type', 'product')

@php
    // $variant is the colour chosen by ?variant=, or the default; see show().
    $price = $variant?->price;
    $images = $product->images;
    $reviews = $product->reviews;
    $avg = $reviews->count() ? round($reviews->avg('rating'), 1) : null;
    $soldOut = $variant && $variant->stock !== null && $variant->stock <= 0;

    $label = 'm-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted';
    $summary = 'flex items-center justify-between gap-4 min-h-14 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden '
             . 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $title = 'm-0 font-display font-medium rtl:font-semibold text-3xl leading-snug tracking-display';
    $control = 'block w-full bg-paper border border-rule-2 rounded-control text-ink '
             . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
@endphp

@section('content')
    <x-ui.page>
        <x-ui.breadcrumbs :items="$trail" />

        <div class="grid gap-10 lg:grid-cols-[7fr_5fr] lg:gap-16 pb-(--section-gap)">

            {{-- ══ Gallery and the drawing ═════════════════════════════════ --}}
            <div class="min-w-0">
                {{-- ══ Photographs ══════════════════════════════════════════
                     A track that snaps one photograph at a time — swiped on a
                     phone, moved by the thumbnails on a desk — and a viewer for
                     a closer look. Every slide goes through the picture
                     component, so a phone downloads the size it draws. --}}
                @php
                    $photos = $images->sortByDesc('is_primary')->values();
                    $slide = 'relative shrink-0 w-full aspect-[4/5] snap-start overflow-hidden bg-paper-2';
                    $round = 'inline-flex items-center justify-center size-11 rounded-pill bg-paper text-ink border-0 cursor-pointer '
                           . 'hover:bg-paper-2 disabled:opacity-0 disabled:pointer-events-none transition-opacity '
                           . 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
                @endphp

                <div x-data="gallery" class="relative">
                    <div x-ref="track" class="flex overflow-x-auto snap-x snap-mandatory overscroll-x-contain [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                         aria-label="{{ __('diwan.gallery.label', ['name' => $product->name]) }}" role="region">
                        @forelse($photos as $photo)
                            <figure class="m-0 {{ $slide }}" aria-label="{{ __('diwan.gallery.photo_n', ['n' => $loop->iteration, 'count' => $photos->count()]) }}">
                                <x-picture :src="$photo->url" :alt="$loop->first ? $product->name : __('diwan.gallery.photo_alt', ['name' => $product->name, 'n' => $loop->iteration])"
                                           sizes="(max-width: 64rem) 100vw, 56vw"
                                           :loading="$loop->first ? 'eager' : 'lazy'" :fetchpriority="$loop->first ? 'high' : 'auto'"
                                           decoding="async" class="absolute inset-0 size-full object-cover" />
                            </figure>
                        @empty
                            <figure class="m-0 {{ $slide }}">
                                <x-picture :src="$product->imageUrl()" :alt="$product->name" sizes="(max-width: 64rem) 100vw, 56vw"
                                           class="absolute inset-0 size-full object-cover" />
                            </figure>
                        @endforelse
                    </div>

                    @if($photos->isNotEmpty())
                        {{-- Enlarge: opens the viewer on the photograph showing. --}}
                        <button type="button" data-open-dialog="lightbox" @click="enlarge"
                                class="{{ $round }} absolute top-3 end-3" aria-label="{{ __('diwan.gallery.enlarge') }}">
                            <x-icon name="arrows-angle-expand" class="size-4" />
                        </button>
                    @endif

                    @if($photos->count() > 1)
                        <div class="flex items-center justify-between gap-4 mt-3">
                            <div x-ref="thumbs" class="flex flex-wrap gap-2">
                                @foreach($photos as $photo)
                                    <button type="button" data-index="{{ $loop->index }}" @click="show"
                                            aria-label="{{ __('diwan.gallery.photo_n', ['n' => $loop->iteration, 'count' => $photos->count()]) }}"
                                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                            class="relative size-16 p-0 overflow-hidden border border-rule bg-paper-2 cursor-pointer aria-[current=true]:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                        <x-picture :src="$photo->url" alt="" sizes="4rem" loading="lazy" class="absolute inset-0 size-full object-cover" />
                                    </button>
                                @endforeach
                            </div>
                            <span class="shrink-0 font-mono text-xs text-muted" dir="ltr" x-text="position" aria-hidden="true">1 / {{ $photos->count() }}</span>
                        </div>
                    @endif

                    @if($photos->isNotEmpty())
                        {{-- The viewer. A native <dialog>: focus trapped, Escape closes,
                             and focus returns to the enlarge button (dialogs.js). --}}
                        <dialog id="lightbox" @keydown="key" @close="closed"
                                class="m-0 p-0 w-screen max-w-none h-dvh max-h-none border-0 bg-deep text-on-deep"
                                aria-label="{{ __('diwan.gallery.label', ['name' => $product->name]) }}">
                            <div class="relative h-full">
                                <div x-ref="zoom" class="flex h-full overflow-x-auto snap-x snap-mandatory [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    @foreach($photos as $photo)
                                        <figure class="m-0 relative shrink-0 w-full h-full snap-start">
                                            <x-picture :src="$photo->url" :alt="__('diwan.gallery.photo_alt', ['name' => $product->name, 'n' => $loop->iteration])"
                                                       sizes="100vw" loading="lazy" class="absolute inset-0 size-full object-contain" />
                                        </figure>
                                    @endforeach
                                </div>

                                <button type="button" data-close-dialog autofocus
                                        class="{{ $round }} absolute top-4 end-4" aria-label="{{ __('diwan.nav.close') }}">
                                    <x-icon name="x-lg" class="size-4" />
                                </button>

                                @if($photos->count() > 1)
                                    <button type="button" @click="prev" :disabled="atStart" class="{{ $round }} absolute start-4 top-1/2 -translate-y-1/2" aria-label="{{ __('diwan.gallery.previous') }}">
                                        <x-icon name="chevron-left" class="size-4" />
                                    </button>
                                    <button type="button" @click="next" :disabled="atEnd" class="{{ $round }} absolute end-4 top-1/2 -translate-y-1/2" aria-label="{{ __('diwan.gallery.next') }}">
                                        <x-icon name="chevron-right" class="size-4" />
                                    </button>
                                    <p class="absolute bottom-4 inset-x-0 m-0 text-center font-mono text-xs text-on-deep-muted" dir="ltr" x-text="position" aria-live="polite"></p>
                                @endif
                            </div>
                        </dialog>
                    @endif
                </div>

                {{-- ══ The drawing — the reason this page exists ═══════════ --}}
                {{-- A caption, not a heading: as an <h2> it came before the page's
                     <h1> in the document, and a screen reader's outline began
                     with the drawing instead of the piece. --}}
                <div class="mt-12">
                    <p class="{{ $label }}">{{ __('diwan.scale.human') }}</p>
                    <x-scale-figure :variant="$variant" :name="$product->name" />
                </div>
            </div>

            {{-- ══ Buy panel ═══════════════════════════════════════════════ --}}
            <div id="buy" class="lg:sticky lg:top-36 self-start scroll-mt-28">
                @if($product->category)
                    <a href="{{ route('furnish.category', $product->category->slug) }}"
                       class="font-label text-xs font-medium uppercase tracking-label text-muted no-underline hover:text-ink">{{ $product->category->name }}</a>
                @endif

                <h1 class="m-0 mt-3 font-display font-medium rtl:font-semibold text-display-s leading-tight tracking-display">{{ $product->name }}</h1>

                <p class="m-0 mt-4 font-mono text-xl text-accent-ink">
                    @if($price !== null)
                        @money($price)
                        <span class="block mt-1 font-body text-xs text-muted">{{ __('diwan.money.price_includes_vat') }}</span>
                    @else
                        <span class="font-body text-base text-muted">{{ __('diwan.product.no_price') }}</span>
                    @endif
                </p>

                @if($avg)
                    <a href="#reviews" class="inline-flex items-center gap-3 mt-3 no-underline">
                        <x-star-rating :rating="(int) round($avg)" class="m-0" />
                        <span class="text-xs text-muted">{{ trans_choice('diwan.product.review_count', $reviews->count()) }}</span>
                    </a>
                @endif

                {{-- ══ Colour — each a plain link, so it works without JavaScript
                     and the chosen colour has an address of its own. A word, not
                     a swatch alone: a colour is never shown by colour only. --}}
                @if($product->variants->count() > 1)
                    <div class="mt-8">
                        <p class="{{ $label }}" id="colourLabel">
                            {{ __('diwan.product.choose_colour') }}:
                            <span class="normal-case tracking-normal text-ink">{{ $variant->colourLabel() }}</span>
                        </p>
                        <ul class="m-0 p-0 list-none flex flex-wrap gap-2" aria-labelledby="colourLabel">
                            @foreach($product->variants->sortBy('id') as $option)
                                @php
                                    $chosen = $option->is($variant);
                                    $gone = $option->stock !== null && $option->stock <= 0;
                                @endphp
                                <li>
                                    <a href="{{ route('furnish.product.show', $product->slug) }}?variant={{ $option->id }}#buy"
                                       @if($chosen) aria-current="true" @endif
                                       @if($gone) aria-label="{{ __('diwan.product.colour_sold_out', ['colour' => $option->colourLabel()]) }}" @endif
                                       class="inline-flex items-center min-h-11 px-4 border rounded-control text-sm no-underline transition-colors
                                              {{ $chosen ? 'border-ink bg-ink text-on-accent' : 'border-rule-2 text-ink hover:border-ink' }}
                                              {{ $gone ? 'line-through decoration-1 text-muted' : '' }}
                                              focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                        {{ $option->colourLabel() }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Fit, stated with its reasoning rather than as a bare label. --}}
                <div class="mt-8 p-5 bg-paper-2">
                    <p class="{{ $label }}">{{ __('diwan.fit.legend') }}</p>
                    <x-fit-badge :variant="$variant" :room="$room" :withNote="true" />
                    <div class="mt-4">
                        <x-scale-rule :variant="$variant" />
                    </div>

                    {{-- And whether it can get there: door, lift or stairs, balcony. --}}
                    <div class="mt-5 pt-5 border-0 border-t border-rule">
                        <p class="{{ $label }}">{{ __('diwan.passage.legend') }}</p>
                        <x-passage-badge :variant="$variant" :way-in="$wayIn" :withNote="true" />
                    </div>
                </div>

                @if($variant && $variant->stock !== null)
                    <p class="flex items-center gap-2 m-0 mt-5 text-sm">
                        @if($variant->stock <= 0)
                            <x-icon name="x-lg" class="size-3.5 text-danger" />
                            <span class="text-danger">{{ __('diwan.product.out_of_stock') }}</span>
                        @elseif($variant->stock <= 5)
                            <x-icon name="exclamation-triangle" class="size-3.5 text-warn" />
                            <span class="text-warn">{{ __('diwan.product.low_stock', ['count' => $variant->stock]) }}</span>
                        @else
                            <span class="size-2 rounded-pill bg-ok" aria-hidden="true"></span>
                            <span class="text-muted">{{ __('diwan.product.in_stock', ['count' => $variant->stock]) }}</span>
                        @endif
                    </p>
                @endif

                <div class="mt-6">
                    <form id="buyForm" method="POST" action="{{ route('cart.add') }}" data-bag-form class="grid grid-cols-[auto_1fr] gap-3"
                          x-data="submitOnce" @submit="lock">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($variant)
                            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                        @endif

                        <x-ui.stepper name="quantity" :value="old('quantity', 1)" :min="1"
                                      :max="max(1, $variant?->stock ?? 99)" :label="__('diwan.product.quantity')" />

                        @if($soldOut || $price === null)
                            <x-ui.button type="submit" disabled class="disabled:opacity-40 disabled:cursor-not-allowed">
                                <x-icon name="bag-plus" class="size-4" />
                                {{ __('diwan.product.add_to_bag') }}
                            </x-ui.button>
                        @else
                            {{-- Locked after one press: the bag adds to a line it
                                 already has, so a second click on a slow
                                 connection used to double the quantity. --}}
                            <x-ui.button id="addToBag" type="submit" ::disabled="busy" class="disabled:opacity-60 disabled:cursor-wait">
                                <x-icon name="bag-plus" class="size-4" />
                                <span x-show="idle">{{ __('diwan.product.add_to_bag') }}</span>
                                <span x-show="busy" x-cloak>{{ __('diwan.product.adding') }}</span>
                            </x-ui.button>
                        @endif

                        <x-ui.button variant="ghost" class="toggle-wishlist col-span-2 aria-pressed:border-ink"
                                     data-product-id="{{ $product->id }}" aria-pressed="false"
                                     aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
                            <x-icon name="bookmark" class="size-4" />
                            {{ __('diwan.product.save') }}
                        </x-ui.button>
                    </form>

                    <button type="button" x-data="share" @click="share"
                            data-url="{{ url()->full() }}" data-title="{{ $product->name }}"
                            data-copied="{{ __('diwan.product.link_copied') }}" data-failed="{{ __('diwan.product.copy_failed') }}"
                            class="inline-flex items-center gap-2 min-h-11 mt-2 p-0 border-0 bg-transparent text-sm text-muted cursor-pointer hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                        <x-icon name="send" class="size-3.5" />
                        {{ __('diwan.product.share') }}
                    </button>
                </div>

                {{-- ══ Details — three sections that open and close ══════════
                     Native <details>: keyboard and screen reader behaviour come
                     with the element, nothing is hidden without JavaScript, and
                     the description starts open. --}}
                @php
                    $row = 'flex justify-between gap-6 py-3 border-0 border-b border-rule last:border-b-0';
                    $doorway = \App\Support\Fit::DOORWAY_CM;
                    $tooWide = $variant?->hasDimensions()
                        && min($variant->width_cm, $variant->depth_cm, $variant->height_cm) > $doorway;
                @endphp

                <div class="mt-10 border-0 border-t border-rule">
                    @if($product->description)
                        <details class="group border-0 border-b border-rule" open>
                            <summary class="{{ $summary }}">
                                <h2 class="m-0 text-base font-medium">{{ __('diwan.product.description') }}</h2>
                                <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                            </summary>
                            <p class="m-0 pb-6 max-w-measure leading-body text-ink-2">{{ $product->description }}</p>
                        </details>
                    @endif

                    {{-- Specification — real columns only. --}}
                    <details class="group border-0 border-b border-rule">
                        <summary class="{{ $summary }}">
                            <h2 class="m-0 text-base font-medium">{{ __('diwan.product.specs') }}</h2>
                            <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                        </summary>
                        <dl class="m-0 pb-4 text-sm">
                            <div class="{{ $row }}">
                                <dt class="text-muted">{{ __('diwan.scale.label') }}</dt>
                                @if($variant?->width_cm)
                                    {{-- The figures read left to right in both scripts; the
                                         unit stays outside the isolate, so Arabic reads
                                         "120 × 80 × 75 سم" and not the unit first. --}}
                                    <dd class="m-0"><bdi dir="ltr" class="font-mono">{{ $variant->width_cm }} &times; {{ $variant->depth_cm }}@if($variant->height_cm) &times; {{ $variant->height_cm }}@endif</bdi> {{ __('diwan.room.unit') }}</dd>
                                @else
                                    <dd class="m-0 text-muted">{{ __('diwan.scale.not_recorded') }}</dd>
                                @endif
                            </div>

                            @if($variant?->footprint())
                                <div class="{{ $row }}">
                                    <dt class="text-muted">{{ __('diwan.fit.footprint') }}</dt>
                                    <dd class="m-0"><bdi dir="ltr" class="font-mono">{{ number_format($variant->footprint(), 2) }}</bdi> {{ __('diwan.room.area_unit') }}</dd>
                                </div>
                            @endif

                            @if($variant?->color)
                                <div class="{{ $row }}">
                                    <dt class="text-muted">{{ __('diwan.product.colour') }}</dt>
                                    <dd class="m-0">{{ $variant->colourLabel() }}</dd>
                                </div>
                            @endif

                            @if($product->category)
                                <div class="{{ $row }}">
                                    <dt class="text-muted">{{ __('diwan.product.category') }}</dt>
                                    <dd class="m-0">{{ $product->category->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </details>

                    {{-- Delivery and payment — only what the shop actually does.
                         No delivery date, no returns window, no price: none of
                         them is settled, so none of them is promised. --}}
                    <details class="group border-0 border-b border-rule">
                        <summary class="{{ $summary }}">
                            <h2 class="m-0 text-base font-medium">{{ __('diwan.product.delivery_title') }}</h2>
                            <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                        </summary>
                        <ul class="m-0 p-0 pb-6 list-none grid gap-4 text-sm leading-body">
                            <li class="flex gap-3">
                                <x-icon name="cash-coin" class="size-4 mt-0.5 text-muted" />
                                <span><strong class="font-medium">{{ __('diwan.checkout.cod') }}.</strong> {{ __('diwan.checkout.cod_note') }}</span>
                            </li>
                            <li class="flex gap-3">
                                <x-icon name="box-seam" class="size-4 mt-0.5 text-muted" />
                                <span><strong class="font-medium">{{ __('diwan.bag.shipping') }}:</strong> {{ __('diwan.bag.shipping_on_contact') }}</span>
                            </li>
                            <li class="flex gap-3">
                                @if($tooWide)
                                    <x-icon name="exclamation-triangle" class="size-4 mt-0.5 text-warn" />
                                    <span class="text-warn">{{ __('diwan.fit.note.doorway') }} {{ __('diwan.product.doorway_measure') }}</span>
                                @else
                                    <x-icon name="rulers" class="size-4 mt-0.5 text-muted" />
                                    <span>{{ __('diwan.product.doorway_check') }}</span>
                                @endif
                            </li>
                            <li class="ps-7">
                                <x-ui.link :href="route('furnish.page', 'shipping')" tone="ink">{{ __('diwan.pages.shipping.title') }}</x-ui.link>
                            </li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>
    </x-ui.page>

    {{-- ══ The bar on a phone ═════════════════════════════════════════════
         Once the page's own Add to bag has scrolled away, the piece, its price
         and the button stay within a thumb's reach. Submits the same form. --}}
    @if(! $soldOut && $price !== null)
        <div x-data="stickyBuy" data-watch="addToBag" aria-hidden="true" :aria-hidden="hidden" :inert="hidden"
             class="lg:hidden fixed inset-x-0 bottom-0 z-30 border-0 border-t border-rule bg-paper
                    motion-safe:transition-transform motion-safe:duration-300 motion-safe:ease-out aria-hidden:translate-y-full">
            <div class="flex items-center gap-4 px-(--page-gutter) py-3">
                <div class="min-w-0 flex-1">
                    <p class="m-0 truncate text-sm">{{ $product->name }}@if($variant?->color) · {{ $variant->colourLabel() }}@endif</p>
                    <p class="m-0 font-mono text-sm text-accent-ink">@money($price)</p>
                </div>
                <x-ui.button type="submit" form="buyForm" class="shrink-0">
                    <x-icon name="bag-plus" class="size-4" />
                    {{ __('diwan.product.sticky_add') }}
                </x-ui.button>
            </div>
        </div>
    @endif

    {{-- ══ Reviews — approved rows only ═══════════════════════════════════
         The summary is arithmetic on the approved reviews and nothing else:
         no review, no average, no bars. --}}
    @php
        $counts = $reviews->countBy('rating');
        $field = 'block w-full bg-paper border border-rule-2 rounded-control text-ink '
               . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus '
               . 'aria-invalid:border-danger';
    @endphp
    <section id="reviews" class="py-(--section-gap) border-0 border-t border-rule scroll-mt-32" aria-labelledby="reviewsTitle">
        <x-ui.page>
            <h2 id="reviewsTitle" class="{{ $title }} mb-10">{{ __('diwan.product.reviews') }}</h2>

            <div class="grid gap-12 lg:grid-cols-[5fr_7fr] lg:gap-16">
                <div class="grid gap-10 content-start">
                    @if($avg)
                        <div>
                            <p class="m-0 flex items-baseline gap-3">
                                <span class="font-mono text-5xl leading-none" dir="ltr">{{ number_format($avg, 1) }}</span>
                                <span class="text-sm text-muted">{{ __('diwan.product.out_of_five') }}</span>
                            </p>
                            <x-star-rating :rating="(int) round($avg)" class="mt-3 mb-1" />
                            <p class="m-0 text-sm text-muted">{{ trans_choice('diwan.product.review_count', $reviews->count()) }}</p>

                            {{-- One row per star level. <progress> is the bar, so no
                                 width needs a style attribute and each row is read
                                 out as a value of a maximum. --}}
                            <ul class="m-0 mt-6 p-0 list-none grid gap-2">
                                @for($n = 5; $n >= 1; $n--)
                                    @php $c = $counts[$n] ?? 0; @endphp
                                    <li class="grid grid-cols-[2.5rem_1fr_2rem] items-center gap-3 text-xs">
                                        <span class="font-mono text-muted" dir="ltr">{{ $n }} ★</span>
                                        <progress value="{{ $c }}" max="{{ $reviews->count() }}"
                                                  aria-label="{{ __('diwan.product.star_count', ['n' => $n, 'count' => $c]) }}"
                                                  class="w-full h-1.5 appearance-none overflow-hidden rounded-pill bg-paper-3
                                                         [&::-webkit-progress-bar]:bg-paper-3 [&::-webkit-progress-value]:bg-accent-ink [&::-moz-progress-bar]:bg-accent-ink"></progress>
                                        <span class="font-mono text-muted text-end" dir="ltr">{{ $c }}</span>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    @endif

                    <div>
                        @auth
                            <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="p-6 sm:p-8 bg-paper-2 grid gap-6"
                                  x-data="submitOnce" @submit="lock" novalidate>
                                @csrf
                                <h3 class="m-0 font-display text-2xl">{{ __('diwan.product.write_review') }}</h3>

                                {{-- Five radios drawn as stars, in their natural order, so
                                     the arrow keys move the way the eye reads in either
                                     script. Once one is chosen every star is filled, then
                                     the stars after the chosen one are emptied again. --}}
                                <fieldset class="m-0 p-0 border-0" @error('rating') aria-invalid="true" aria-describedby="ratingError" @enderror>
                                    <legend class="{{ $label }}">{{ __('diwan.product.your_rating') }}</legend>
                                    <div class="group/stars flex gap-1">
                                        @for($n = 1; $n <= 5; $n++)
                                            <input type="radio" id="rate{{ $n }}" name="rating" value="{{ $n }}" class="sr-only" @checked((int) old('rating') === $n) @if($n === 1) required @endif>
                                            <label for="rate{{ $n }}"
                                                   class="inline-flex items-center justify-center size-11 cursor-pointer text-2xl leading-none text-rule-2 hover:text-accent-ink
                                                          group-has-[input:checked]/stars:text-accent-ink [input:checked+label~&]:text-rule-2!
                                                          [input:focus-visible+&]:outline-2 [input:focus-visible+&]:outline-focus">
                                                <span aria-hidden="true">★</span>
                                                <span class="sr-only">{{ __('diwan.testimonials.rating', ['rating' => $n]) }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <p id="ratingError" class="m-0 mt-2 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </fieldset>

                                <label class="block">
                                    <span class="{{ $label }} block">{{ __('diwan.product.your_review') }}</span>
                                    <textarea name="comment" required maxlength="1000" rows="5" dir="auto"
                                              class="{{ $field }} p-3 leading-body resize-y"
                                              @error('comment') aria-invalid="true" aria-describedby="commentError" @enderror>{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <span id="commentError" class="block mt-2 text-sm text-danger">{{ $message }}</span>
                                    @enderror
                                </label>

                                <p class="m-0 text-xs text-muted">{{ __('diwan.product.review_moderated') }}</p>

                                <x-ui.button type="submit" variant="ghost" :block="true" ::disabled="busy">
                                    <span x-show="idle">{{ __('diwan.product.submit_review') }}</span>
                                    <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
                                </x-ui.button>
                            </form>
                        @else
                            <x-ui.note icon="person">
                                <x-ui.link :href="route('login')">{{ __('diwan.product.sign_in_review') }}</x-ui.link>
                            </x-ui.note>
                        @endauth
                    </div>
                </div>

                <div>
                    @forelse($reviews as $review)
                        <article class="py-6 border-0 border-b border-rule first:pt-0">
                            <x-star-rating :rating="$review->rating" class="m-0" />
                            {{-- dir="auto": an English review on the Arabic site keeps
                                 its full stop at its own end, and the reverse. --}}
                            <p class="m-0 mt-3 font-display text-xl leading-snug [overflow-wrap:anywhere]" dir="auto">{{ $review->comment }}</p>
                            <p class="m-0 mt-3 text-xs text-muted">
                                @if($review->user?->name)<bdi>{{ $review->user->name }}</bdi> · @endif{{ $review->created_at->translatedFormat('j F Y') }}
                            </p>
                        </article>
                    @empty
                        <p class="m-0 text-muted">{{ __('diwan.product.no_reviews') }}</p>
                    @endforelse
                </div>
            </div>
        </x-ui.page>
    </section>

    @if($related->isNotEmpty())
        <section class="py-(--section-gap) border-0 border-t border-rule">
            <x-ui.page>
                <h2 class="{{ $title }} mb-10">{{ __('diwan.product.related') }}</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-12 lg:gap-x-6" data-reveal-group>
                    @foreach($related as $index => $item)
                        <div data-reveal>
                            <x-product-card :product="$item" :room="$room" :i="$index" />
                        </div>
                    @endforeach
                </div>
            </x-ui.page>
        </section>
    @endif
    {{-- ══ Recently viewed ════════════════════════════════════════════════
         From this visitor's session only. A row that scrolls sideways on a
         phone, so it never pushes the footer a screen further away. --}}
    @if($recent->isNotEmpty())
        <section class="py-(--section-gap) border-0 border-t border-rule" aria-labelledby="recentTitle">
            <x-ui.page>
                <h2 id="recentTitle" class="{{ $title }} mb-10">{{ __('diwan.product.recently_viewed') }}</h2>
                <x-ui.rail :label="__('diwan.product.recently_viewed')">
                    @foreach($recent as $index => $item)
                        <li class="shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4.5rem)/4)] snap-start" data-reveal>
                            <x-product-card :product="$item" :room="$room" :i="$index" />
                        </li>
                    @endforeach
                </x-ui.rail>
            </x-ui.page>
        </section>
    @endif
@endsection
