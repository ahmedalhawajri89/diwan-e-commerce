{{--
  The catalogue. A filter rail on the inline-start edge — open beside the grid
  on a desktop, folded behind one button on a phone — and the grid itself.
  Filters apply when the shopper presses Apply, never on change; what is
  applied shows above the grid as chips, each of which removes itself.
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

    $bands = [
        'under_500'  => __('diwan.catalogue.under', ['amount' => App\Support\Money::format(500)]),
        '500_1500'   => __('diwan.catalogue.between', ['from' => App\Support\Money::format(500), 'to' => App\Support\Money::format(1500)]),
        'above_1500' => __('diwan.catalogue.above', ['amount' => App\Support\Money::format(1500)]),
    ];

    $sorts = [
        '' => __('diwan.catalogue.sorts.featured'),
        'price_low' => __('diwan.catalogue.sorts.price_low'),
        'price_high' => __('diwan.catalogue.sorts.price_high'),
        'date_old' => __('diwan.catalogue.sorts.old'),
        'alphabetical_az' => __('diwan.catalogue.sorts.az'),
        'alphabetical_za' => __('diwan.catalogue.sorts.za'),
    ];

    // Each applied filter as [what it says, the address without it].
    $without = function (string $key, $value = null) {
        $rest = $value === null ? null : array_values(array_diff((array) request($key, []), [$value]));

        return request()->fullUrlWithQuery([$key => $rest ?: null, 'page' => null]);
    };
    $chips = collect();
    if (request()->filled('q')) {
        $chips->push(['“'.request('q').'”', $without('q')]);
    }
    if ($fitOnly) {
        $chips->push([__('diwan.catalogue.fit_only'), $without('fit_only')]);
    }
    $chosen = array_map('intval', (array) request('categories', []));
    foreach ($categories->filter(fn ($c) => in_array($c->id, $chosen, true)) as $category) {
        $chips->push([$category->name, $without('categories', (string) $category->id)]);
    }
    foreach (array_intersect_key($bands, array_flip((array) request('prices', []))) as $value => $label) {
        $chips->push([$label, $without('prices', $value)]);
    }
    foreach ((array) request('colors', []) as $colour) {
        $chips->push([$availableColors[$colour] ?? $colour, $without('colors', $colour)]);
    }
@endphp

@section('content')
    <x-ui.page>
        <x-ui.breadcrumbs :items="[__('diwan.nav.home') => route('furnish.index'), __('diwan.catalogue.title') => null]" class="pt-8 pb-0" />

        <x-ui.page-head :title="__('diwan.catalogue.title')" :lede="__('diwan.catalogue.intro')" class="pt-6!">
            <x-slot:aside>
                @if($products->total() > 0)
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $products->total()) }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        <div class="grid gap-10 py-10 lg:grid-cols-[16rem_1fr] lg:gap-14 pb-(--section-gap)">

            {{-- ══ Filters ═════════════════════════════════════════════════
                 Nothing here submits on its own. The shopper ticks what they
                 want and presses Apply: a keyboard or screen-reader user can
                 move through the boxes without the page reloading under them,
                 and a phone does not fetch the catalogue once per tap. --}}
            <aside x-data="disclosure" @if($filtered) data-open @endif>
                <button type="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="filterForm"
                        class="flex w-full items-center justify-between min-h-12 px-4 border border-rule-2 bg-paper text-sm text-ink cursor-pointer lg:hidden">
                    <span class="inline-flex items-center gap-2">
                        <x-icon name="list" class="size-4" />{{ __('diwan.catalogue.filter_open') }}
                        @if($chips->isNotEmpty())
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-pill bg-ink text-on-accent font-mono text-[0.6875rem]">{{ $chips->count() }}</span>
                        @endif
                    </span>
                    <x-icon name="chevron-down" class="size-3.5" />
                </button>

                <form method="GET" action="{{ route('furnish.products') }}" id="filterForm"
                      x-show="open" x-cloak class="mt-3 lg:mt-0 lg:block! lg:sticky lg:top-36">
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <div class="{{ $group }} pt-0">
                        <label class="block">
                            <span class="sr-only">{{ __('diwan.catalogue.search_here') }}</span>
                            <input type="search" name="q" value="{{ request('q') }}" class="{{ $input }}"
                                   placeholder="{{ __('diwan.catalogue.search_here') }}">
                        </label>
                    </div>

                    {{-- The fit filter is the rail's first real control, not an
                         afterthought: it is why people are here. --}}
                    <div class="{{ $group }}"><fieldset class="m-0 p-0 border-0 min-w-0">
                        <legend class="{{ $groupTitle }} p-0">{{ __('diwan.catalogue.fit_filter') }}</legend>
                        @if($room)
                            <x-ui.check name="fit_only" value="1" :checked="$fitOnly">{{ __('diwan.catalogue.fit_only') }}</x-ui.check>
                            <p class="m-0 mt-1 text-xs text-muted">
                                <bdi dir="ltr" class="font-mono">{{ $room['width'] }}&times;{{ $room['length'] }}</bdi> {{ __('diwan.room.unit') }} ·
                                <button type="button" data-open-dialog="roomModal"
                                        class="inline-flex items-center min-h-11 p-0 border-0 bg-transparent text-xs text-accent-ink underline underline-offset-4 cursor-pointer">{{ __('diwan.room.change') }}</button>
                            </p>
                        @else
                            <x-ui.button variant="ghost" :block="true" data-open-dialog="roomModal">
                                <x-icon name="rulers" class="size-4" />
                                {{ __('diwan.room.set_prompt') }}
                            </x-ui.button>
                        @endif
                    </fieldset></div>

                    @if($categories->isNotEmpty())
                        <div class="{{ $group }}"><fieldset class="m-0 p-0 border-0 min-w-0">
                            <legend class="{{ $groupTitle }} p-0">{{ __('diwan.catalogue.category') }}</legend>
                            @foreach($categories as $category)
                                <x-ui.check name="categories[]" :value="$category->id"
                                            :checked="in_array($category->id, (array) request('categories', []))">{{ $category->name }}</x-ui.check>
                            @endforeach
                        </fieldset></div>
                    @endif

                    <div class="{{ $group }}"><fieldset class="m-0 p-0 border-0 min-w-0">
                        <legend class="{{ $groupTitle }} p-0">{{ __('diwan.catalogue.price') }}</legend>
                        @foreach($bands as $value => $label)
                            <x-ui.check name="prices[]" :value="$value"
                                        :checked="in_array($value, (array) request('prices', []))">{{ $label }}</x-ui.check>
                        @endforeach
                    </fieldset></div>

                    @if($availableColors->isNotEmpty())
                        <div class="{{ $group }}"><fieldset class="m-0 p-0 border-0 min-w-0">
                            <legend class="{{ $groupTitle }} p-0">{{ __('diwan.catalogue.colour') }}</legend>
                            {{-- A word beside each swatch: a colour name an operator typed
                                 ("Walnut") is not a CSS colour, and its swatch would be an
                                 empty circle with the name hidden in a tooltip a phone
                                 never shows. --}}
                            <div class="grid grid-cols-2 gap-x-3">
                                @foreach($availableColors as $colour => $colourName)
                                    <label class="flex items-center gap-2.5 min-h-11 text-sm text-neutral cursor-pointer hover:text-ink has-focus-visible:outline-2 has-focus-visible:outline-focus">
                                        <input type="checkbox" name="colors[]" value="{{ $colour }}" class="peer sr-only" @checked(in_array($colour, (array) request('colors', [])))>
                                        {{-- The one style attribute here is the swatch's real
                                             colour, which no stylesheet can know. --}}
                                        <span class="size-5 shrink-0 rounded-pill border border-rule-2 outline-offset-2 peer-checked:outline-2 peer-checked:outline-ink" style="background: {{ strtolower($colour) }};" aria-hidden="true"></span>
                                        <span class="peer-checked:text-ink peer-checked:font-medium">{{ $colourName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset></div>
                    @endif

                    <div class="flex items-center gap-4 pt-5 max-lg:sticky max-lg:bottom-0 max-lg:bg-paper max-lg:pb-4">
                        <x-ui.button type="submit" :block="true">{{ __('diwan.catalogue.apply') }}</x-ui.button>
                        @if($filtered)
                            <x-ui.link :href="route('furnish.products')" :arrow="false" tone="ink" class="shrink-0">{{ __('diwan.catalogue.clear') }}</x-ui.link>
                        @endif
                    </div>
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

                    {{-- The sort is a short menu of links: each order has an
                         address, nothing reloads while a keyboard moves through
                         it, and it works without JavaScript. --}}
                    <details class="group relative">
                        <summary class="flex items-center gap-2 min-h-11 cursor-pointer list-none text-sm [&::-webkit-details-marker]:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            <span class="text-muted">{{ __('diwan.catalogue.sort_by') }}:</span>
                            <span>{{ $sorts[request('sort', '')] ?? $sorts[''] }}</span>
                            <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                        </summary>
                        <ul class="absolute end-0 z-20 m-0 mt-2 p-2 list-none w-60 bg-paper border border-rule shadow-[0_12px_32px_var(--color-veil)]">
                            @foreach($sorts as $value => $label)
                                @php $current = (string) request('sort', '') === (string) $value; @endphp
                                <li>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => $value ?: null, 'page' => null]) }}"
                                       @if($current) aria-current="true" @endif
                                       class="flex items-center justify-between gap-3 min-h-11 px-3 text-sm no-underline text-ink hover:bg-paper-2 aria-[current=true]:font-medium">
                                        {{ $label }}
                                        @if($current)<x-icon name="check-lg" class="size-3.5" />@endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </div>

                @if($chips->isNotEmpty())
                    <ul class="m-0 mb-8 p-0 list-none flex flex-wrap items-center gap-2" aria-label="{{ __('diwan.catalogue.active') }}">
                        @foreach($chips as [$text, $without])
                            <li>
                                <a href="{{ $without }}" aria-label="{{ __('diwan.catalogue.remove_filter', ['filter' => $text]) }}"
                                   class="inline-flex items-center gap-2 min-h-11 ps-4 pe-3 rounded-pill border border-rule-2 text-sm text-ink no-underline hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                    <bdi>{{ $text }}</bdi>
                                    <x-icon name="x" class="size-3.5 text-muted" />
                                </a>
                            </li>
                        @endforeach
                        @if($chips->count() > 1)
                            <li><x-ui.link :href="route('furnish.products')" :arrow="false" tone="ink" class="ms-2">{{ __('diwan.catalogue.clear') }}</x-ui.link></li>
                        @endif
                    </ul>
                @endif

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
                                <x-product-card :product="$product" :room="$room" :i="$index" :eager="$index < 2" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-16">
                        {{ $products->onEachSide(1)->links() }}
                    </div>
                @else
                    {{-- The empty grid names its cause, and offers the one step
                         that undoes it. --}}
                    @php
                        $why = match ($empty) {
                            'query' => __('diwan.catalogue.empty_query', ['query' => request('q')]),
                            'fit' => __('diwan.catalogue.empty_fit', ['size' => $room['width'].'×'.$room['length'].' '.__('diwan.room.unit')]),
                            'past_end' => __('diwan.catalogue.empty_past_end'),
                            default => __('diwan.catalogue.empty'),
                        };
                    @endphp
                    <x-ui.empty :title="$why">
                        <x-slot:action>
                            @if($empty === 'past_end')
                                <x-ui.link :href="request()->fullUrlWithQuery(['page' => 1])">{{ __('diwan.catalogue.first_page') }}</x-ui.link>
                            @else
                                <x-ui.link :href="route('furnish.products')">{{ __('diwan.catalogue.empty_cta') }}</x-ui.link>
                            @endif
                        </x-slot:action>
                    </x-ui.empty>
                @endif
            </div>
        </div>
    </x-ui.page>
@endsection
