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
    $variant = $product->primaryVariant();
    $price = $variant?->price;
    $images = $product->images;
    $reviews = $product->reviews;
    $avg = $reviews->count() ? round($reviews->avg('rating'), 1) : null;
    $soldOut = $variant && $variant->stock !== null && $variant->stock <= 0;

    $label = 'm-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted';
    $title = 'm-0 font-display font-medium rtl:font-semibold text-3xl leading-snug tracking-display';
    $control = 'block w-full bg-paper border border-rule-2 rounded-control text-ink '
             . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
@endphp

@section('content')
    <x-ui.page>
        <nav class="pt-8 pb-6" aria-label="{{ __('diwan.common.back') }}">
            <a href="{{ route('furnish.products') }}" class="group inline-flex items-center gap-2 text-sm text-muted no-underline hover:text-ink">
                <x-icon name="arrow-left" class="size-3.5 transition-transform group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
                {{ __('diwan.product.back') }}
            </a>
        </nav>

        <div class="grid gap-10 lg:grid-cols-[7fr_5fr] lg:gap-16 pb-(--section-gap)">

            {{-- ══ Gallery and the drawing ═════════════════════════════════ --}}
            <div class="min-w-0">
                <figure class="m-0 relative aspect-[4/5] overflow-hidden bg-paper-2">
                    {{-- Deliberately a plain <img>. The gallery swaps this
                         element's `src` when a thumbnail is pressed, and a
                         <source> set would keep winning over the swap. One
                         photograph pays full price so the gallery works. --}}
                    <img id="heroImage" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                         fetchpriority="high" decoding="async" width="1600" height="2000"
                         class="absolute inset-0 size-full object-cover">
                </figure>

                @if($images->count() > 1)
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($images as $image)
                            <button type="button" data-hero-src="{{ $image->url }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                    aria-label="{{ $product->name }} — {{ $loop->iteration }}"
                                    class="relative size-18 p-0 overflow-hidden border border-rule bg-paper-2 cursor-pointer aria-pressed:border-ink aria-pressed:outline-1 aria-pressed:outline-ink">
                                <x-picture :src="$image->url" alt="" sizes="4.5rem" loading="lazy"
                                           class="absolute inset-0 size-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- ══ The drawing — the reason this page exists ═══════════ --}}
                <div class="mt-12">
                    <h2 class="{{ $label }}">{{ __('diwan.scale.human') }}</h2>
                    <x-scale-figure :variant="$variant" :name="$product->name" />
                </div>
            </div>

            {{-- ══ Buy panel ═══════════════════════════════════════════════ --}}
            <div class="lg:sticky lg:top-36 self-start">
                @if($product->category)
                    <a href="{{ route('furnish.category', $product->category->slug) }}"
                       class="font-label text-xs font-medium uppercase tracking-label text-muted no-underline hover:text-ink">{{ $product->category->name }}</a>
                @endif

                <h1 class="m-0 mt-3 font-display font-medium rtl:font-semibold text-display-s leading-tight tracking-display">{{ $product->name }}</h1>

                <p class="m-0 mt-4 font-mono text-xl text-accent-ink">
                    @if($price !== null)
                        {{ __('diwan.common.currency') }}{{ number_format($price, 2) }}
                    @else
                        <span class="font-body text-base text-muted">{{ __('diwan.product.no_price') }}</span>
                    @endif
                </p>

                @if($avg)
                    <a href="#reviews" class="inline-flex items-center gap-3 mt-3 no-underline">
                        <x-star-rating :rating="(int) round($avg)" class="m-0" />
                        <span class="text-xs text-muted">{{ __('diwan.product.review_count', ['count' => $reviews->count()]) }}</span>
                    </a>
                @endif

                {{-- Fit, stated with its reasoning rather than as a bare label. --}}
                <div class="mt-8 p-5 bg-paper-2">
                    <p class="{{ $label }}">{{ __('diwan.fit.legend') }}</p>
                    <x-fit-badge :variant="$variant" :room="$room" :withNote="true" />
                    <div class="mt-4">
                        <x-scale-rule :variant="$variant" />
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
                    @auth
                        <form method="POST" action="{{ route('cart.add') }}" class="grid grid-cols-[6rem_1fr] gap-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <label class="block">
                                <span class="sr-only">{{ __('diwan.product.quantity') }}</span>
                                <input type="number" name="quantity" value="1" min="1" max="{{ $variant?->stock ?: 99 }}"
                                       inputmode="numeric" dir="ltr" class="{{ $control }} h-12 px-3 text-center font-mono">
                            </label>

                            <x-ui.button type="submit" :disabled="$soldOut || $price === null" class="disabled:opacity-40 disabled:cursor-not-allowed">
                                <x-icon name="bag-plus" class="size-4" />
                                {{ __('diwan.product.add_to_bag') }}
                            </x-ui.button>

                            <x-ui.button variant="ghost" class="toggle-wishlist col-span-2 aria-pressed:border-ink"
                                         data-product-id="{{ $product->id }}" aria-pressed="false"
                                         aria-label="{{ __('diwan.wishlist.save_named', ['name' => $product->name]) }}">
                                <x-icon name="bookmark" class="size-4" />
                                {{ __('diwan.product.save') }}
                            </x-ui.button>
                        </form>
                    @else
                        <x-ui.button :href="route('login')" :block="true">{{ __('diwan.product.sign_in_first') }}</x-ui.button>
                    @endauth
                </div>

                @if($product->description)
                    <div class="mt-10">
                        <h2 class="{{ $label }}">{{ __('diwan.product.description') }}</h2>
                        <p class="m-0 max-w-measure leading-body text-ink-2">{{ $product->description }}</p>
                    </div>
                @endif

                {{-- ══ Specification — real columns only ═══════════════════ --}}
                <div class="mt-10">
                    <h2 class="{{ $label }}">{{ __('diwan.product.specs') }}</h2>
                    <dl class="m-0 text-sm">
                        @php $row = 'flex justify-between gap-6 py-3 border-0 border-b border-rule'; @endphp

                        <div class="{{ $row }} border-t">
                            <dt class="text-muted">{{ __('diwan.scale.label') }}</dt>
                            @if($variant?->width_cm)
                                <dd class="m-0 font-mono" dir="ltr">
                                    {{ $variant->width_cm }} &times; {{ $variant->depth_cm }}@if($variant->height_cm) &times; {{ $variant->height_cm }}@endif
                                    {{ __('diwan.room.unit') }}
                                </dd>
                            @else
                                <dd class="m-0 text-muted">{{ __('diwan.scale.not_recorded') }}</dd>
                            @endif
                        </div>

                        @if($variant?->footprint())
                            <div class="{{ $row }}">
                                <dt class="text-muted">{{ __('diwan.fit.footprint') }}</dt>
                                <dd class="m-0 font-mono" dir="ltr">{{ number_format($variant->footprint(), 2) }} m&sup2;</dd>
                            </div>
                        @endif

                        @if($variant?->color)
                            <div class="{{ $row }}">
                                <dt class="text-muted">{{ __('diwan.product.colour') }}</dt>
                                <dd class="m-0">{{ $variant->color }}</dd>
                            </div>
                        @endif

                        @if($product->category)
                            <div class="{{ $row }}">
                                <dt class="text-muted">{{ __('diwan.product.category') }}</dt>
                                <dd class="m-0">{{ $product->category->name }}</dd>
                            </div>
                        @endif

                        <div class="{{ $row }}">
                            <dt class="text-muted">{{ __('diwan.product.sku') }}</dt>
                            <dd class="m-0 font-mono">{{ $product->slug }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </x-ui.page>

    {{-- ══ Reviews — real rows only ════════════════════════════════════════ --}}
    <section id="reviews" class="py-(--section-gap) border-0 border-t border-rule scroll-mt-32">
        <x-ui.page>
            <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
                <h2 class="{{ $title }}">{{ __('diwan.product.reviews') }}</h2>
                @if($avg)
                    <span class="font-mono text-sm text-muted">
                        {{ $avg }} / 5 · {{ __('diwan.product.review_count', ['count' => $reviews->count()]) }}
                    </span>
                @endif
            </div>

            <div class="grid gap-12 lg:grid-cols-[7fr_5fr] lg:gap-16">
                <div>
                    @forelse($reviews as $review)
                        <article class="py-6 border-0 border-b border-rule first:pt-0">
                            <x-star-rating :rating="$review->rating" class="m-0" />
                            <p class="m-0 mt-3 font-display text-xl leading-snug">{{ $review->comment }}</p>
                            <p class="m-0 mt-3 text-xs text-muted">
                                {{ $review->user?->name }} · {{ $review->created_at->translatedFormat('j F Y') }}
                            </p>
                        </article>
                    @empty
                        <p class="m-0 text-muted">{{ __('diwan.product.no_reviews') }}</p>
                    @endforelse
                </div>

                <div>
                    @auth
                        <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="p-6 sm:p-8 bg-paper-2 grid gap-6">
                            @csrf
                            <h3 class="m-0 font-display text-2xl">{{ __('diwan.product.write_review') }}</h3>

                            <label class="block">
                                <span class="{{ $label }} block">{{ __('diwan.product.your_rating') }}</span>
                                <select name="rating" required class="{{ $control }} h-11 px-3 cursor-pointer">
                                    @for($n = 5; $n >= 1; $n--)
                                        <option value="{{ $n }}">{{ $n }} / 5</option>
                                    @endfor
                                </select>
                            </label>

                            <label class="block">
                                <span class="{{ $label }} block">{{ __('diwan.product.your_review') }}</span>
                                <textarea name="comment" required maxlength="1000" rows="5" class="{{ $control }} p-3 leading-body resize-y"></textarea>
                            </label>

                            <x-ui.button type="submit" variant="ghost" :block="true">{{ __('diwan.product.submit_review') }}</x-ui.button>
                        </form>
                    @else
                        <x-ui.note icon="person">
                            <x-ui.link :href="route('login')">{{ __('diwan.product.sign_in_review') }}</x-ui.link>
                        </x-ui.note>
                    @endauth
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
@endsection
