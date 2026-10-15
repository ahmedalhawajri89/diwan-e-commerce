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

    {{-- ══ The hero: a room, measured ══════════════════════════════════════
         The photograph, the promise, and the fit system's first question
         asked right here: how big is your room? Answered, the page counts
         how many pieces fit it, with the same assessment every card makes.

         The rules drawn over the photograph carry no figures. It is a room,
         not one of our measured pieces, and a number on it would be an
         invented dimension. --}}
    @php
        $field = 'block w-full min-w-0 h-12 px-3 bg-paper border border-rule-2 rounded-control text-ink font-mono text-base '
               . 'placeholder:text-muted focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
    @endphp
    <section data-hero
             class="relative isolate flex items-end -mt-(--header-h) min-h-svh lg:min-h-[44rem] lg:max-h-[60rem] lg:h-svh overflow-hidden bg-deep">
        <div class="absolute inset-0 -z-10 parallax-drift">
            <x-picture :src="asset('assets/images/hero-living-room.jpg')"
                       :alt="__('diwan.home.hero_alt')"
                       sizes="100vw" :width="2560" :height="1440"
                       fetchpriority="high" decoding="async"
                       class="block size-full object-cover motion-safe:animate-settle" />
        </div>

        <div class="absolute inset-0 -z-10 bg-linear-to-t from-deep/90 via-photo to-veil" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-56 -z-10 bg-linear-to-b from-scrim to-transparent" aria-hidden="true"></div>

        {{-- The rules: a tape along the foot of the photograph and one up its
             edge, drawn in as the page opens. Ticks, no figures. --}}
        <div class="absolute inset-x-(--page-gutter) bottom-8 -z-10 hidden md:block" data-draw aria-hidden="true">
            <svg viewBox="0 0 600 14" preserveAspectRatio="none" class="block w-full h-3.5 overflow-visible">
                <line x1="0" y1="0" x2="600" y2="0" class="stroke-on-deep-muted" stroke-width="1" vector-effect="non-scaling-stroke" />
                @for($t = 0; $t <= 600; $t += 10)
                    <line x1="{{ $t }}" y1="0" x2="{{ $t }}" y2="{{ $t % 100 === 0 ? 14 : ($t % 50 === 0 ? 9 : 5) }}"
                          class="stroke-on-deep-muted" stroke-width="1" vector-effect="non-scaling-stroke" />
                @endfor
            </svg>
        </div>

        <div class="{{ $page }} grid gap-10 lg:grid-cols-[1fr_24rem] lg:items-end pt-(--header-h) pb-16 lg:pb-24 text-on-deep">
            <div class="scroll-fade">
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

                {{-- The rooms, one tap each: the second way in. --}}
                @if($categoryTiles->isNotEmpty())
                    <nav class="mt-10 motion-safe:animate-fade-up [animation-delay:950ms]" aria-label="{{ __('diwan.home.rooms_label') }}">
                        <p class="m-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-on-deep-muted">{{ __('diwan.home.rooms_label') }}</p>
                        <ul class="m-0 p-0 list-none flex flex-wrap gap-2">
                            @foreach($categoryTiles as $category)
                                <li>
                                    <a href="{{ route('furnish.category', $category->slug) }}"
                                       class="inline-flex items-center min-h-11 px-4 rounded-pill border border-on-deep-muted text-sm text-on-deep no-underline transition-colors hover:bg-on-deep hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </div>

            {{-- The room card: paper on the photograph, the one place on the
                 hero a visitor can act. --}}
            <div class="p-6 sm:p-7 bg-paper text-ink motion-safe:animate-fade-up [animation-delay:1100ms]">
                <p class="flex items-center gap-2 m-0 font-label text-xs font-medium uppercase tracking-label text-muted">
                    <x-icon name="rulers" class="size-4 text-accent-ink" />{{ __('diwan.home.room_card_title') }}
                </p>

                @if($room)
                    {{-- Answered: the count, read from the catalogue. --}}
                    <p class="m-0 mt-4 font-display text-2xl leading-snug">
                        {{ trans_choice('diwan.home.fits_count', $fitCount, ['size' => $room['width'].'×'.$room['length'].' '.__('diwan.room.unit')]) }}
                    </p>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-6">
                        @if($fitCount > 0)
                            <x-ui.button :href="route('furnish.products', ['fit_only' => 1])">{{ __('diwan.home.see_fits') }}</x-ui.button>
                        @endif
                        <x-ui.link data-open-dialog="roomModal" tone="ink" :arrow="false">{{ __('diwan.room.change') }}</x-ui.link>
                    </div>
                @else
                    <p class="m-0 mt-3 text-sm leading-body text-muted">{{ __('diwan.home.room_card_body') }}</p>
                    <form method="POST" action="{{ route('room.store') }}" class="grid grid-cols-2 gap-3 mt-5" x-data="submitOnce" @submit="lock">
                        @csrf
                        <input type="hidden" name="then" value="catalogue">
                        <label class="block">
                            <span class="block mb-1.5 text-xs text-muted">{{ __('diwan.room.width') }} ({{ __('diwan.room.unit') }})</span>
                            <input type="text" name="width" inputmode="numeric" data-digits required placeholder="350" dir="ltr" class="{{ $field }}">
                        </label>
                        <label class="block">
                            <span class="block mb-1.5 text-xs text-muted">{{ __('diwan.room.length') }} ({{ __('diwan.room.unit') }})</span>
                            <input type="text" name="length" inputmode="numeric" data-digits required placeholder="480" dir="ltr" class="{{ $field }}">
                        </label>
                        <x-ui.button type="submit" :block="true" class="col-span-2 mt-1" ::disabled="busy">
                            {{ __('diwan.home.room_card_cta') }}
                            <x-icon name="arrow-right" class="size-4" />
                        </x-ui.button>
                        <p class="col-span-2 m-0 text-xs text-muted">{{ __('diwan.room.hint') }}</p>
                    </form>
                @endif

                {{-- «Ask Diwan», in the card rather than floating over the
                     photograph: the hero keeps the floating door tucked away. --}}
                @inject('assistant', 'App\Support\Ai\ShoppingAssistant')
                @if($assistant->available())
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 m-0 mt-5 pt-4 border-0 border-t border-rule text-sm text-muted">
                        {{ __('diwan.assistant.hero_prompt') }}
                        <button type="button" data-open-assistant aria-controls="assistantPanel"
                                class="group inline-flex items-center gap-1.5 min-h-9 p-0 border-0 bg-transparent font-medium text-ink cursor-pointer underline decoration-rule-2 underline-offset-4 transition-colors hover:decoration-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            <x-icon name="stars" class="size-3.5 text-brass transition-transform duration-500 ease-out group-hover:rotate-12" />{{ __('diwan.assistant.hero_cta') }}
                        </button>
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- ══ What is true of every order ════════════════════════════════════
         Four facts, each something the shop actually does. No guarantee,
         delivery time or rating the shop has not set. --}}
    <section class="border-0 border-b border-rule" aria-label="{{ __('diwan.home.trust_label') }}">
        <ul class="{{ $page }} m-0 list-none grid grid-cols-2 lg:grid-cols-4 gap-px bg-rule" data-reveal-group>
            @foreach([
                ['cash-coin', 'trust_pay'],
                ['rulers', 'trust_measure'],
                ['check-lg', 'trust_fit'],
                ['person', 'trust_account'],
            ] as [$glyph, $key])
                <li class="flex items-start gap-3 bg-paper py-6 lg:px-6 lg:first:ps-0" data-reveal>
                    <x-icon name="{{ $glyph }}" class="size-5 mt-0.5 shrink-0 text-accent-ink" />
                    <span class="text-sm leading-snug">
                        <strong class="block font-medium">{{ __('diwan.home.'.$key.'.0') }}</strong>
                        <span class="text-muted">{{ __('diwan.home.'.$key.'.1') }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
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

    {{-- ══ Where the visitor left off ═════════════════════════════════════
         Only for a returning visitor, from their own session. --}}
    @if($recent->isNotEmpty())
        <section class="{{ $section }} pb-0">
            <div class="{{ $page }}">
                <h2 class="{{ $title }} mb-10" data-reveal>{{ __('diwan.home.recent_title') }}</h2>
                <x-ui.rail :label="__('diwan.home.recent_title')">
                    @foreach($recent as $index => $product)
                        <li class="shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4.5rem)/4)] snap-start" data-reveal>
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        </li>
                    @endforeach
                </x-ui.rail>
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

                <x-ui.rail :label="__('diwan.home.new_title')">
                    @foreach($newest as $index => $product)
                        <li class="shrink-0 w-[44%] sm:w-[30%] lg:w-[calc((100%-4.5rem)/4)] snap-start" data-reveal>
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        </li>
                    @endforeach
                </x-ui.rail>
            </div>
        </section>
    @endif

    {{-- ══ From one room ═════════════════════════════════════════════════
         The fullest room: its newest piece large, three more of its own
         beside it. Every piece shown is real and in that room. --}}
    @if($look && $lookPieces->count() >= 2)
        @php $lead = $lookPieces->first(); @endphp
        <section class="{{ $section }} pt-0">
            <div class="{{ $page }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="{{ $eyebrow }} text-muted">{{ __('diwan.home.look_note') }}</p>
                        <h2 class="{{ $title }}">{{ $look->name }}</h2>
                    </div>
                    <a href="{{ route('furnish.category', $look->slug) }}" class="{{ $link }}">
                        {{ __('diwan.nav.see_room', ['room' => $look->name]) }}
                        <x-icon name="arrow-right" class="{{ $arrow }}" />
                    </a>
                </div>

                <div class="grid gap-6 lg:grid-cols-[7fr_5fr] lg:gap-10">
                    <a href="{{ route('furnish.product.show', $lead->slug) }}"
                       class="group relative isolate flex items-end aspect-[4/5] lg:aspect-auto lg:min-h-[36rem] overflow-hidden bg-paper-2 text-on-deep no-underline unveil"
                       data-reveal>
                        <x-picture :src="$lead->imageUrl()" :alt="$lead->name" sizes="(max-width: 64rem) 100vw, 50vw"
                                   loading="lazy" class="absolute inset-0 -z-10 size-full object-cover transition-transform duration-(--dur-slow) ease-out group-hover:scale-[1.03]" />
                        <span class="absolute inset-x-0 bottom-0 h-1/2 -z-10 bg-linear-to-t from-deep/90 to-transparent" aria-hidden="true"></span>
                        <span class="flex w-full items-end justify-between gap-4 p-6 lg:p-8">
                            <span>
                                <span class="block font-display text-3xl leading-snug">{{ $lead->name }}</span>
                                @if($lead->primaryVariant())
                                    <span class="block mt-1 font-mono text-sm">@money($lead->primaryVariant()->price)</span>
                                @endif
                            </span>
                            <span class="inline-flex items-center justify-center size-11 rounded-pill bg-paper text-ink transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1">
                                <x-icon name="arrow-right" class="size-4" />
                            </span>
                        </span>
                    </a>

                    <ul class="m-0 p-0 list-none grid gap-6 content-start" data-reveal-group>
                        @foreach($lookPieces->skip(1) as $piece)
                            @php $pv = $piece->primaryVariant(); @endphp
                            <li data-reveal>
                                <a href="{{ route('furnish.product.show', $piece->slug) }}"
                                   class="group grid grid-cols-[7rem_1fr] gap-5 items-center text-ink no-underline">
                                    <span class="relative block aspect-[4/5] overflow-hidden bg-paper-2">
                                        <x-picture :src="$piece->imageUrl()" alt="" sizes="7rem" loading="lazy"
                                                   class="absolute inset-0 size-full object-cover transition-transform duration-(--dur-slow) ease-out group-hover:scale-[1.04]" />
                                    </span>
                                    <span class="grid gap-1.5">
                                        <span class="font-display text-xl leading-snug group-hover:underline underline-offset-4">{{ $piece->name }}</span>
                                        @if($pv)
                                            <span class="font-mono text-sm text-accent-ink">@money($pv->price)</span>
                                        @endif
                                        <x-scale-rule :variant="$pv" />
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    {{-- ══ By price ══════════════════════════════════════════════════════
         Three ways in by budget, each with how many pieces it really holds. --}}
    <section class="{{ $section }} pt-0">
        <div class="{{ $page }}">
            <h2 class="{{ $title }} mb-10" data-reveal>{{ __('diwan.home.price_title') }}</h2>
            <ul class="m-0 p-0 list-none grid gap-4 md:grid-cols-3" data-reveal-group>
                @foreach([
                    'under_500' => __('diwan.catalogue.under', ['amount' => App\Support\Money::format(500)]),
                    '500_1500' => __('diwan.catalogue.between', ['from' => App\Support\Money::format(500), 'to' => App\Support\Money::format(1500)]),
                    'above_1500' => __('diwan.catalogue.above', ['amount' => App\Support\Money::format(1500)]),
                ] as $band => $label)
                    <li data-reveal>
                        <a href="{{ route('furnish.products', ['prices' => [$band]]) }}"
                           class="group flex items-end justify-between gap-4 h-full min-h-36 p-6 bg-paper-2 text-ink no-underline transition-colors hover:bg-paper-3 focus-visible:outline-2 focus-visible:outline-focus">
                            <span>
                                <span class="block font-display text-3xl leading-snug">{{ $label }}</span>
                                <span class="block mt-2 text-sm text-muted">{{ trans_choice('diwan.piece_count', $bands[$band]) }}</span>
                            </span>
                            <span class="inline-flex items-center justify-center size-11 shrink-0 rounded-pill border border-rule-2 transition-[translate,border-color] group-hover:border-ink group-hover:translate-x-1 rtl:group-hover:-translate-x-1">
                                <x-icon name="arrow-right" class="size-4" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

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

    {{-- ══ How a piece reaches you — a story in chapters ══════════════════
         Measured, drawn, tried in your room, delivered. Each chapter is one
         picture and what the shop actually does at that step; the second is
         not a photograph but the real drawing of the largest measured piece,
         from the fit system itself.

         Moved by the shopper (chapters, arrows, swipe, arrow keys), never by
         a timer. Pictures cross-fade and settle from slightly close, for
         visitors who accept motion; for the rest they simply change. --}}
    @php
        // Each chapter: its picture (or the drawing) and its words, in order.
        $story = __('diwan.home.story');
        $chapters = collect([
            ['image' => asset('assets/images/workshop.jpg'), 'alt' => __('diwan.home.craft_alt'), 'words' => $story[0]],
            $demoVariant?->hasDimensions() ? ['drawing' => true, 'words' => $story[1]] : null,
            ['image' => asset('assets/images/hero-living-room.jpg'), 'alt' => __('diwan.home.hero_alt'), 'words' => $story[2]],
            ['image' => asset('assets/images/auth-panel.jpg'), 'alt' => __('diwan.home.story_delivered_alt'), 'words' => $story[3]],
        ])->filter()->values();
        $round = 'inline-flex items-center justify-center size-11 rounded-pill border border-rule-2 bg-paper text-ink cursor-pointer transition-colors '
               . 'hover:border-ink disabled:opacity-35 disabled:cursor-default disabled:hover:border-rule-2 '
               . 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    @endphp
    <section class="{{ $section }}" x-data="story" @keydown="key" aria-labelledby="storyTitle"
             aria-roledescription="{{ __('diwan.home.story_role') }}">
        <div class="{{ $page }}">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
                <div>
                    <p class="{{ $eyebrow }} text-muted">{{ __('diwan.home.story_note') }}</p>
                    <h2 id="storyTitle" class="{{ $title }}">{{ __('diwan.home.craft_title') }}</h2>
                </div>
                <a href="{{ route('furnish.about') }}" class="{{ $link }}">
                    {{ __('diwan.home.craft_cta') }}
                    <x-icon name="arrow-right" class="{{ $arrow }}" />
                </a>
            </div>

            <div class="grid gap-8 lg:grid-cols-[7fr_5fr] lg:gap-14 lg:items-stretch">

                {{-- The stage: one chapter's picture at a time. --}}
                <div data-stage class="relative aspect-[4/5] sm:aspect-[4/3] lg:aspect-auto lg:min-h-[34rem] overflow-hidden bg-paper-2 touch-pan-y select-none" data-reveal>
                    @foreach($chapters as $i => $chapter)
                        <figure data-slide="{{ $i }}" @if($i === 0) data-active @else inert aria-hidden="true" @endif
                                class="group/slide absolute inset-0 m-0 opacity-0 data-active:opacity-100 motion-safe:transition-opacity motion-safe:duration-700 motion-safe:ease-out">
                            @isset($chapter['drawing'])
                                {{-- Not a photograph: the largest measured piece, drawn
                                     to scale beside a 175 cm person, as on its own page. --}}
                                <div class="absolute inset-0 grid content-center gap-4 p-5 sm:p-8 lg:p-12">
                                    <x-scale-figure :variant="$demoVariant" :name="$fitDemo->name" />
                                    <figcaption class="flex flex-wrap items-baseline justify-between gap-3 text-sm">
                                        <a href="{{ route('furnish.product.show', $fitDemo->slug) }}" class="font-display text-xl text-ink no-underline hover:underline underline-offset-4">{{ $fitDemo->name }}</a>
                                        <bdi dir="ltr" class="font-mono text-muted">{{ $demoVariant->width_cm }} &times; {{ $demoVariant->depth_cm }} &times; {{ $demoVariant->height_cm }}</bdi>
                                    </figcaption>
                                </div>
                            @else
                                <x-picture :src="$chapter['image']" :alt="$chapter['alt']" sizes="(max-width: 64rem) 100vw, 56vw" loading="lazy"
                                           class="absolute inset-0 size-full object-cover scale-[1.06] group-data-active/slide:scale-100 motion-safe:transition-transform motion-safe:duration-[1800ms] motion-safe:ease-out" />
                            @endisset
                        </figure>
                    @endforeach

                    {{-- Where in the story, over the picture. --}}
                    <span class="absolute bottom-4 start-4 z-10 px-3 py-1.5 rounded-pill bg-paper/90 font-mono text-xs text-ink" dir="ltr" x-text="position" aria-hidden="true">01 / {{ str_pad($chapters->count(), 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                {{-- The chapters and their words. --}}
                <div class="flex flex-col">
                    <div role="tablist" aria-label="{{ __('diwan.home.story_note') }}" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-1 gap-x-4">
                        @foreach($chapters as $i => $chapter)
                            @php [$name] = $chapter['words']; @endphp
                            <button type="button" role="tab" data-go="{{ $i }}" @click="choose" id="storyTab{{ $i }}" aria-controls="storyText{{ $i }}"
                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}" tabindex="{{ $i === 0 ? 0 : -1 }}" @if($i === 0) data-active @endif
                                    class="group/tab relative flex items-baseline gap-3 min-h-14 py-4 border-0 bg-transparent text-start cursor-pointer text-muted data-active:text-ink hover:text-ink
                                           focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus">
                                {{-- A rule along each chapter; the current one is drawn in ink. --}}
                                <span class="absolute inset-x-0 top-0 h-px bg-rule" aria-hidden="true"></span>
                                <span class="absolute inset-x-0 -top-px h-[3px] bg-ink origin-left rtl:origin-right scale-x-0 group-data-active/tab:scale-x-100 motion-safe:transition-transform motion-safe:duration-500 motion-safe:ease-out" aria-hidden="true"></span>
                                <span class="font-mono text-xs" dir="ltr">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="font-display text-xl lg:text-2xl leading-snug">{{ $name }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="relative grow min-h-28 lg:min-h-36 mt-6 lg:mt-8">
                        @foreach($chapters as $i => $chapter)
                            @php [, $body] = $chapter['words']; @endphp
                            <p id="storyText{{ $i }}" role="tabpanel" aria-labelledby="storyTab{{ $i }}" data-text="{{ $i }}"
                               @if($i === 0) data-active @else inert aria-hidden="true" @endif
                               class="absolute inset-0 m-0 max-w-[44ch] leading-body text-neutral opacity-0 translate-y-2 data-active:opacity-100 data-active:translate-y-0
                                      motion-safe:transition-[opacity,translate] motion-safe:duration-500 motion-safe:ease-out">
                                {{ $body }}
                            </p>
                        @endforeach
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="button" class="{{ $round }}" @click="prev" :disabled="atStart" aria-label="{{ __('diwan.home.story_prev') }}">
                            <x-icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" class="{{ $round }}" @click="next" :disabled="atEnd" aria-label="{{ __('diwan.home.story_next') }}">
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

    {{-- ══ Questions people ask before they buy ═════════════════════════
         Answers say only what the shop does; the policy pages have the rest. --}}
    <section class="{{ $section }} border-0 border-t border-rule">
        <div class="{{ $page }} grid gap-10 lg:grid-cols-[5fr_7fr] lg:gap-16">
            <div data-reveal>
                <h2 class="{{ $title }}">{{ __('diwan.home.faq_title') }}</h2>
                <p class="m-0 mt-4 max-w-[40ch] text-sm leading-body text-muted">{{ __('diwan.home.faq_body') }}</p>
                <a href="{{ route('furnish.contact') }}" class="{{ $link }} mt-6">
                    {{ __('diwan.nav.contact') }}
                    <x-icon name="arrow-right" class="{{ $arrow }}" />
                </a>
            </div>
            <div class="border-0 border-t border-rule" data-reveal>
                {{-- One answer open at a time (a shared name). Opening one: the
                     plus folds into a minus inside a disc filled with ink, a rule
                     draws along the foot of the question, and the answer is
                     uncovered from the top. --}}
                @foreach(__('diwan.home.faq') as [$q, $a])
                    <details name="faq" class="group relative border-0 border-b border-rule">
                        <summary class="group/q flex items-center justify-between gap-4 min-h-16 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            <span class="font-display text-xl leading-snug motion-safe:transition-transform motion-safe:duration-500 motion-safe:ease-out group-hover/q:translate-x-1 rtl:group-hover/q:-translate-x-1">{{ $q }}</span>
                            <span class="relative inline-flex items-center justify-center size-9 shrink-0 rounded-pill border border-rule-2 text-ink
                                         motion-safe:transition-[rotate,scale,background-color,border-color,color] motion-safe:duration-500 motion-safe:ease-out
                                         group-hover/q:border-ink group-open:bg-ink group-open:border-ink group-open:text-paper group-open:rotate-180" aria-hidden="true">
                                <span class="absolute w-3 h-px bg-current"></span>
                                <span class="absolute w-3 h-px bg-current rotate-90 group-open:rotate-0 motion-safe:transition-[rotate] motion-safe:duration-500 motion-safe:ease-out"></span>
                            </span>
                            {{-- The rule along the foot of the open question. --}}
                            <span class="absolute inset-x-0 -bottom-px h-0.5 bg-ink origin-left rtl:origin-right scale-x-0 group-open:scale-x-100 motion-safe:transition-transform motion-safe:duration-700 motion-safe:ease-out" aria-hidden="true"></span>
                        </summary>
                        <p class="m-0 pb-6 max-w-measure text-sm leading-body text-neutral motion-safe:group-open:animate-unfold">{{ $a }}</p>
                    </details>
                @endforeach
                <p class="m-0 pt-5 text-sm">
                    <a href="{{ route('furnish.page', 'shipping') }}" class="text-accent-ink underline underline-offset-4">{{ __('diwan.pages.shipping.title') }}</a>
                </p>
            </div>
        </div>
    </section>

@endsection
