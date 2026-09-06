{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  App pages get no enrichment and no display type above --text-2xl, so the four
  decorated stat cards with their giant numerals and expanding background
  circles are gone. A stat row states the figures; the page is then a list.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.dashboard.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.dashboard.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.dashboard.intro') }}</p>
        </div>
    </div>

    <dl class="hm-stats">
        <div class="hm-stat">
            <dt>{{ __('admin.dashboard.products') }}</dt>
            <dd>{{ $stats['total_products'] }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.dashboard.orders') }}</dt>
            <dd>{{ $stats['total_orders'] }}</dd>
        </div>
        <div class="hm-stat {{ $stats['pending_orders'] > 0 ? 'hm-stat--flag' : '' }}">
            <dt>{{ __('admin.dashboard.pending') }}</dt>
            <dd>{{ $stats['pending_orders'] }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.dashboard.customers') }}</dt>
            <dd>{{ $stats['total_users'] }}</dd>
        </div>
        <div class="hm-stat {{ $stats['unmeasured'] > 0 ? 'hm-stat--flag' : '' }}">
            <dt>{{ __('admin.dashboard.unmeasured') }}</dt>
            <dd>{{ $stats['unmeasured'] }}</dd>
        </div>
    </dl>

    @if($stats['unmeasured'] > 0)
        <p class="hm-note hm-note--info hm-mb-xl">
            <x-icon name="rulers" />
            <span>
                {{ __('admin.dashboard.unmeasured_note') }}
                <a href="{{ route('admin.products.index') }}" class="hm-link hm-link--sm">
                    {{ __('admin.dashboard.fix_them') }}
                    <x-icon name="arrow-right" />
                </a>
            </span>
        </p>
    @endif

    <div class="hm-head">
        <h2 class="hm-head__title hm-t-lg">{{ __('admin.dashboard.recent') }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="hm-head__aside hm-link hm-link--sm">
            {{ __('admin.orders.title') }}
            <x-icon name="arrow-right" />
        </a>
    </div>

    @forelse($stats['recent_orders'] as $order)
        <div class="d-flex flex-wrap gap-3 align-items-center hm-listrow hm-listrow--sm">
            {{-- The link sizes to its own text; the column width lives on the
                 wrapper, so the drawn underline stops where the number does. --}}
            <span class="hm-col-fig">
                <a href="{{ route('admin.orders.show', $order->id) }}"
                   class="hm-num hm-link hm-link--sm">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</a>
            </span>

            <span class="hm-fill-md hm-minw-0 hm-t-sm">
                {{ $order->user?->name ?? __('admin.orders.guest') }}
            </span>

            <span class="hm-chip {{ $order->status === 'pending' ? 'hm-chip--warn' : ($order->status === 'cancelled' ? 'hm-chip--danger' : 'hm-chip--ok') }}">
                {{ __('admin.orders.status_values.' . $order->status) }}
            </span>

            <span class="hm-num hm-col-fig hm-end">
                {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
            </span>

            <span class="hm-muted hm-num hm-t-xs hm-col-fig hm-end">
                {{ $order->created_at->translatedFormat('j M Y') }}
            </span>
        </div>
    @empty
        <p class="hm-muted">{{ __('admin.dashboard.no_orders') }}</p>
    @endforelse

</x-app-layout>
