{{--
  The overview, in the order an operator's day runs: today at a glance, how
  the chosen period sold against the one before, what is waiting for a
  person, the days of the period, where the orders stand, and what moves.

  Cards on a linen ground, square as the house's surfaces are. Every figure
  is computed in App\Support\Overview; where there is nothing to count the
  page says so and never fills the hole with a made-up number.
--}}
@php
    $money = fn ($v) => \App\Support\Money::format($v);
    $tone = fn ($status) => ['pending' => 'warn', 'cancelled' => 'danger'][$status] ?? 'ok';

    $waitIcon = [
        'confirm' => 'telephone', 'low_stock' => 'box-seam', 'unpriced' => 'cash-coin',
        'unmeasured' => 'rulers', 'untranslated' => 'translate', 'reviews' => 'star', 'messages' => 'envelope',
    ];
    $open = collect($waiting)->filter(fn ($w) => $w['count'] > 0);
    // The clear ones are listed too, after, quietly: "these are fine" is
    // worth knowing, and it is true.
    $clear = collect($waiting)->filter(fn ($w) => $w['count'] === 0);
    $urgent = ['confirm', 'low_stock', 'unpriced'];

    $figureIcon = ['revenue' => 'cash-coin', 'orders' => 'receipt', 'average' => 'bag', 'customers' => 'person'];

    // Each stage its own ink; the legend says the word, the colour seconds it.
    $stageStroke = ['pending' => 'stroke-warn', 'processing' => 'stroke-accent-ink', 'shipped' => 'stroke-ink-2', 'delivered' => 'stroke-ok', 'cancelled' => 'stroke-rule-2'];
    $stageDot = ['pending' => 'bg-warn', 'processing' => 'bg-accent-ink', 'shipped' => 'bg-ink-2', 'delivered' => 'bg-ok', 'cancelled' => 'bg-rule-2'];
    $placed = array_sum($pipeline);

    $card = 'min-w-0 flex flex-col bg-paper border border-rule';
    $cardHead = 'flex items-center justify-between gap-4 px-5 pt-4';
    $cardTitle = 'm-0 font-display text-xl';
    $quiet = 'm-0 my-auto px-5 py-6 text-center text-sm leading-body text-muted';
@endphp

<x-app-layout canvas="linen">
    <x-slot name="title">{{ __('admin.dashboard.title') }}</x-slot>

    {{-- ══ Greeting and the period ═══════════════════════════════════════ --}}
    <header class="flex flex-wrap items-end justify-between gap-4 mb-5">
        <div class="min-w-0">
            <p class="m-0 font-label text-xs font-medium uppercase tracking-label text-muted">{{ now()->translatedFormat(__('admin.format.day')) }}</p>
            <h1 class="m-0 mt-1 font-display font-medium rtl:font-semibold text-3xl leading-tight">
                {{ __('admin.dashboard.greeting.'.$greeting, ['name' => $firstName]) }}
            </h1>
            <p class="m-0 mt-1 max-w-measure text-sm leading-body text-muted">{{ __('admin.dashboard.intro') }}</p>
        </div>

        {{-- The period: links, so each is a page that can be kept or shared. --}}
        <nav aria-label="{{ __('admin.dashboard.window') }}" class="inline-flex p-1 rounded-pill bg-paper border border-rule">
            @foreach($overview::WINDOWS as $n)
                <a href="{{ route('admin.dashboard', ['days' => $n]) }}"
                   @if($overview->days === $n) aria-current="page" @endif
                   class="inline-flex items-center min-h-9 px-4 rounded-pill text-sm text-muted no-underline motion-safe:transition-colors hover:text-ink aria-[current=page]:bg-ink aria-[current=page]:text-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    {{ __('admin.dashboard.days.'.$n) }}
                </a>
            @endforeach
        </nav>
    </header>

    {{-- ══ Today ═════════════════════════════════════════════════════════
         One dark strip across the page, so the eye starts here: what sold
         today, the day's three counts, and the call to make first. --}}
    <section aria-labelledby="todayTitle" class="relative grid gap-5 mb-4 p-5 sm:px-6 bg-deep text-on-deep overflow-hidden lg:grid-cols-[minmax(0,auto)_minmax(0,1fr)] xl:grid-cols-[minmax(0,auto)_minmax(0,1fr)_minmax(0,22rem)] lg:items-center">
        {{-- A ruler's ticks along the top edge: the shop's own idea. --}}
        <span class="absolute inset-x-0 top-0 h-1.5 bg-[repeating-linear-gradient(to_right,var(--color-brass)_0_1px,transparent_1px_12px)] opacity-40" aria-hidden="true"></span>

        <div class="min-w-0 lg:pe-8 lg:border-0 lg:border-e lg:border-deep-rule">
            <div class="flex items-center gap-3">
                <h2 id="todayTitle" class="m-0 font-label text-xs font-medium uppercase tracking-label text-brass">{{ __('admin.dashboard.today') }}</h2>
                <span class="inline-flex items-center gap-1.5 text-xs text-on-deep-muted">
                    <span class="size-1.5 rounded-pill bg-ok motion-safe:animate-pulse" aria-hidden="true"></span>{{ __('admin.dashboard.live') }}
                </span>
            </div>
            <p class="m-0 mt-2 text-xs text-on-deep-muted">{{ __('admin.dashboard.today_sales') }}</p>
            <p class="m-0 mt-1 font-mono text-4xl leading-none text-on-deep"><x-admin.money :value="$today['revenue']" /></p>
        </div>

        <dl class="grid grid-cols-3 gap-4 m-0">
            @foreach(['orders', 'pending', 'customers'] as $key)
                <div class="min-w-0">
                    <dt class="text-xs leading-snug text-on-deep-muted">{{ __('admin.dashboard.today_'.$key) }}</dt>
                    <dd class="m-0 mt-1 font-mono text-3xl leading-none {{ $key === 'pending' && $today['pending'] > 0 ? 'text-brass' : 'text-on-deep' }}">{{ $today[$key] }}</dd>
                </div>
            @endforeach
        </dl>

        @if($oldestPending)
            <a href="{{ route('admin.orders.show', $oldestPending->id) }}"
               class="group flex items-center gap-3 p-3 bg-deep-2 border border-deep-rule text-on-deep no-underline motion-safe:transition-colors hover:border-brass focus-visible:outline-2 focus-visible:outline-brass lg:col-span-2 xl:col-span-1">
                <span class="inline-flex items-center justify-center size-9 shrink-0 rounded-pill bg-brass text-deep"><x-icon name="telephone" class="size-4" /></span>
                <span class="flex-1 min-w-0 text-sm leading-snug">
                    {{ __('admin.dashboard.oldest', ['when' => $oldestPending->created_at->diffForHumans()]) }}
                    <span class="block font-mono text-xs text-on-deep-muted truncate" dir="ltr">{{ $oldestPending->number() }} · {{ $oldestPending->user?->name ?? __('admin.orders.guest') }}</span>
                </span>
                <x-icon name="chevron-right" class="size-3.5 text-on-deep-muted motion-safe:transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
            </a>
        @endif
    </section>

    {{-- ══ The period against the one before it, in one row ═════════════ --}}
    <section aria-labelledby="figuresTitle" class="mb-4">
        <h2 id="figuresTitle" class="sr-only">{{ __('admin.dashboard.days.'.$overview->days) }}</h2>
        <dl class="grid grid-cols-2 gap-3 sm:gap-4 m-0 xl:grid-cols-4">
            @foreach($figures as $key => $figure)
                @php
                    $change = $figure['change'];
                    $trend = $key === 'average' ? [] : $daily->pluck($key)->all();
                    $pill = match (true) {
                        $change === null || $change === 0 => 'bg-paper-2 text-muted',
                        $change > 0 => 'bg-ok-wash text-ok',
                        default => 'bg-danger-wash text-danger',
                    };
                @endphp
                {{-- Sized by the card's own width (a container), not the window's:
                     an amount keeps its unit on one line, so a fixed size pushed
                     "ر.س" out of a quarter-width card. --}}
                <div class="{{ $card }} @container p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.dashboard.figures.'.$key) }}</dt>
                        <span class="inline-flex items-center justify-center size-8 rounded-pill bg-paper-2 text-neutral" aria-hidden="true"><x-icon :name="$figureIcon[$key]" class="size-3.5" /></span>
                    </div>
                    <dd class="m-0 mt-2 font-mono text-[clamp(1.25rem,11cqi,1.875rem)] leading-none text-ink">
                        @if($figure['now'] === null)
                            <span class="text-muted">—</span>
                        @elseif(in_array($key, ['revenue', 'average'], true))
                            <x-admin.money :value="$figure['now']" />
                        @else
                            {{ $figure['now'] }}
                        @endif
                    </dd>
                    <dd class="m-0 mt-2 flex flex-wrap items-center gap-2 text-xs text-muted">
                        @if($change === null)
                            {{ __('admin.dashboard.no_change') }}
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-pill font-medium {{ $pill }}">
                                @if($change > 0)<x-icon name="arrow-up-right" class="size-3" />@elseif($change < 0)<x-icon name="arrow-down-right" class="size-3" />@endif
                                <bdi dir="ltr">{{ $change > 0 ? '+' : '' }}{{ $change }}%</bdi>
                            </span>
                            <span>{{ __('admin.dashboard.vs_before') }}</span>
                        @endif
                    </dd>
                    <dd class="m-0 mt-auto pt-3"><x-admin.spark :values="$trend" /></dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ══ Waiting for you, and the days ═════════════════════════════════ --}}
    <div class="grid gap-4 mb-4 xl:grid-cols-12">
        <section aria-labelledby="waitingTitle" class="{{ $card }} xl:col-span-4">
            <div class="{{ $cardHead }}">
                <h2 id="waitingTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.waiting_title') }}</h2>
                @if($open->isNotEmpty())
                    <span class="inline-flex items-center justify-center min-w-7 h-7 px-2 rounded-pill bg-warn-wash font-mono text-xs text-warn">{{ $open->count() }}</span>
                @endif
            </div>

            @if($open->isEmpty())
                <p class="{{ $quiet }}">
                    <x-icon name="check-lg" class="block mx-auto mb-3 size-6 text-ok" />{{ __('admin.dashboard.all_clear') }}
                </p>
            @else
                <ul class="m-0 mt-3 p-0 list-none">
                    @foreach($open as $key => $item)
                        @php $isUrgent = in_array($key, $urgent, true); @endphp
                        <li>
                            <a href="{{ route($item['route'], $item['params']) }}"
                               class="group flex items-center gap-3.5 min-h-12 px-5 text-ink no-underline motion-safe:transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus">
                                <span class="inline-flex items-center justify-center size-9 shrink-0 rounded-pill {{ $isUrgent ? 'bg-warn-wash text-warn' : 'bg-paper-2 text-neutral' }}">
                                    <x-icon :name="$waitIcon[$key]" class="size-4" />
                                </span>
                                <span class="flex-1 min-w-0 text-sm leading-snug">{{ __('admin.dashboard.waiting.'.$key) }}</span>
                                <span class="font-mono text-lg {{ $isUrgent ? 'text-warn' : 'text-ink' }}">{{ $item['count'] }}</span>
                                <x-icon name="chevron-right" class="size-3.5 text-muted motion-safe:transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($open->isNotEmpty() && $clear->isNotEmpty())
                <ul class="m-0 mx-5 mt-1 mb-3 p-0 pt-2 list-none border-0 border-t border-dashed border-rule">
                    @foreach($clear as $key => $item)
                        <li class="flex items-center gap-3.5 min-h-9 text-sm text-muted">
                            <span class="inline-flex items-center justify-center size-9 shrink-0"><x-icon name="check-lg" class="size-3.5 text-ok" /></span>
                            <span class="flex-1 min-w-0 truncate">{{ __('admin.dashboard.waiting.'.$key) }}</span>
                            <span class="font-mono">0</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section aria-labelledby="chartTitle" class="{{ $card }} xl:col-span-8">
            <div class="{{ $cardHead }}">
                <h2 id="chartTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.chart_title') }}</h2>
                <span class="text-xs text-muted">{{ __('admin.dashboard.days.'.$overview->days) }}</span>
            </div>
            @if($daily->sum('orders') > 0)
                <x-admin.bars :days="$daily" :caption="__('admin.dashboard.chart_title')" class="px-5 pt-4 pb-5" />
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.chart_empty') }}</p>
            @endif
        </section>
    </div>

    {{-- ══ Where the orders stand, and what moves ════════════════════════ --}}
    <div class="grid gap-4 mb-4 lg:grid-cols-2 xl:grid-cols-3">
        <section aria-labelledby="pipelineTitle" class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h2 id="pipelineTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.pipeline_title') }}</h2>
            </div>
            @if($placed > 0)
                <div class="flex items-center gap-5 px-5 py-4">
                    {{-- A ring, each stage's share its arc; the total in the middle. --}}
                    <div class="relative size-32 shrink-0">
                        <svg viewBox="0 0 36 36" class="size-full -rotate-90" aria-hidden="true">
                            <circle cx="18" cy="18" r="15.915" fill="none" class="stroke-paper-2" stroke-width="3.5" />
                            @php $offset = 0; @endphp
                            @foreach($pipeline as $stage => $n)
                                @if($n > 0)
                                    @php $share = $n / $placed * 100; @endphp
                                    <circle cx="18" cy="18" r="15.915" fill="none" class="{{ $stageStroke[$stage] }}" stroke-width="3.5"
                                            stroke-dasharray="{{ round($share, 3) }} {{ round(100 - $share, 3) }}" stroke-dashoffset="{{ round(-$offset, 3) }}" />
                                    @php $offset += $share; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <span class="absolute inset-0 grid place-content-center text-center">
                            <span class="font-mono text-2xl leading-none">{{ $placed }}</span>
                            <span class="mt-1 text-[0.6875rem] text-muted">{{ __('admin.dashboard.figures.orders') }}</span>
                        </span>
                    </div>

                    <ul class="flex-1 min-w-0 m-0 p-0 list-none grid gap-0.5">
                        @foreach($pipeline as $stage => $n)
                            <li>
                                <a href="{{ route('admin.orders.index', ['status' => $stage]) }}"
                                   class="flex items-center gap-2.5 min-h-9 px-2 -mx-2 text-sm {{ $n > 0 ? 'text-ink' : 'text-muted' }} no-underline motion-safe:transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus">
                                    <span class="size-2.5 shrink-0 rounded-pill {{ $n > 0 ? $stageDot[$stage] : 'border border-rule-2' }}" aria-hidden="true"></span>
                                    <span class="flex-1 truncate">{{ __('admin.orders.status_values.'.$stage) }}</span>
                                    <span class="font-mono">{{ $n }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.pipeline_empty') }}</p>
            @endif

            {{-- Whether the way-in check works, in the customers' answers.
                 Nothing until somebody has answered: no number to invent. --}}
            <div class="px-5 py-3 border-0 border-t border-rule">
                <p class="m-0 mb-1.5 text-xs text-muted">{{ __('admin.dashboard.way_in') }}</p>
                @if(array_sum($deliveries) > 0)
                    <p class="flex flex-wrap gap-x-4 gap-y-1 m-0 text-sm">
                        @foreach(['easy' => 'text-ok', 'hard' => 'text-warn', 'failed' => 'text-danger'] as $answer => $answerTone)
                            <span><span class="font-mono {{ $answerTone }}">{{ $deliveries[$answer] }}</span> {{ __('admin.orders.feedback.'.$answer) }}</span>
                        @endforeach
                        <span><span class="font-mono">{{ $deliveries['cancelled'] }}</span> {{ __('admin.dashboard.way_in_cancelled') }}</span>
                    </p>
                @else
                    <p class="m-0 text-sm text-muted">{{ __('admin.dashboard.way_in_none') }}</p>
                @endif

                {{-- And the returns, by why: size beside the rest. --}}
                <p class="m-0 mt-3 mb-1.5 text-xs text-muted">{{ __('admin.dashboard.returns_by') }}</p>
                @if(array_sum($returnReasons) > 0)
                    <p class="flex flex-wrap gap-x-4 gap-y-1 m-0 text-sm">
                        @foreach($returnReasons as $reason => $n)
                            <span><span class="font-mono {{ $reason === 'size' ? 'text-warn' : '' }}">{{ $n }}</span> {{ __('diwan.returns.reasons.'.$reason) }}</span>
                        @endforeach
                    </p>
                @else
                    <p class="m-0 text-sm text-muted">{{ __('admin.dashboard.returns_none') }}</p>
                @endif
            </div>
        </section>

        <section aria-labelledby="sellersTitle" class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h2 id="sellersTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.sellers_title') }}</h2>
            </div>
            @if($bestSellers->isNotEmpty())
                @php $most = max($bestSellers->max('quantity'), 1); @endphp
                <ol class="m-0 px-5 py-4 list-none grid gap-3.5">
                    @foreach($bestSellers as $row)
                        <li class="grid gap-1.5">
                            <div class="flex items-baseline justify-between gap-4 text-sm">
                                <span class="min-w-0 truncate">
                                    <span class="inline-block w-7 font-mono text-xs text-muted" dir="ltr">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $row->name }}
                                </span>
                                <span class="shrink-0 font-mono text-xs text-muted">{{ __('admin.dashboard.sold', ['n' => $row->quantity]) }} · {{ $money($row->revenue) }}</span>
                            </div>
                            {{-- Its quantity against the best seller's, as a length. --}}
                            <svg viewBox="0 0 100 2" preserveAspectRatio="none" class="block w-full h-1 rtl:-scale-x-100" aria-hidden="true">
                                <rect x="0" y="0" width="100" height="2" class="fill-paper-2" />
                                <rect x="0" y="0" width="{{ $row->quantity / $most * 100 }}" height="2" class="fill-ink origin-left [transform-box:fill-box] motion-safe:animate-grow-x" />
                            </svg>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.sellers_empty') }}</p>
            @endif
        </section>

        <section aria-labelledby="savedTitle" class="{{ $card }} lg:col-span-2 xl:col-span-1">
            <div class="{{ $cardHead }}">
                <h2 id="savedTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.saved_title') }}</h2>
            </div>
            @if($mostSaved->isNotEmpty())
                <ol class="m-0 px-5 py-4 list-none grid gap-2.5">
                    @foreach($mostSaved as $product)
                        <li>
                            <a href="{{ route('admin.products.show', $product) }}" class="group flex items-center gap-3 text-sm text-ink no-underline">
                                <span class="relative block w-10 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                                    <x-picture :src="$product->imageUrl()" alt="" sizes="2.5rem" :width="80" :height="100" loading="lazy" class="absolute inset-0 size-full object-cover" />
                                </span>
                                <span class="flex-1 min-w-0 truncate group-hover:underline underline-offset-4">{{ $product->name }}</span>
                                <span class="shrink-0 font-mono text-xs text-muted">{{ __('admin.dashboard.saves', ['n' => $product->saves]) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.saved_empty') }}</p>
            @endif
        </section>
    </div>

    {{-- ══ Recent orders ════════════════════════════════════════════════ --}}
    <section aria-labelledby="recentTitle" class="{{ $card }}">
        <div class="{{ $cardHead }} pb-4">
            <h2 id="recentTitle" class="{{ $cardTitle }}">{{ __('admin.dashboard.recent') }}</h2>
            <x-ui.link :href="route('admin.orders.index')">{{ __('admin.orders.title') }}</x-ui.link>
        </div>

        @if($recentOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr class="border-0 border-t border-rule motion-safe:transition-colors hover:bg-paper-2">
                                <td class="px-5 py-2.5"><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $order->number() }}</a></td>
                                <td class="px-3 py-2.5">{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                                <td class="px-3 py-2.5"><x-ui.chip :tone="$tone($order->status)">{{ __('admin.orders.status_values.'.$order->status) }}</x-ui.chip></td>
                                <td class="px-3 py-2.5 font-mono text-end">{{ $money($order->total_price) }}</td>
                                <td class="px-5 py-2.5 font-mono text-xs text-muted text-end whitespace-nowrap">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="{{ $quiet }}">{{ __('admin.dashboard.no_orders') }}</p>
        @endif
    </section>
</x-app-layout>
