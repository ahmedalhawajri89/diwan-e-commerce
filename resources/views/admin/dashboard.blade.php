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

    @php
        // The fit system only works on measured pieces, so the one number an
        // operator can act on is how many are still unmeasured.
        $unmeasured = \App\Models\ProductVariant::where(function ($q) {
            $q->whereNull('width_cm')->orWhereNull('depth_cm');
        })->count();
    @endphp

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
        <div class="hm-stat {{ $unmeasured > 0 ? 'hm-stat--flag' : '' }}">
            <dt>{{ __('admin.dashboard.unmeasured') }}</dt>
            <dd>{{ $unmeasured }}</dd>
        </div>
    </dl>

    @if($unmeasured > 0)
        <p class="hm-note hm-note--info" style="margin-bottom: var(--space-xl);">
            <i class="bi bi-rulers" aria-hidden="true"></i>
            <span>
                {{ __('admin.dashboard.unmeasured_note') }}
                <a href="{{ route('admin.products.index') }}" class="hm-link hm-link--sm">
                    {{ __('admin.dashboard.fix_them') }}
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </span>
        </p>
    @endif

    <div class="hm-head">
        <h2 class="hm-head__title" style="font-size: var(--text-lg);">{{ __('admin.dashboard.recent') }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="hm-head__aside hm-link hm-link--sm">
            {{ __('admin.orders.title') }}
            <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    @forelse($stats['recent_orders'] as $order)
        <div class="d-flex flex-wrap gap-3 align-items-center"
             style="padding-block: var(--space-sm); border-bottom: var(--rule-hair) solid var(--color-rule);">
            {{-- The link sizes to its own text; the column width lives on the
                 wrapper, so the drawn underline stops where the number does. --}}
            <span style="flex: 0 0 6rem;">
                <a href="{{ route('admin.orders.show', $order->id) }}"
                   class="hm-num hm-link hm-link--sm">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</a>
            </span>

            <span style="flex: 1 1 10rem; min-width: 0; font-size: var(--text-sm);">
                {{ $order->user?->name ?? __('admin.orders.guest') }}
            </span>

            <span class="hm-chip {{ $order->status === 'pending' ? 'hm-chip--warn' : ($order->status === 'cancelled' ? 'hm-chip--danger' : 'hm-chip--ok') }}">
                {{ __('admin.orders.status_values.' . $order->status) }}
            </span>

            <span class="hm-num" style="flex: 0 0 6rem; text-align: end;">
                {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
            </span>

            <span class="hm-muted hm-num" style="font-size: var(--text-xs); flex: 0 0 6rem; text-align: end;">
                {{ $order->created_at->translatedFormat('j M Y') }}
            </span>
        </div>
    @empty
        <p class="hm-muted">{{ __('admin.dashboard.no_orders') }}</p>
    @endforelse

</x-app-layout>
