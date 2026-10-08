@php
    $spent = $orders->sum('total_price');
    $tone = fn ($s) => ['cancelled' => 'danger', 'delivered' => 'ok'][$s] ?? 'warn';
    $row = 'flex items-center justify-between gap-4 py-3 border-0 border-b border-rule text-sm';
@endphp

<x-app-layout>
    <x-slot name="title">{{ $user->name }}</x-slot>

    <x-admin.head :title="$user->name" :intro="$user->email">
        @unless($user->isProtected())
            <x-ui.button variant="ghost" :href="route('admin.users.edit', $user)">
                <x-icon name="pencil" class="size-4" />{{ __('admin.action.edit') }}
            </x-ui.button>
        @endunless
        <x-ui.link :href="route('admin.users.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    @if($user->isProtected())
        <x-ui.note icon="shield-lock" class="mb-8">{{ __('admin.users.protected_edit') }}</x-ui.note>
    @endif

    <x-admin.stats class="lg:grid-cols-3!">
        <x-admin.stat :label="__('admin.users.joined')" :value="$user->created_at->translatedFormat('j M Y')" />
        <x-admin.stat :label="__('admin.users.orders')" :value="$orders->count()" />
        <x-admin.stat :label="__('admin.users.total_spent')" :value="__('diwan.common.currency').number_format($spent, 2)" />
    </x-admin.stats>

    <div class="grid gap-12 lg:grid-cols-[2fr_1fr]">
        <section class="min-w-0">
            <h2 class="m-0 mb-4 font-display text-2xl">{{ __('admin.users.orders') }}</h2>

            @if($orders->isNotEmpty())
                <x-admin.table>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $order->number() }}</a></td>
                                <td class="font-mono text-xs text-muted">{{ $order->created_at->translatedFormat('j M Y') }}</td>
                                <td><x-ui.chip :tone="$tone($order->status)">{{ __('admin.orders.status_values.'.$order->status) }}</x-ui.chip></td>
                                <td class="text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items_count) }}</td>
                                <td class="font-mono text-end">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-admin.table>
            @else
                <p class="m-0 text-sm text-muted">{{ __('admin.users.no_orders') }}</p>
            @endif
        </section>

        <aside>
            <h2 class="m-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.users.account') }}</h2>
            <div class="border-0 border-t border-rule">
                <div class="{{ $row }}">
                    <span class="text-muted">{{ __('admin.users.roles') }}</span>
                    <span class="flex flex-wrap gap-1 justify-end">
                        @forelse($user->roles as $role)
                            <x-ui.chip>{{ $role->name }}</x-ui.chip>
                        @empty
                            <span class="text-muted">{{ __('admin.users.no_role') }}</span>
                        @endforelse
                    </span>
                </div>
                <div class="{{ $row }}">
                    <span class="text-muted">{{ __('diwan.auth.email') }}</span>
                    <x-ui.chip :tone="$user->email_verified_at ? 'ok' : 'warn'">{{ $user->email_verified_at ? __('admin.users.verified') : __('admin.users.unverified') }}</x-ui.chip>
                </div>
            </div>
        </aside>
    </div>
    @if($actions->isNotEmpty())
        <section aria-labelledby="actionsTitle" class="mt-14">
            <div class="flex flex-wrap items-baseline justify-between gap-4 mb-4">
                <h2 id="actionsTitle" class="m-0 font-display text-2xl">{{ __('admin.activity.their_latest') }}</h2>
                <x-ui.link :href="route('admin.activity.index', ['person' => $user->id])">{{ __('admin.activity.title') }}</x-ui.link>
            </div>
            <ol class="m-0 p-0 list-none border border-rule divide-y divide-rule">
                @foreach($actions as $log)
                    <li class="px-4 py-3"><x-admin.activity-line :log="$log" compact /></li>
                @endforeach
            </ol>
        </section>
    @endif
</x-app-layout>
