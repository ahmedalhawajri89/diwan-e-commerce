{{--
  Rooms: a photograph that sets the scene, beside the pieces from that room's
  own category. Nothing is pinned onto the photographs — they are not of these
  pieces, and a marker that opened something not in the picture would be the
  same lie as an invented dimension.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.lookbook.title'))
@section('description', __('diwan.lookbook.body'))

@section('content')
    <x-ui.page>
        <x-ui.page-head :title="__('diwan.lookbook.lede')" :lede="__('diwan.lookbook.body')" />
    </x-ui.page>

    @forelse($looks as $index => $look)
        <section class="py-(--section-gap) {{ $index > 0 ? 'border-0 border-t border-rule' : '' }}">
            <x-ui.page class="grid gap-10 lg:grid-cols-[7fr_5fr] lg:gap-16 lg:items-center">
                <div class="relative aspect-[4/5] overflow-hidden bg-paper-2 {{ $index % 2 ? 'lg:order-last' : '' }}" data-reveal>
                    <x-picture :src="$look['image']" :alt="__('diwan.'.$look['title'])"
                               sizes="(max-width: 64rem) 100vw, 50rem" :width="1600" :height="2000"
                               loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async"
                               class="absolute inset-0 size-full object-cover" />
                </div>

                <div data-reveal>
                    <p class="m-0 mb-3 font-mono text-xs text-accent-ink" dir="ltr">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                    <h2 class="m-0 font-display font-medium rtl:font-semibold text-3xl lg:text-display-s leading-tight">{{ __('diwan.'.$look['title']) }}</h2>
                    <p class="m-0 mt-3 text-sm text-muted">{{ __('diwan.lookbook.pieces', ['category' => $look['category']->name]) }}</p>

                    <ul class="m-0 mt-8 p-0 list-none border-0 border-t border-rule">
                        @foreach($look['pieces'] as $product)
                            @php $variant = $product->primaryVariant(); @endphp
                            <li>
                                <a href="{{ route('furnish.product.show', $product->slug) }}"
                                   class="group flex items-center gap-4 py-4 border-0 border-b border-rule text-ink no-underline">
                                    <span class="relative block w-14 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                                        <x-picture :src="$product->imageUrl()" alt="" sizes="3.5rem" :width="112" :height="140" loading="lazy"
                                                   class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block font-display text-lg leading-snug group-hover:underline underline-offset-4">{{ $product->name }}</span>
                                        <span class="block mt-1"><x-scale-rule :variant="$variant" /></span>
                                    </span>
                                    @if($variant?->price !== null)
                                        <span class="font-mono text-sm text-accent-ink">{{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        <x-ui.link :href="route('furnish.category', $look['category']->slug)">{{ $look['category']->name }}</x-ui.link>
                    </div>
                </div>
            </x-ui.page>
        </section>
    @empty
        <x-ui.page class="py-(--section-gap)">
            <x-ui.empty :title="__('diwan.lookbook.empty')">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.nav.catalogue') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.page>
    @endforelse
@endsection
