{{--
  A day-by-day bar chart, drawn by the server as SVG: no chart library, no
  third-party origin, nothing to load. Each bar is a real day of the window,
  days without a sale included at zero so the spacing is the calendar's. The
  best day is drawn in the accent.

  The picture is for sighted readers; the same figures are in a table for
  everyone else.

  @param  \Illuminate\Support\Collection  $days     [{date, revenue, orders}]
  @param  string                          $caption
--}}
@props(['days', 'caption'])

@php
    $currency = __('diwan.common.currency');
    $money = fn ($v) => $currency.number_format($v, 2);
    $max = max($days->max('revenue'), 0);
    $peak = $max > 0 ? $days->search(fn ($d) => $d['revenue'] === $max) : null;
    $step = 10;
    $width = $days->count() * $step;
    // Narrow bars when there are many days, so they stay bars and not a slab.
    $bar = $days->count() > 45 ? 6 : 7;
@endphp

<figure {{ $attributes->merge(['class' => 'm-0']) }}>
    {{-- The period in three readings, so a quiet month is still a reading
         and not an empty field. --}}
    <dl class="grid grid-cols-3 gap-4 m-0 mb-6 pb-5 border-0 border-b border-rule">
        <div class="min-w-0">
            <dt class="text-xs text-muted">{{ __('admin.dashboard.chart_total') }}</dt>
            <dd class="m-0 mt-1 font-mono text-lg"><x-admin.money :value="$days->sum('revenue')" /></dd>
        </div>
        <div class="min-w-0">
            <dt class="text-xs text-muted">{{ __('admin.dashboard.chart_active') }}</dt>
            <dd class="m-0 mt-1 font-mono text-lg">{{ __('admin.dashboard.chart_active_value', ['n' => $days->filter(fn ($d) => $d['orders'] > 0)->count(), 'd' => $days->count()]) }}</dd>
        </div>
        <div class="min-w-0">
            <dt class="text-xs text-muted">{{ __('admin.dashboard.chart_peak') }}</dt>
            <dd class="m-0 mt-1 font-mono text-lg">
                @if($peak !== null)
                    <x-admin.money :value="$max" />
                    <span class="block text-xs text-muted">{{ $days[$peak]['date']->translatedFormat('j F') }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </dd>
        </div>
    </dl>

    <div class="mb-2 font-mono text-xs text-muted" dir="ltr">{{ $money($max) }}</div>

    <svg viewBox="0 0 {{ $width }} 100" preserveAspectRatio="none" class="block w-full h-44 sm:h-56 overflow-visible" aria-hidden="true" dir="ltr">
        @foreach([25, 50, 75] as $y)
            <line x1="0" x2="{{ $width }}" y1="{{ $y }}" y2="{{ $y }}" class="stroke-rule" stroke-width="1" vector-effect="non-scaling-stroke" stroke-dasharray="2 4" />
        @endforeach
        <line x1="0" x2="{{ $width }}" y1="100" y2="100" class="stroke-rule-2" stroke-width="1" vector-effect="non-scaling-stroke" />

        @foreach($days as $i => $day)
            @php $h = $max > 0 ? max($day['revenue'] / $max * 96, $day['revenue'] > 0 ? 1.5 : 0) : 0; @endphp
            @if($h > 0)
                <rect x="{{ $i * $step + ($step - $bar) / 2 }}" y="{{ 100 - $h }}" width="{{ $bar }}" height="{{ $h }}"
                      class="{{ $i === $peak ? 'fill-accent-ink' : 'fill-ink' }} origin-bottom [transform-box:fill-box] hover:fill-accent-ink motion-safe:transition-colors motion-safe:animate-grow">
                    <title>{{ $day['date']->translatedFormat('j M') }} — {{ $money($day['revenue']) }} · {{ trans_choice('admin.dashboard.orders_count', $day['orders'], ['count' => $day['orders']]) }}</title>
                </rect>
            @endif
        @endforeach
    </svg>

    <div class="flex justify-between gap-4 mt-2 font-mono text-xs text-muted" dir="ltr">
        <span>{{ $days->first()['date']->translatedFormat('j M') }}</span>
        <span>{{ $days->last()['date']->translatedFormat('j M') }}</span>
    </div>

    <table class="sr-only">
        <caption>{{ $caption }}</caption>
        <thead><tr><th scope="col">{{ __('admin.dashboard.chart_day') }}</th><th scope="col">{{ __('admin.dashboard.figures.revenue') }}</th><th scope="col">{{ __('admin.dashboard.figures.orders') }}</th></tr></thead>
        <tbody>
            @foreach($days->filter(fn ($d) => $d['orders'] > 0) as $day)
                <tr><th scope="row">{{ $day['date']->translatedFormat('j F Y') }}</th><td>{{ $money($day['revenue']) }}</td><td>{{ $day['orders'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</figure>
