{{--
  Hallmark · genre: modern-minimal · macrostructure: 11 Catalogue · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  A category at its own address. The catalogue filter did this already, but
  `?categories[]=3` is not a link anybody shares and not a URL a search engine
  keeps.

  No filter rail here on purpose: the page is already one filter. Offering the
  whole rail would just be the catalogue with a different heading. The way to
  narrow further is the catalogue itself, linked at the foot.
--}}
@extends('layouts.furnish')

@section('title', $category->name)

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">

            <nav aria-label="{{ __('diwan.nav.catalogue') }}" style="margin-bottom: var(--space-lg);">
                <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    {{ __('diwan.nav.catalogue') }}
                </a>
            </nav>

            <div class="hm-head">
                <div>
                    <h1 class="hm-head__title">{{ $category->name }}</h1>
                    <p class="hm-muted mb-0" style="font-size: var(--text-sm);">
                        {{ __('diwan.catalogue.intro') }}
                    </p>
                </div>
                @if($products->total() > 0)
                    <span class="hm-head__aside hm-label">
                        {{ trans_choice('diwan.piece_count', $products->total()) }}
                    </span>
                @endif
            </div>

            @unless($room)
                <p class="hm-note hm-note--info" style="margin-bottom: var(--space-lg);">
                    <i class="bi bi-rulers" aria-hidden="true"></i>
                    <span>
                        {{ __('diwan.room.body') }}
                        <button type="button" class="hm-link hm-link--sm" data-bs-toggle="modal" data-bs-target="#roomModal">
                            {{ __('diwan.room.set_prompt') }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </span>
                </p>
            @endunless

            @if($products->count() > 0)
                <div class="hm-grid">
                    @foreach($products as $index => $product)
                        <x-product-card :product="$product" :room="$room" :i="$index" />
                    @endforeach
                </div>

                <div style="margin-top: var(--space-2xl);">{{ $products->onEachSide(1)->links() }}</div>
            @else
                <div class="hm-empty">
                    <p>{{ __('diwan.catalogue.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.nav.shop_all') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            @endif

            {{-- ══ The other categories — real counts, no invented ones ══ --}}
            @if($siblings->isNotEmpty())
                <section style="margin-top: var(--space-3xl); padding-top: var(--space-xl); border-top: var(--rule-hair) solid var(--color-rule);">
                    <h2 class="hm-label" style="margin-bottom: var(--space-md);">{{ __('diwan.nav.by_piece') }}</h2>

                    <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: var(--space-md) var(--space-lg);">
                        @foreach($siblings as $sibling)
                            <li>
                                <a href="{{ route('furnish.category', $sibling->slug) }}" class="hm-link hm-link--sm">
                                    {{ $sibling->name }}
                                    <span class="hm-num hm-muted" style="font-size: var(--text-xs);">
                                        {{ $sibling->products_count }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    </section>

@endsection
