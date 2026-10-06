{{--
  A category at its own address. The catalogue filter did this already, but
  `?categories[]=3` is not a link anybody shares and not a URL a search engine
  keeps.

  No filter rail here on purpose: the page is already one filter. Offering the
  whole rail would just be the catalogue with a different heading. The way to
  narrow further is the catalogue itself, linked at the top.
--}}
@extends('layouts.furnish')

@section('title', $category->name)

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        <x-ui.breadcrumbs :items="[
            __('diwan.nav.home') => route('furnish.index'),
            __('diwan.nav.catalogue') => route('furnish.products'),
            $category->name => null,
        ]" class="pt-8 pb-0" />

        <x-ui.page-head :title="$category->name" :lede="__('diwan.catalogue.intro')" class="pt-6!">
            <x-slot:aside>
                @if($products->total() > 0)
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $products->total()) }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        <div class="pt-10">
            @unless($room)
                <x-ui.note class="mb-8">
                    {{ __('diwan.room.body') }}
                    <x-ui.link data-open-dialog="roomModal" class="ms-1">{{ __('diwan.room.set_prompt') }}</x-ui.link>
                </x-ui.note>
            @endunless

            @if($products->count() > 0)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-12 lg:gap-x-6" data-reveal-group>
                    @foreach($products as $index => $product)
                        <div data-reveal>
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-16">{{ $products->onEachSide(1)->links() }}</div>
            @else
                <x-ui.empty :title="__('diwan.catalogue.empty')">
                    <x-slot:action>
                        <x-ui.link :href="route('furnish.products')">{{ __('diwan.nav.shop_all') }}</x-ui.link>
                    </x-slot:action>
                </x-ui.empty>
            @endif
        </div>

        {{-- ══ The other categories — real counts, no invented ones ══════════ --}}
        @if($siblings->isNotEmpty())
            <section class="mt-20 pt-10 border-0 border-t border-rule">
                <h2 class="m-0 mb-5 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('diwan.nav.by_piece') }}</h2>

                <ul class="flex flex-wrap gap-3 m-0 p-0 list-none">
                    @foreach($siblings as $sibling)
                        <li>
                            <a href="{{ route('furnish.category', $sibling->slug) }}"
                               class="inline-flex items-center gap-2.5 h-11 px-5 border border-rule-2 rounded-pill text-sm text-ink no-underline transition-colors hover:border-ink">
                                {{ $sibling->name }}
                                <span class="font-mono text-xs text-muted">{{ $sibling->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </x-ui.page>
@endsection
