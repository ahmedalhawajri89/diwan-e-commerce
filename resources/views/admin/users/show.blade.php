{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ $user->name }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ $user->name }}</h1>
            <p class="hm-app__intro hm-num" dir="ltr">{{ $user->email }}</p>
        </div>

        <div class="d-flex gap-3 align-items-center">
            @unless($user->isProtected())
                <a href="{{ route('admin.users.edit', $user) }}" class="hm-btn hm-btn--ghost">
                    <x-icon name="pencil" />
                    {{ __('admin.action.edit') }}
                </a>
            @endunless
            <a href="{{ route('admin.users.index') }}" class="hm-link hm-link--sm">
                <x-icon name="arrow-left" />
                {{ __('admin.action.back') }}
            </a>
        </div>
    </div>

    @if($user->isProtected())
        <p class="hm-note hm-mb-xl">
            <x-icon name="shield-lock" />
            <span>{{ __('admin.users.protected_edit') }}</span>
        </p>
    @endif

    @php $spent = $orders->sum('total_price'); @endphp

    <dl class="hm-stats">
        <div class="hm-stat">
            <dt>{{ __('admin.users.joined') }}</dt>
            <dd class="hm-t-lg">{{ $user->created_at->translatedFormat('j M Y') }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.users.orders') }}</dt>
            <dd>{{ $orders->count() }}</dd>
        </div>
        <div class="hm-stat">
            <dt>{{ __('admin.users.total_spent') }}</dt>
            <dd>{{ __('diwan.common.currency') }}{{ number_format($spent, 2) }}</dd>
        </div>
    </dl>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="hm-head">
                <h2 class="hm-head__title hm-t-lg">{{ __('admin.users.orders') }}</h2>
            </div>

            @forelse($orders as $order)
                <div class="d-flex flex-wrap gap-3 align-items-center hm-listrow hm-listrow--sm">
                    <span class="hm-col-fig">
                        <a href="{{ route('admin.orders.show', $order->id) }}"
                           class="hm-num hm-link hm-link--sm">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</a>
                    </span>
                    <span class="hm-muted hm-num hm-fill-sm hm-t-xs">
                        {{ $order->created_at->translatedFormat('j M Y') }}
                    </span>
                    <span class="hm-chip {{ $order->status === 'cancelled' ? 'hm-chip--danger' : ($order->status === 'delivered' ? 'hm-chip--ok' : 'hm-chip--warn') }}">
                        {{ __('admin.orders.status_values.' . $order->status) }}
                    </span>
                    <span class="hm-muted hm-t-xs">
                        {{ trans_choice('diwan.piece_count', $order->items_count) }}
                    </span>
                    <span class="hm-num hm-mis-auto">
                        {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
                    </span>
                </div>
            @empty
                <p class="hm-muted hm-t-sm">{{ __('admin.users.no_orders') }}</p>
            @endforelse
        </div>

        <div class="col-lg-4">
            <h2 class="hm-label hm-mb-sm">{{ __('admin.users.account') }}</h2>

            <div class="hm-panel">
                <p class="hm-total">
                    <span class="hm-muted">{{ __('admin.users.roles') }}</span>
                    <span>
                        @forelse($user->roles as $role)
                            <span class="hm-chip">{{ $role->name }}</span>
                        @empty
                            <span class="hm-muted">{{ __('admin.users.no_role') }}</span>
                        @endforelse
                    </span>
                </p>

                <p class="hm-total">
                    <span class="hm-muted">{{ __('diwan.auth.email') }}</span>
                    <span class="hm-chip {{ $user->email_verified_at ? 'hm-chip--ok' : 'hm-chip--warn' }}">
                        {{ $user->email_verified_at ? __('admin.users.verified') : __('admin.users.unverified') }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
