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

            <nav class="hm-mb-lg" aria-label="{{ __('diwan.nav.catalogue') }}">
                <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                    <x-icon name="arrow-left" />
                    {{ __('diwan.nav.catalogue') }}
                </a>
            </nav>

            <div class="hm-head">
                <div>
                    <h1 class="hm-head__title">{{ $category->name }}</h1>
                    <p class="hm-muted mb-0 hm-t-sm">
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
                <p class="hm-note hm-note--info hm-mb-lg">
                    <x-icon name="rulers" />
                    <span>
                        {{ __('diwan.room.body') }}
                        <button type="button" class="hm-link hm-link--sm" data-bs-toggle="modal" data-bs-target="#roomModal">
                            {{ __('diwan.room.set_prompt') }}
                            <x-icon name="arrow-right" />
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

                <div class="hm-mt-2xl">{{ $products->onEachSide(1)->links() }}</div>
            @else
                <div class="hm-empty">
                    <p>{{ __('diwan.catalogue.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.nav.shop_all') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @endif

            {{-- ══ The other categories — real counts, no invented ones ══ --}}
            @if($siblings->isNotEmpty())
                <section class="hm-mt-3xl hm-pt-xl hm-ruled-t">
                    <h2 class="hm-label hm-mb-md">{{ __('diwan.nav.by_piece') }}</h2>

                    <ul class="hm-plainlist hm-plainlist--wrap">
                        @foreach($siblings as $sibling)
                            <li>
                                <a href="{{ route('furnish.category', $sibling->slug) }}" class="hm-link hm-link--sm">
                                    {{ $sibling->name }}
                                    <span class="hm-num hm-muted hm-t-xs">
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
