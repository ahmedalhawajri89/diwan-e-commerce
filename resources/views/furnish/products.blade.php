{{--
  The catalogue. A filter rail on the inline-start edge — open beside the grid
  on a desktop, folded behind one button on a phone — and the grid itself.
  Every filter submits as it changes (data-autosubmit); without JavaScript the
  Apply button inside <noscript> does it.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.catalogue.title'))
@section('description', __('diwan.catalogue.intro'))

@php
    $group = 'py-5 border-0 border-b border-rule last:border-b-0';
    $groupTitle = 'm-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted';
    $input = 'block w-full h-11 px-3 bg-paper border border-rule-2 rounded-control text-sm text-ink '
           . 'placeholder:text-muted focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
    $filtered = request()->hasAny(['q', 'categories', 'prices', 'colors', 'fit_only', 'sort']);
@endphp

@section('content')
    <x-ui.page>
        <x-ui.page-head :title="__('diwan.catalogue.title')" :lede="__('diwan.catalogue.intro')">
            <x-slot:aside>
                @if($products->total() > 0)
                    <span class="font-mono text-xs text-muted">{{ __('diwan.catalogue.count', ['count' => $products->total()]) }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        <div class="grid gap-10 py-10 lg:grid-cols-[16rem_1fr] lg:gap-14 pb-(--section-gap)">

            {{-- ══ Filters ═════════════════════════════════════════════════ --}}
            <aside x-data="disclosure" @if($filtered) data-open @endif>
                <button type="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="filterForm"
                        class="flex w-full items-center justify-between h-12 px-4 border border-rule-2 bg-paper text-sm text-ink cursor-pointer lg:hidden">
                    <span class="inline-flex items-center gap-2"><x-icon name="list" class="size-4" />{{ __('diwan.catalogue.filter_open') }}</span>
                    <x-icon name="chevron-down" class="size-3.5" />
                </button>

                <form method="GET" action="{{ route('furnish.products') }}" id="filterForm"
                      x-show="open" x-cloak class="mt-3 lg:mt-0 lg:block! lg:sticky lg:top-36">
                    <div class="{{ $group }} pt-0">
                        <label class="block">
                            <span class="sr-only">{{ __('diwan.catalogue.search_here') }}</span>
                            <input type="search" name="q" value="{{ request('q') }}" class="{{ $input }}"
                                   placeholder="{{ __('diwan.catalogue.search_here') }}">
                        </label>
                    </div>

                    {{-- The fit filter is the rail's first real control, not an
                         afterthought: it is why people are here. --}}
                    <div class="{{ $group }}">
                        <h2 class="{{ $groupTitle }}">{{ __('diwan.catalogue.fit_filter') }}</h2>
                        @if($room)
                            <x-ui.check name="fit_only" value="1" :checked="$fitOnly" data-autosubmit>{{ __('diwan.catalogue.fit_only') }}</x-ui.check>
                            <p class="m-0 mt-2 text-xs text-muted">
                                <span class="font-mono" dir="ltr">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span> ·
                                <button type="button" data-open-dialog="roomModal"
                                        class="p-0 border-0 bg-transparent text-xs text-accent-ink underline underline-offset-4 cursor-pointer">{{ __('diwan.room.change') }}</button>
                            </p>
                        @else
                            <x-ui.button variant="ghost" :block="true" data-open-dialog="roomModal">
                                <x-icon name="rulers" class="size-4" />
                                {{ __('diwan.room.set_prompt') }}
                            </x-ui.button>
                        @endif
                    </div>

                    @if($categories->isNotEmpty())
                        <div class="{{ $group }}">
                            <h2 class="{{ $groupTitle }}">{{ __('diwan.catalogue.category') }}</h2>
                            @foreach($categories as $category)
                                <x-ui.check name="categories[]" :value="$category->id" data-autosubmit
                                            :checked="in_array($category->id, (array) request('categories', []))">{{ $category->name }}</x-ui.check>
                            @endforeach
                        </div>
                    @endif

                    <div class="{{ $group }}">
                        <h2 class="{{ $groupTitle }}">{{ __('diwan.catalogue.price') }}</h2>
                        @foreach([
                            'under_100' => __('diwan.catalogue.under', ['amount' => 100]),
                            '100_300'   => __('diwan.catalogue.between', ['from' => 100, 'to' => 300]),
                            'above_300' => __('diwan.catalogue.above', ['amount' => 300]),
                        ] as $value => $label)
                            <x-ui.check name="prices[]" :value="$value" data-autosubmit
                                        :checked="in_array($value, (array) request('prices', []))">{{ $label }}</x-ui.check>
                        @endforeach
                    </div>

                    @if($availableColors->isNotEmpty())
                        <div class="{{ $group }}">
                            <h2 class="{{ $groupTitle }}">{{ __('diwan.catalogue.colour') }}</h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableColors as $colour)
                                    @php $active = in_array($colour, (array) request('colors', [])); @endphp
                                    {{-- The one style attribute here is the swatch's real
                                         colour, which no stylesheet can know. --}}
                                    <label title="{{ $colour }}" style="background: {{ strtolower($colour) }};"
                                           class="relative size-8 rounded-pill border border-rule-2 cursor-pointer outline-offset-2 has-checked:outline-2 has-checked:outline-ink has-focus-visible:outline-2 has-focus-visible:outline-focus">
                                        <input type="checkbox" name="colors[]" value="{{ $colour }}" class="sr-only"
                                               @checked($active) data-autosubmit>
                                        <span class="sr-only">{{ $colour }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($filtered)
                        <div class="{{ $group }}">
                            <x-ui.link :href="route('furnish.products')" :arrow="false" tone="ink">{{ __('diwan.catalogue.clear') }}</x-ui.link>
                        </div>
                    @endif

                    <noscript>
                        <x-ui.button type="submit" :block="true" class="mt-4">{{ __('diwan.catalogue.apply') }}</x-ui.button>
                    </noscript>
                </form>
            </aside>

            {{-- ══ Grid ════════════════════════════════════════════════════ --}}
            <div class="min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <p class="m-0 text-xs text-muted">
                        @if($products->total() > 0)
                            {{ __('diwan.catalogue.showing', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}
                        @endif
                    </p>

                    <form method="GET" action="{{ route('furnish.products') }}" class="flex items-center gap-3">
                        @foreach(request()->except(['sort', 'page']) as $key => $value)
                            @foreach((array) $value as $item)
                                <input type="hidden" name="{{ $key }}{{ is_array($value) ? '[]' : '' }}" value="{{ $item }}">
                            @endforeach
                        @endforeach

                        <label for="sortSelect" class="whitespace-nowrap text-xs uppercase tracking-label text-muted">{{ __('diwan.catalogue.sort_by') }}</label>
                        <select id="sortSelect" name="sort" data-autosubmit class="{{ $input }} w-auto h-10 cursor-pointer">
                            <option value="">{{ __('diwan.catalogue.sorts.featured') }}</option>
                            <option value="date_new" @selected(request('sort') === 'date_new')>{{ __('diwan.catalogue.sorts.new') }}</option>
                            <option value="date_old" @selected(request('sort') === 'date_old')>{{ __('diwan.catalogue.sorts.old') }}</option>
                            <option value="alphabetical_az" @selected(request('sort') === 'alphabetical_az')>{{ __('diwan.catalogue.sorts.az') }}</option>
                            <option value="alphabetical_za" @selected(request('sort') === 'alphabetical_za')>{{ __('diwan.catalogue.sorts.za') }}</option>
                        </select>
                    </form>
                </div>

                @unless($room)
                    <x-ui.note class="mb-8">
                        {{ __('diwan.room.body') }}
                        <x-ui.link data-open-dialog="roomModal" class="ms-1">{{ __('diwan.room.set_prompt') }}</x-ui.link>
                    </x-ui.note>
                @endunless

                @if($products->count() > 0)
                    <div class="grid grid-cols-2 xl:grid-cols-3 gap-x-4 gap-y-12 lg:gap-x-6" data-reveal-group>
                        @foreach($products as $index => $product)
                            <div data-reveal>
                                <x-product-card :product="$product" :room="$room" :i="$index" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-16">
                        {{ $products->onEachSide(1)->links() }}
                    </div>
                @else
                    <x-ui.empty :title="__('diwan.catalogue.empty')">
                        <x-slot:action>
                            <x-ui.link :href="route('furnish.products')">{{ __('diwan.catalogue.empty_cta') }}</x-ui.link>
                        </x-slot:action>
                    </x-ui.empty>
                @endif
            </div>
        </div>
    </x-ui.page>
@endsection
