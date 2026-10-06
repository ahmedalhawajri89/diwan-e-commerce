{{--
  The home page. A photograph first, then the way in by room, what people
  keep, what is new, how ordering works, a room you can drag the walls of,
  how it is made, and what buyers said. Every number on it is read from the
  catalogue.

  Motion is quiet and always optional: the photograph settles, the headline
  rises line by line, sections ease in as they arrive (data-reveal), and two
  photographs move with the scroll itself. With reduced motion asked for, or
  without JavaScript, the page is simply still.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.home.title'))

{{-- The header lies clear over the photograph until it is scrolled past. --}}
@section('header_overlay', true)

@php
    use App\Support\Fit;

    $page    = 'mx-auto w-full max-w-page px-(--page-gutter)';
    $section = 'py-(--section-gap)';
    $eyebrow = 'm-0 mb-4 font-label text-xs font-medium uppercase tracking-label';
    $title   = 'm-0 font-display font-medium rtl:font-semibold text-3xl leading-snug tracking-display';
    $link    = 'group/link inline-flex items-center gap-2 text-sm text-accent-ink no-underline border-0 border-b border-current pb-1 transition-colors hover:text-ink';
    $arrow   = 'size-3.5 transition-transform duration-300 ease-out group-hover/link:translate-x-1 rtl:group-hover/link:-translate-x-1';

    // Each headline line rises a beat after the one above it.
    $lineDelay = ['[animation-delay:250ms]', '[animation-delay:420ms]', '[animation-delay:590ms]'];

    // The fit demo works on one real piece with all three measurements.
    $demoVariant = $fitDemo?->primaryVariant();
    $demoRoom = ['width' => $room['width'] ?? 350, 'length' => $room['length'] ?? 480];

    if ($demoVariant?->hasDimensions()) {
        $demoAnswer = Fit::assess($demoVariant, $demoRoom);
        $fitConfig = [
            'piece' => ['w' => $demoVariant->width_cm, 'd' => $demoVariant->depth_cm, 'h' => $demoVariant->height_cm],
            'rules' => Fit::rules(),
            'room' => $demoRoom,
            'labels' => [
                'fits' => __('diwan.fit.fits'),
                'tight' => __('diwan.fit.tight'),
                'no' => __('diwan.fit.no'),
                'notes' => __('diwan.fit.note'),
                'fitsExplain' => __('diwan.fit.explain.fits'),
                'share' => __('diwan.fit.share'),
            ],
        ];
    }

    $range = 'block w-full mt-2 range-brass';
@endphp

@section('content')

    {{-- ══ The photograph ══════════════════════════════════════════════════ --}}
    <section data-hero
             class="relative isolate flex items-end -mt-(--header-h) h-svh min-h-[36rem] max-h-[64rem] overflow-hidden bg-deep">
        <div class="absolute inset-0 -z-10 parallax-drift">
            <x-picture :src="asset('assets/images/hero-living-room.jpg')"
                       :alt="__('diwan.home.hero_alt')"
                       sizes="100vw" :width="2560" :height="1440"
                       fetchpriority="high" decoding="async"
                       class="block size-full object-cover motion-safe:animate-settle" />
        </div>

        {{-- A fall-off under the headline, and a band at the top for the clear
             header's words. The photograph is already low-lit; no even wash. --}}
        <div class="absolute inset-0 -z-10 bg-linear-to-t from-deep/85 via-photo to-veil" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-56 -z-10 bg-linear-to-b from-scrim to-transparent" aria-hidden="true"></div>

        <div class="{{ $page }} flex items-end justify-between gap-10 pb-14 lg:pb-20 text-on-deep scroll-fade">
            <div>
                <h1 class="m-0 text-on-deep font-display font-medium rtl:font-semibold text-display leading-tight tracking-display">
                    @foreach(__('diwan.home.lede_lines') as $i => $line)
                        {{-- Each line rises out of its own mask. The padding
                             keeps Arabic marks and descenders inside it. --}}
                        <span class="block overflow-hidden pt-[0.08em] -mt-[0.08em] pb-[0.12em] -mb-[0.12em]">
                            <span class="block motion-safe:animate-rise-line {{ $lineDelay[$i] ?? '' }}">{{ $line }}</span>
                        </span>
                    @endforeach
                </h1>

                <p class="m-0 mt-7 max-w-[44ch] text-md leading-body text-on-deep motion-safe:animate-fade-up [animation-delay:800ms]">
                    {{ __('diwan.home.hero_body') }}
                </p>

                <div class="flex flex-wrap items-center gap-x-8 gap-y-4 mt-10 motion-safe:animate-fade-up [animation-delay:950ms]">
                    {{-- One filled button per view. The second action is a link. --}}
                    <x-ui.button variant="light" :href="route('furnish.products')">{{ __('diwan.home.hero_cta') }}</x-ui.button>
                    <button type="button" data-open-dialog="roomModal"
                            class="group/link inline-flex items-center gap-2 p-0 pb-1 border-0 border-b border-on-deep-muted bg-transparent text-sm text-on-deep cursor-pointer transition-colors hover:border-on-deep">
                        {{ __('diwan.home.hero_second') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </button>
                </div>
            </div>

            {{-- The scroll cue: a hairline drawing itself downwards. --}}
            <div class="hidden md:flex flex-col items-center gap-4 shrink-0 motion-safe:animate-fade-up [animation-delay:1300ms]" aria-hidden="true">
                <span class="font-label text-[0.6875rem] uppercase tracking-label text-on-deep-muted">{{ __('diwan.home.scroll') }}</span>
                <span class="block w-px h-16 bg-on-deep motion-safe:animate-cue"></span>
            </div>
        </div>
    </section>

    {{-- ══ Shop by room ════════════════════════════════════════════════════
         Every room with something in it: its own photograph, its real count,
         its own line. The words sit on a deep fall-off, not the photograph's
         luck — over a pale bed the count was 1.9:1. --}}
    @if($categoryTiles->count() >= 2)
        <section class="{{ $section }}">
            <div class="{{ $page }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                    <h2 class="{{ $title }}">{{ __('diwan.home.tiles_title') }}</h2>
                    <a href="{{ route('furnish.products') }}" class="{{ $link }}">
                        {{ __('diwan.home.tiles_all') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>

                <ul class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6 m-0 p-0 list-none" data-reveal-group>
                    @foreach($categoryTiles as $category)
                        <li data-reveal>
                            <a href="{{ route('furnish.category', $category->slug) }}"
                               class="group relative isolate flex items-end aspect-[4/5] lg:aspect-[5/4] overflow-hidden bg-paper-2 text-on-deep no-underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                <x-picture :src="$category->products->first()?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')"
                                           alt="" sizes="(max-width: 64rem) 46vw, 28rem"
                                           :width="1600" :height="2000" loading="lazy" decoding="async"
                                           class="absolute inset-0 -z-10 size-full object-cover transition-transform duration-(--dur-slow) ease-out group-hover:scale-[1.05]" />
                                <span class="absolute inset-x-0 bottom-0 h-3/4 -z-10 bg-linear-to-t from-deep/90 via-deep/55 to-transparent" aria-hidden="true"></span>

                                <span class="block w-full p-4 lg:p-6">
                                    <span class="flex items-end justify-between gap-3">
                                        <span class="font-display text-xl lg:text-2xl leading-snug">{{ $category->name }}</span>
                                        <x-icon name="arrow-right" class="size-4 mb-1.5 opacity-0 -translate-x-2 rtl:translate-x-2 transition-[opacity,translate] duration-500 ease-out group-hover:opacity-100 group-hover:translate-x-0" />
                                    </span>
                                    {{-- A hairline that draws itself under the name on hover. --}}
                                    <span class="block h-px mt-2 bg-on-deep origin-left rtl:origin-right scale-x-0 transition-transform duration-700 ease-out group-hover:scale-x-100 group-focus-visible:scale-x-100" aria-hidden="true"></span>
                                    @if($category->description)
                                        <span class="hidden lg:block mt-2 text-sm leading-snug text-on-deep max-w-[36ch]">{{ $category->description }}</span>
                                    @endif
                                    <span class="block mt-2 text-xs text-on-deep">
                                        {{ trans_choice('diwan.piece_count', $category->products_count) }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ══ Most saved ══════════════════════════════════════════════════════ --}}
    @if($featuredProducts->isNotEmpty())
        <section class="{{ $section }} pt-0">
            <div class="{{ $page }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="{{ $eyebrow }} text-muted">{{ __('diwan.home.most_saved_note') }}</p>
                        <h2 class="{{ $title }}">{{ __('diwan.home.most_saved') }}</h2>
                    </div>
                    <a href="{{ route('furnish.products') }}" class="{{ $link }}">
                        {{ __('diwan.home.catalogue_cta') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-12 lg:gap-x-6" data-reveal-group>
                    @foreach($featuredProducts as $index => $product)
                        <div data-reveal>
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ══ Just in ═════════════════════════════════════════════════════════
         The newest pieces, minus any already shown as most saved. A row that
         scrolls sideways on a phone, so eight pieces do not cost eight
         screens of scrolling. --}}
    @if($newest->isNotEmpty())
        <section class="{{ $section }} pt-0">
            <div class="{{ $page }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="{{ $eyebrow }} text-muted">{{ __('diwan.home.new_note') }}</p>
                        <h2 class="{{ $title }}">{{ __('diwan.home.new_title') }}</h2>
                    </div>
                    <a href="{{ route('furnish.products') }}" class="{{ $link }}">
                        {{ __('diwan.home.catalogue_cta') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>

                <ul class="m-0 p-0 list-none flex gap-4 lg:gap-6 overflow-x-auto snap-x snap-mandatory pb-3 [scrollbar-width:thin]">
                    @foreach($newest as $index => $product)
                        <li class="shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4.5rem)/4)] snap-start">
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ══ Ordering, in three steps ════════════════════════════════════════
         What happens after Add to bag, said before anyone has to ask: no
         account to look, nothing charged online, a call before delivery.
         Each line is what the checkout actually does. --}}
    <section class="{{ $section }} pt-0">
        <div class="{{ $page }}">
            <h2 class="{{ $title }} mb-10" data-reveal>{{ __('diwan.home.order_title') }}</h2>
            <ol class="m-0 p-0 list-none grid gap-px bg-rule md:grid-cols-3 border-0 border-y border-rule" data-reveal-group>
                @foreach(__('diwan.home.order_steps') as $n => [$stepTitle, $stepBody])
                    <li class="bg-paper py-8 md:px-8 md:first:ps-0 md:last:pe-0" data-reveal>
                        <span class="block font-mono text-sm text-accent-ink" aria-hidden="true">0{{ $n + 1 }}</span>
                        <h3 class="m-0 mt-4 font-display font-medium text-2xl leading-snug">{{ $stepTitle }}</h3>
                        <p class="m-0 mt-3 max-w-[40ch] text-sm leading-body text-neutral">{{ $stepBody }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ══ The measurement — the one deep band, and the one you can touch ══ --}}
    <section class="bg-deep text-on-deep overflow-hidden">
        <div class="{{ $page }} {{ $section }} grid gap-14 lg:grid-cols-[5fr_7fr] lg:items-center lg:gap-20">
            <div data-reveal>
                <p class="{{ $eyebrow }} text-brass">{{ __('diwan.room.label') }}</p>
                <h2 class="{{ $title }} text-on-deep text-display-s leading-tight">{{ __('diwan.home.fit_title') }}</h2>
                <p class="m-0 mt-6 max-w-[46ch] leading-body text-on-deep-muted">{{ __('diwan.home.fit_body') }}</p>

                {{-- How the system works, in three plain steps. --}}
                <ol class="m-0 mt-10 p-0 list-none border-t border-deep-rule">
                    @foreach(__('diwan.home.fit_steps') as $n => $step)
                        <li class="flex gap-5 py-4 border-b border-deep-rule">
                            <span class="font-mono text-xs text-brass pt-1" dir="ltr">0{{ $n + 1 }}</span>
                            <span class="text-sm leading-body text-on-deep">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>

                @unless(isset($fitConfig))
                    <div class="mt-10">
                        <x-ui.button variant="light" data-open-dialog="roomModal">
                            <x-icon name="rulers" class="size-4" />
                            {{ __('diwan.home.fit_cta') }}
                        </x-ui.button>
                    </div>
                @endunless
            </div>

            {{-- Drag the walls; one real piece answers as they move. The
                 sliders are the room form's own fields, so saving them is
                 one press. Without JavaScript they still submit. --}}
            @isset($fitConfig)
                <form x-data="fitDemo" data-fit="{{ json_encode($fitConfig) }}"
                      method="POST" action="{{ route('room.store') }}"
                      class="bg-paper text-ink" data-reveal>
                    @csrf

                    {{-- The piece on test: the largest in the catalogue. --}}
                    <a href="{{ route('furnish.product.show', $fitDemo->slug) }}"
                       class="group flex items-center gap-4 px-6 sm:px-8 py-4 border-b border-rule text-ink no-underline">
                        <span class="relative block size-14 shrink-0 overflow-hidden bg-paper-2">
                            <x-picture :src="$fitDemo->imageUrl()" alt="" sizes="56px" :width="112" :height="112"
                                       loading="lazy" decoding="async"
                                       class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-[0.6875rem] uppercase tracking-label text-muted">{{ __('diwan.home.fit_testing') }}</span>
                            <span class="block font-display text-xl leading-snug truncate">{{ $fitDemo->name }}</span>
                        </span>
                        <span class="hidden sm:inline font-mono text-xs text-muted" dir="ltr">
                            {{ $demoVariant->width_cm }} × {{ $demoVariant->depth_cm }} × {{ $demoVariant->height_cm }} {{ __('diwan.room.unit') }}
                        </span>
                    </a>

                    <div class="p-6 sm:p-8">
                        {{-- The answer: a word, always; colour only seconds it. --}}
                        <div class="flex flex-wrap items-center justify-between gap-3" aria-live="polite">
                            <p class="inline-flex items-center gap-3 m-0 font-display text-3xl leading-none" :class="verdictClass">
                                <span class="block size-2.5 rounded-pill bg-current" aria-hidden="true"></span>
                                <span x-text="verdict">{{ __('diwan.fit.'.$demoAnswer['status']) }}</span>
                            </p>
                            <p class="m-0 px-3 py-1.5 bg-paper-2 font-mono text-xs text-neutral" x-text="shareText">
                                {{ __('diwan.fit.share', ['percent' => number_format($demoAnswer['share'] * 100, 1)]) }}
                            </p>
                        </div>
                        <p class="m-0 mt-3 min-h-[1lh] text-sm text-muted" x-text="reason">
                            {{ $demoAnswer['note'] ? __('diwan.fit.note.'.$demoAnswer['note']) : __('diwan.fit.explain.fits') }}
                        </p>

                        {{-- The plan, in centimetres, with the walls' lengths
                             written along them. --}}
                        <div class="mt-6 grid grid-cols-[auto_1fr] gap-2" dir="ltr">
                            <span></span>
                            <span class="text-center font-mono text-[0.6875rem] text-muted">
                                &larr; <span x-text="width">{{ $demoRoom['width'] }}</span> {{ __('diwan.room.unit') }} &rarr;
                            </span>
                            <span class="self-center font-mono text-[0.6875rem] text-muted [writing-mode:vertical-rl] rotate-180">
                                &larr; <span x-text="length">{{ $demoRoom['length'] }}</span> {{ __('diwan.room.unit') }} &rarr;
                            </span>
                            <div class="bg-paper-2 p-3">
                                <svg :view-box.camel="viewBox" viewBox="0 0 {{ $demoRoom['width'] }} {{ $demoRoom['length'] }}"
                                     preserveAspectRatio="xMidYMid meet" role="img"
                                     aria-label="{{ __('diwan.home.fit_piece', ['name' => $fitDemo->name]) }}"
                                     class="block w-full h-60 sm:h-72 overflow-visible" x-cloak>
                                    <rect x="0" y="0" :width="width" :height="length"
                                          class="plan-shape fill-paper stroke-ink" stroke-width="2" vector-effect="non-scaling-stroke" />
                                    <rect :x="walkX" :y="walkY" :width="walkW" :height="walkH"
                                          class="plan-shape fill-none stroke-rule-2" stroke-width="1" stroke-dasharray="4 4" vector-effect="non-scaling-stroke" />
                                    <rect :x="pieceX" :y="pieceY" :width="pieceW" :height="pieceH"
                                          class="plan-shape" :class="pieceClass" stroke-width="1.5" vector-effect="non-scaling-stroke" />
                                </svg>
                            </div>
                        </div>
                        <p class="m-0 mt-3 text-xs leading-body text-muted">
                            {{ __('diwan.home.fit_piece', ['name' => $fitDemo->name]) }}
                            {{ __('diwan.home.fit_walkway') }}
                        </p>

                        <fieldset class="m-0 mt-8 p-0 border-0 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                            <legend class="sr-only">{{ __('diwan.home.fit_try') }}</legend>
                            <label class="block">
                                <span class="flex items-baseline justify-between text-xs uppercase tracking-label text-muted">
                                    {{ __('diwan.room.width') }}
                                    <span class="font-mono text-sm normal-case text-ink" dir="ltr"><span x-text="width">{{ $demoRoom['width'] }}</span> {{ __('diwan.room.unit') }}</span>
                                </span>
                                <input type="range" name="width" min="200" max="1000" step="10"
                                       value="{{ $demoRoom['width'] }}" x-model.number="width" class="{{ $range }}">
                            </label>
                            <label class="block">
                                <span class="flex items-baseline justify-between text-xs uppercase tracking-label text-muted">
                                    {{ __('diwan.room.length') }}
                                    <span class="font-mono text-sm normal-case text-ink" dir="ltr"><span x-text="length">{{ $demoRoom['length'] }}</span> {{ __('diwan.room.unit') }}</span>
                                </span>
                                <input type="range" name="length" min="200" max="1000" step="10"
                                       value="{{ $demoRoom['length'] }}" x-model.number="length" class="{{ $range }}">
                            </label>
                        </fieldset>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 px-6 sm:px-8 py-5 bg-paper-2">
                        <span class="text-sm text-muted">
                            {{ __('diwan.home.fit_floor') }}
                            <span class="font-mono text-ink" dir="ltr"><span x-text="floorText">{{ number_format($demoRoom['width'] * $demoRoom['length'] / 10000, 1) }}</span> m&sup2;</span>
                        </span>
                        <x-ui.button type="submit">
                            <x-icon name="rulers" class="size-4" />
                            {{ __('diwan.home.fit_save') }}
                        </x-ui.button>
                    </div>
                </form>
            @endisset
        </div>
    </section>

    {{-- ══ The workshop — the frame opens as it arrives ═══════════════════ --}}
    <section class="{{ $section }}">
        <figure class="{{ $page }} m-0">
            <div class="relative aspect-[4/3] md:aspect-[16/7] overflow-hidden bg-paper-2 unveil">
                <x-picture :src="asset('assets/images/workshop.jpg')"
                           :alt="__('diwan.home.craft_alt')"
                           sizes="(max-width: 88rem) 100vw, 88rem" :width="2400" :height="1350"
                           loading="lazy" decoding="async"
                           class="absolute inset-0 size-full object-cover unveil-zoom" />
            </div>

            <figcaption class="grid gap-6 lg:grid-cols-[5fr_6fr] lg:gap-20 mt-10" data-reveal>
                <h2 class="{{ $title }}">{{ __('diwan.home.craft_title') }}</h2>
                <div>
                    <p class="m-0 max-w-[52ch] leading-body text-neutral">{{ __('diwan.home.craft_body') }}</p>
                    <a href="{{ route('furnish.about') }}" class="{{ $link }} mt-6">
                        {{ __('diwan.home.craft_cta') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>
            </figcaption>
        </figure>
    </section>

    {{-- ══ What buyers said — only when they are real ══════════════════════
         The latest approved reviews, as written. No review means no section,
         not a plausible-sounding fake. Each runs in its own direction, so an
         English review on the Arabic page ends where it should. --}}
    @if($quotes->isNotEmpty())
        <section class="{{ $section }} border-0 border-t border-rule">
            <div class="{{ $page }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="{{ $eyebrow }} text-muted">{{ __('diwan.home.quote_label') }}</p>
                        <h2 class="{{ $title }}">{{ __('diwan.home.reviews_title') }}</h2>
                    </div>
                    <a href="{{ route('furnish.testimonials') }}" class="{{ $link }}">
                        {{ __('diwan.home.reviews_all') }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>

                <ul class="m-0 p-0 list-none grid gap-6 md:grid-cols-3" data-reveal-group>
                    @foreach($quotes as $quote)
                        <li data-reveal>
                            <figure class="m-0 h-full flex flex-col p-6 lg:p-8 bg-paper-2">
                                <x-star-rating :rating="$quote->rating" class="m-0" />
                                <blockquote class="m-0 mt-5 flex-1 font-display text-xl lg:text-2xl leading-snug [overflow-wrap:anywhere]" dir="auto">{{ $quote->comment }}</blockquote>
                                <figcaption class="mt-6 text-sm text-muted">
                                    @if($quote->user?->name)<bdi>{{ $quote->user->name }}</bdi>@endif
                                    @if($quote->product)
                                        <a href="{{ route('furnish.product.show', $quote->product->slug) }}" class="block mt-1 text-ink-2 underline-offset-4 hover:underline">{{ $quote->product->name }}</a>
                                    @endif
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

@endsection
