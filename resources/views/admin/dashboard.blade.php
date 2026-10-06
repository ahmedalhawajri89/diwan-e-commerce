{{--
  The overview, in the order an operator's day runs: what is waiting for a
  person, how the chosen period sold against the one before, where the
  orders stand, what moves, and the latest orders.

  Every figure is computed in App\Support\Overview. Where there is nothing to
  count the page says so; it never fills a hole with a made-up number.
--}}
@php
    $currency = __('diwan.common.currency');
    $money = fn ($v) => $currency.number_format($v, 2);
    $tone = fn ($status) => ['pending' => 'warn', 'cancelled' => 'danger'][$status] ?? 'ok';

    $waitIcon = [
        'confirm' => 'telephone', 'low_stock' => 'box-seam', 'unpriced' => 'cash-coin',
        'unmeasured' => 'rulers', 'untranslated' => 'translate', 'reviews' => 'star', 'messages' => 'envelope',
    ];
    $open = collect($waiting)->filter(fn ($w) => $w['count'] > 0);

    // Each stage its own ink; the legend says the word, the colour seconds it.
    $stageFill = ['pending' => 'fill-warn', 'processing' => 'fill-accent-ink', 'shipped' => 'fill-ink-2', 'delivered' => 'fill-ok', 'cancelled' => 'fill-rule-2'];
    $stageDot = ['pending' => 'bg-warn', 'processing' => 'bg-accent-ink', 'shipped' => 'bg-ink-2', 'delivered' => 'bg-ok', 'cancelled' => 'bg-rule-2'];
    $placed = array_sum($pipeline);

    $panel = 'min-w-0 p-5 sm:p-6 border border-rule bg-paper';
    $panelTitle = 'm-0 font-display text-xl';
    $quiet = 'm-0 py-8 text-center text-sm leading-body text-muted bg-paper-2/60';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.dashboard.title') }}</x-slot>

    <x-admin.head :title="__('admin.dashboard.title')" :intro="__('admin.dashboard.intro')">
        {{-- The period: links, so each is a page that can be kept or shared. --}}
        <nav aria-label="{{ __('admin.dashboard.window') }}" class="inline-flex p-1 rounded-pill bg-paper-2">
            @foreach($overview::WINDOWS as $n)
                <a href="{{ route('admin.dashboard', ['days' => $n]) }}"
                   @if($overview->days === $n) aria-current="page" @endif
                   class="inline-flex items-center min-h-9 px-4 rounded-pill text-sm text-muted no-underline motion-safe:transition-colors hover:text-ink aria-[current=page]:bg-paper aria-[current=page]:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    {{ __('admin.dashboard.days.'.$n) }}
                </a>
            @endforeach
        </nav>
    </x-admin.head>

    {{-- ══ Waiting for you ═══════════════════════════════════════════════ --}}
    <section aria-labelledby="waitingTitle" class="mb-10">
        <h2 id="waitingTitle" class="m-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.dashboard.waiting_title') }}</h2>

        @if($open->isEmpty())
            <x-ui.note tone="ok">{{ __('admin.dashboard.all_clear') }}</x-ui.note>
        @else
            {{-- Cards that share the row between them however many there are
                 (auto-fit), each a tile: the sign, the count, what it is. --}}
            <ul class="m-0 p-0 list-none grid gap-3 grid-cols-2 sm:grid-cols-[repeat(auto-fit,minmax(13rem,1fr))]">
                @foreach($open as $key => $item)
                    @php $urgent = in_array($key, ['confirm', 'low_stock', 'unpriced'], true); @endphp
                    <li class="min-w-0">
                        <a href="{{ route($item['route'], $item['params']) }}"
                           class="group relative flex flex-col h-full p-4 sm:p-5 border border-rule bg-paper text-ink no-underline overflow-hidden
                                  motion-safe:transition-[border-color,translate] motion-safe:duration-300 hover:border-ink motion-safe:hover:-translate-y-0.5
                                  focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            {{-- The urgent ones carry a warm rule along their head. --}}
                            @if($urgent)<span class="absolute inset-x-0 top-0 h-0.5 bg-warn" aria-hidden="true"></span>@endif
                            <span class="flex items-center justify-between gap-3">
                                <span class="inline-flex items-center justify-center size-9 rounded-pill {{ $urgent ? 'bg-warn-wash text-warn' : 'bg-paper-2 text-neutral' }}">
                                    <x-icon :name="$waitIcon[$key]" class="size-4" />
                                </span>
                                <x-icon name="chevron-right" class="size-3.5 text-muted motion-safe:transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </span>
                            <span class="mt-4 font-mono text-3xl leading-none">{{ $item['count'] }}</span>
                            <span class="mt-2 text-sm leading-snug text-neutral">{{ __('admin.dashboard.waiting.'.$key) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ══ The period's figures ══════════════════════════════════════════ --}}
    <section aria-labelledby="figuresTitle" class="mb-10">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
            <h2 id="figuresTitle" class="m-0 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.dashboard.days.'.$overview->days) }}</h2>
            <p class="m-0 text-xs text-muted">{{ __('admin.dashboard.compared.'.$overview->days) }} {{ __('admin.dashboard.not_cancelled') }}</p>
        </div>

        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-px m-0 bg-rule border border-rule">
            @foreach($figures as $key => $figure)
                @php
                    $change = $figure['change'];
                    // The day-by-day shape under the figure, where a day has one.
                    $trend = $key === 'average' ? [] : $daily->pluck($key)->all();
                @endphp
                <div class="flex flex-col min-w-0 p-5 bg-paper">
                    <dt class="font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.dashboard.figures.'.$key) }}</dt>
                    <dd class="m-0 mt-3 font-mono text-2xl xl:text-[2rem] leading-none text-ink">
                        @if($figure['now'] === null)
                            <span class="text-muted">—</span>
                        @elseif(in_array($key, ['revenue', 'average'], true))
                            <x-admin.money :value="$figure['now']" />
                        @else
                            {{ $figure['now'] }}
                        @endif
                    </dd>
                    <dd class="m-0 mt-auto pt-4">
                        <x-admin.spark :values="$trend" class="mb-3" />
                    </dd>
                    <dd class="m-0 flex items-center gap-1.5 text-xs {{ $change === null ? 'text-muted' : ($change > 0 ? 'text-ok' : ($change < 0 ? 'text-danger' : 'text-muted')) }}">
                        @if($change === null)
                            {{ __('admin.dashboard.no_change') }}
                        @elseif($change > 0)
                            <x-icon name="arrow-up-right" class="size-3.5 shrink-0" />{{ __('admin.dashboard.up', ['n' => $change]) }}
                        @elseif($change < 0)
                            <x-icon name="arrow-down-right" class="size-3.5 shrink-0" />{{ __('admin.dashboard.down', ['n' => abs($change)]) }}
                        @else
                            {{ __('admin.dashboard.level') }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ══ Sales by day, and where the orders stand ═════════════════════ --}}
    <div class="grid gap-6 mb-10 lg:grid-cols-3">
        <section aria-labelledby="chartTitle" class="{{ $panel }} lg:col-span-2">
            <h2 id="chartTitle" class="{{ $panelTitle }} mb-5">{{ __('admin.dashboard.chart_title') }}</h2>
            @if($daily->sum('orders') > 0)
                <x-admin.bars :days="$daily" :caption="__('admin.dashboard.chart_title')" />
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.chart_empty') }}</p>
            @endif
        </section>

        <section aria-labelledby="pipelineTitle" class="{{ $panel }}">
            <div class="flex items-baseline justify-between gap-4 mb-5">
                <h2 id="pipelineTitle" class="{{ $panelTitle }}">{{ __('admin.dashboard.pipeline_title') }}</h2>
                <span class="font-mono text-sm text-muted">{{ $placed }}</span>
            </div>
            @if($placed > 0)
                {{-- One tape, each stage's share its length: the shop's idea of
                     measuring, applied to its own orders. --}}
                <svg viewBox="0 0 100 6" preserveAspectRatio="none" class="block w-full h-3 rounded-pill overflow-hidden" aria-hidden="true">
                    @php $x = 0; @endphp
                    @foreach($pipeline as $stage => $n)
                        @if($n > 0)
                            @php $w = $n / $placed * 100; @endphp
                            <rect x="{{ $x }}" y="0" width="{{ $w }}" height="6" class="{{ $stageFill[$stage] }}" />
                            @php $x += $w; @endphp
                        @endif
                    @endforeach
                </svg>

                <ul class="m-0 mt-5 p-0 list-none grid gap-0.5">
                    @foreach($pipeline as $stage => $n)
                        <li>
                            <a href="{{ route('admin.orders.index', ['status' => $stage]) }}"
                               class="flex items-center gap-3 min-h-11 px-2 -mx-2 text-sm {{ $n > 0 ? 'text-ink' : 'text-muted' }} no-underline motion-safe:transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus">
                                <span class="size-2.5 rounded-pill {{ $n > 0 ? $stageDot[$stage] : 'border border-rule-2' }}" aria-hidden="true"></span>
                                <span class="flex-1">{{ __('admin.orders.status_values.'.$stage) }}</span>
                                <span class="font-mono">{{ $n }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- The one that has waited longest: the first call to make. --}}
                @if($oldestPending)
                    <a href="{{ route('admin.orders.show', $oldestPending->id) }}"
                       class="group flex items-center gap-3 mt-5 p-4 bg-warn-wash text-ink no-underline motion-safe:transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus">
                        <x-icon name="telephone" class="size-4 shrink-0 text-warn" />
                        <span class="flex-1 min-w-0 text-sm leading-snug">
                            {{ __('admin.dashboard.oldest', ['when' => $oldestPending->created_at->diffForHumans()]) }}
                            <span class="block font-mono text-xs text-muted" dir="ltr">{{ $oldestPending->number() }} · {{ $oldestPending->user?->name ?? __('admin.orders.guest') }}</span>
                        </span>
                        <x-icon name="chevron-right" class="size-3.5 text-muted motion-safe:transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                    </a>
                @endif
            @else
                <p class="{{ $quiet }}">{{ __('admin.dashboard.pipeline_empty') }}</p>
            @endif
        </section>
    </div>

    {{-- ══ What moves ═══════════════════════════════════════════════════ --}}
    <div class="grid gap-6 mb-10 lg:grid-cols-2 lg:items-start">
        <section aria-labelledby="sellersTitle" class="{{ $panel }}">
            <h2 id="sellersTitle" class="{{ $panelTitle }} mb-4">{{ __('admin.dashboard.sellers_title') }}</h2>
            @if($bestSellers->isNotEmpty())
                @php $most = max($bestSellers->max('quantity'), 1); @endphp
                <ol class="m-0 p-0 list-none grid gap-4">
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

        <section aria-labelledby="savedTitle" class="{{ $panel }}">
            <h2 id="savedTitle" class="{{ $panelTitle }} mb-4">{{ __('admin.dashboard.saved_title') }}</h2>
            @if($mostSaved->isNotEmpty())
                <ol class="m-0 p-0 list-none grid gap-3">
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
    <div class="flex flex-wrap items-baseline justify-between gap-4 mb-4">
        <h2 class="m-0 font-display text-2xl">{{ __('admin.dashboard.recent') }}</h2>
        <x-ui.link :href="route('admin.orders.index')">{{ __('admin.orders.title') }}</x-ui.link>
    </div>

    @if($recentOrders->isNotEmpty())
        <x-admin.table>
            <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $order->number() }}</a></td>
                        <td>{{ $order->user?->name ?? __('admin.orders.guest') }}</td>
                        <td><x-ui.chip :tone="$tone($order->status)">{{ __('admin.orders.status_values.'.$order->status) }}</x-ui.chip></td>
                        <td class="font-mono text-end">{{ $money($order->total_price) }}</td>
                        <td class="font-mono text-xs text-muted text-end whitespace-nowrap">{{ $order->created_at->translatedFormat('j M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-ui.empty>{{ __('admin.dashboard.no_orders') }}</x-ui.empty>
    @endif
</x-app-layout>
