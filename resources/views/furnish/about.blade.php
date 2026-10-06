{{--
  The house's story: a headline, the workshop photograph opening as it
  arrives, two paragraphs, and three principles as a numbered spec sheet —
  rows, not icon tiles.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.about.title'))
@section('description', __('diwan.about.lede'))
@section('og_image', asset('assets/images/workshop.jpg'))

@section('content')
    <x-ui.page class="pt-16 lg:pt-24">
        <p class="m-0 mb-5 font-label text-xs font-medium uppercase tracking-label text-muted motion-safe:animate-fade-up">{{ __('diwan.about.title') }}</p>
        <h1 class="m-0 max-w-[16ch] font-display font-medium rtl:font-semibold text-display leading-tight tracking-display motion-safe:animate-fade-up [animation-delay:150ms]">{{ __('diwan.about.lede') }}</h1>
    </x-ui.page>

    <x-ui.page class="mt-14">
        <div class="relative aspect-[4/3] md:aspect-[16/9] overflow-hidden bg-paper-2 unveil">
            <x-picture :src="asset('assets/images/workshop.jpg')" :alt="__('diwan.about.alt_one')"
                       sizes="(max-width: 88rem) 100vw, 88rem" :width="2400" :height="1350"
                       fetchpriority="high" decoding="async"
                       class="absolute inset-0 size-full object-cover unveil-zoom" />
        </div>
    </x-ui.page>

    <x-ui.page class="grid gap-8 py-(--section-gap) lg:grid-cols-[5fr_7fr] lg:gap-20">
        <div></div>
        <div class="grid gap-6 max-w-[60ch]" data-reveal>
            <p class="m-0 font-display text-2xl leading-snug">{{ __('diwan.about.body_one') }}</p>
            <p class="m-0 leading-body text-neutral">{{ __('diwan.about.body_two') }}</p>
            <div class="mt-2"><x-ui.link :href="route('furnish.products')">{{ __('diwan.about.cta') }}</x-ui.link></div>
        </div>
    </x-ui.page>

    {{-- ══ The principles, as a spec sheet ═══════════════════════════════ --}}
    <section class="bg-paper-2 py-(--section-gap)">
        <x-ui.page>
            <h2 class="m-0 mb-10 font-display font-medium rtl:font-semibold text-3xl" data-reveal>{{ __('diwan.about.principle') }}</h2>

            <ol class="m-0 p-0 list-none border-0 border-t border-rule-2" data-reveal-group>
                @foreach([
                    ['t' => __('diwan.about.p_one_t'),   'b' => __('diwan.about.p_one_b')],
                    ['t' => __('diwan.about.p_two_t'),   'b' => __('diwan.about.p_two_b')],
                    ['t' => __('diwan.about.p_three_t'), 'b' => __('diwan.about.p_three_b')],
                ] as $index => $principle)
                    <li class="grid gap-4 py-8 border-0 border-b border-rule-2 md:grid-cols-[4rem_1fr_2fr] md:gap-10" data-reveal>
                        <span class="font-mono text-sm text-accent-ink" dir="ltr">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="m-0 font-display text-2xl leading-snug">{{ $principle['t'] }}</h3>
                        <p class="m-0 max-w-measure leading-body text-ink-2">{{ $principle['b'] }}</p>
                    </li>
                @endforeach
            </ol>
        </x-ui.page>
    </section>
@endsection
