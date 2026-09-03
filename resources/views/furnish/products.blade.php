{{--
  Hallmark · genre: modern-minimal · macrostructure: 11 Catalogue · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  F6 knobs: card ratio = 4/3 · density = 4-up · micro-action = save
  Filter rail on the inline-start edge; card-internal links only; no global CTA
  competing with the grid.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.catalogue.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-head">
                <div>
                    <h1 class="hm-head__title">{{ __('diwan.catalogue.title') }}</h1>
                    <p class="hm-muted mb-0" style="font-size: var(--text-sm);">{{ __('diwan.catalogue.intro') }}</p>
                </div>
            </div>

            <div class="hm-cat">
                {{-- ══ Filter rail ═══════════════════════════════════════ --}}
                <aside>
                    <form method="GET" action="{{ route('furnish.products') }}" class="hm-rail" id="filterForm">
                        <div class="hm-rail__group">
                            <label class="hm-field" style="margin: 0;">
                                <span class="hm-visually-hidden">{{ __('diwan.catalogue.search_here') }}</span>
                                <input type="search" name="q" value="{{ request('q') }}"
                                       placeholder="{{ __('diwan.catalogue.search_here') }}">
                            </label>
                        </div>

                        {{-- The fit filter is the rail's first real control, not
                             an afterthought: it is why people are here. --}}
                        <div class="hm-rail__group">
                            <h3>{{ __('diwan.catalogue.fit_filter') }}</h3>
                            @if($room)
                                <label class="hm-check">
                                    <input type="checkbox" name="fit_only" value="1" {{ $fitOnly ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <span>{{ __('diwan.catalogue.fit_only') }}</span>
                                </label>
                                <p class="hm-field__hint" style="margin-top: var(--space-2xs);">
                                    <span class="hm-num">{{ $room['width'] }}&times;{{ $room['length'] }}</span> {{ __('diwan.room.unit') }} ·
                                    <button type="button" class="hm-link hm-link--sm" data-bs-toggle="modal" data-bs-target="#roomModal">
                                        {{ __('diwan.room.change') }}
                                    </button>
                                </p>
                            @else
                                <button type="button" class="hm-btn hm-btn--ghost hm-btn--block"
                                        data-bs-toggle="modal" data-bs-target="#roomModal">
                                    <i class="bi bi-rulers" aria-hidden="true"></i>
                                    {{ __('diwan.room.set_prompt') }}
                                </button>
                            @endif
                        </div>

                        @if($categories->isNotEmpty())
                            <div class="hm-rail__group">
                                <h3>{{ __('diwan.catalogue.category') }}</h3>
                                @foreach($categories as $category)
                                    <label class="hm-check">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                               @checked(in_array($category->id, (array) request('categories', [])))
                                               onchange="this.form.submit()">
                                        <span>{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="hm-rail__group">
                            <h3>{{ __('diwan.catalogue.price') }}</h3>
                            @foreach([
                                'under_100' => __('diwan.catalogue.under', ['amount' => 100]),
                                '100_300'   => __('diwan.catalogue.between', ['from' => 100, 'to' => 300]),
                                'above_300' => __('diwan.catalogue.above', ['amount' => 300]),
                            ] as $value => $label)
                                <label class="hm-check">
                                    <input type="checkbox" name="prices[]" value="{{ $value }}"
                                           @checked(in_array($value, (array) request('prices', [])))
                                           onchange="this.form.submit()">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        @if($availableColors->isNotEmpty())
                            <div class="hm-rail__group">
                                <h3>{{ __('diwan.catalogue.colour') }}</h3>
                                <div class="hm-swatches">
                                    @foreach($availableColors as $colour)
                                        @php $active = in_array($colour, (array) request('colors', [])); @endphp
                                        <label class="hm-swatch" aria-pressed="{{ $active ? 'true' : 'false' }}"
                                               style="background: {{ strtolower($colour) }};"
                                               title="{{ $colour }}">
                                            <input type="checkbox" name="colors[]" value="{{ $colour }}"
                                                   class="hm-visually-hidden" @checked($active)
                                                   onchange="this.form.submit()">
                                            <span class="hm-visually-hidden">{{ $colour }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(request()->hasAny(['q', 'categories', 'prices', 'colors', 'fit_only', 'sort']))
                            <div class="hm-rail__group">
                                <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                                    {{ __('diwan.catalogue.clear') }}
                                </a>
                            </div>
                        @endif

                        <noscript>
                            <button type="submit" class="hm-btn hm-btn--primary hm-btn--block" style="margin-top: var(--space-md);">
                                {{ __('diwan.catalogue.apply') }}
                            </button>
                        </noscript>
                    </form>
                </aside>

                {{-- ══ Grid ══════════════════════════════════════════════ --}}
                <div>
                    <div class="hm-toolbar">
                        <span class="hm-toolbar__count">
                            @if($products->total() > 0)
                                {{ __('diwan.catalogue.showing', [
                                    'from'  => $products->firstItem(),
                                    'to'    => $products->lastItem(),
                                    'total' => $products->total(),
                                ]) }}
                            @endif
                        </span>

                        <form method="GET" action="{{ route('furnish.products') }}"
                              class="d-flex align-items-center gap-2">
                            @foreach(request()->except(['sort', 'page']) as $key => $value)
                                @foreach((array) $value as $item)
                                    <input type="hidden" name="{{ $key }}{{ is_array($value) ? '[]' : '' }}" value="{{ $item }}">
                                @endforeach
                            @endforeach

                            <label for="sortSelect" class="hm-label" style="margin: 0;">{{ __('diwan.catalogue.sort_by') }}</label>
                            <select id="sortSelect" name="sort" class="hm-input" style="width: auto;"
                                    onchange="this.form.submit()">
                                <option value="">{{ __('diwan.catalogue.sorts.featured') }}</option>
                                <option value="date_new" @selected(request('sort') === 'date_new')>{{ __('diwan.catalogue.sorts.new') }}</option>
                                <option value="date_old" @selected(request('sort') === 'date_old')>{{ __('diwan.catalogue.sorts.old') }}</option>
                                <option value="alphabetical_az" @selected(request('sort') === 'alphabetical_az')>{{ __('diwan.catalogue.sorts.az') }}</option>
                                <option value="alphabetical_za" @selected(request('sort') === 'alphabetical_za')>{{ __('diwan.catalogue.sorts.za') }}</option>
                            </select>
                        </form>
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

                        <div style="margin-top: var(--space-2xl);">
                            {{ $products->onEachSide(1)->links() }}
                        </div>
                    @else
                        <div class="hm-empty">
                            <p>{{ __('diwan.catalogue.empty') }}</p>
                            <a href="{{ route('furnish.products') }}" class="hm-link">
                                {{ __('diwan.catalogue.empty_cta') }}
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection
