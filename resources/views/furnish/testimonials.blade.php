{{--
  Reads the reviews table where status = approved. If there are none, the page
  says so; nothing is invented to fill the space.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.testimonials.title'))
@section('description', __('diwan.testimonials.body'))

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.testimonials.lede')" :lede="__('diwan.testimonials.body')" />

        @if($reviews->count() > 0)
            {{-- Columns rather than a grid of equal boxes: a long review and a
                 short one should each take the room they need. --}}
            <div class="mt-12 columns-1 md:columns-2 lg:columns-3 gap-6" data-reveal-group>
                @foreach($reviews as $review)
                    <figure class="break-inside-avoid m-0 mb-6 p-7 bg-paper-2" data-reveal>
                        <x-star-rating :rating="$review->rating" class="m-0" />
                        <blockquote class="m-0 mt-4 font-display text-xl leading-snug">&ldquo;{{ $review->comment }}&rdquo;</blockquote>
                        <figcaption class="mt-5 pt-4 border-0 border-t border-rule text-xs text-muted">
                            <span class="block text-sm text-ink">{{ $review->user?->name }}</span>
                            @if($review->product)
                                <a href="{{ route('furnish.product.show', $review->product->slug) }}" class="text-accent-ink underline underline-offset-4">
                                    {{ __('diwan.testimonials.on', ['product' => $review->product->name]) }}
                                </a>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-10">{{ $reviews->onEachSide(1)->links() }}</div>
        @else
            <x-ui.empty :title="__('diwan.testimonials.empty')" class="mt-10">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.nav.catalogue') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>
        @endif
    </x-ui.page>
@endsection
