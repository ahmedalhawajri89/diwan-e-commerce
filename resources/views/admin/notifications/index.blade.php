{{--
  Every notice the signed-in administrator has, newest first, grouped by
  day. Unread ones carry a dot and a tinted ground; opening one marks it read
  and goes to what it is about (a POST, so a prefetch or a crawler never
  reads anything on the operator's behalf).
--}}
@php
    $tint = ['accent' => 'bg-accent-wash text-accent-ink', 'warn' => 'bg-warn-wash text-warn', 'ok' => 'bg-ok-wash text-ok'];
    $tab = 'inline-flex items-center gap-2 min-h-9 px-3.5 rounded-pill border text-sm no-underline transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $dayName = function (string $date) {
        $day = \Illuminate\Support\Carbon::parse($date);

        return match (true) {
            $day->isToday() => __('admin.alerts_page.today'),
            $day->isYesterday() => __('admin.alerts_page.yesterday'),
            default => $day->translatedFormat('l، j F Y'),
        };
    };
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.alerts_page.title') }}</x-slot>

    <x-admin.head :title="__('admin.alerts_page.title')" :intro="__('admin.alerts_page.intro')">
        @if($unread > 0)
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost">
                    <x-icon name="check-lg" class="size-4" />{{ __('admin.alerts_page.mark_all') }}
                </x-ui.button>
            </form>
        @endif
    </x-admin.head>

    <nav aria-label="{{ __('admin.list.filter') }}" class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.notifications.index') }}" @unless($unreadOnly) aria-current="page" @endunless
           class="{{ $tab }} {{ $unreadOnly ? 'border-rule bg-paper text-ink hover:border-ink' : 'border-ink bg-ink text-paper' }}">{{ __('admin.alerts_page.all') }}</a>
        <a href="{{ route('admin.notifications.index', ['show' => 'unread']) }}" @if($unreadOnly) aria-current="page" @endif
           class="{{ $tab }} {{ $unreadOnly ? 'border-ink bg-ink text-paper' : 'border-rule bg-paper text-ink hover:border-ink' }}">
            {{ __('admin.alerts_page.unread') }}
            <span class="font-mono text-xs {{ $unreadOnly ? 'text-paper/70' : 'text-muted' }}">{{ $unread }}</span>
        </a>
    </nav>

    @if($notifications->isEmpty())
        <x-ui.empty>{{ $unreadOnly ? __('admin.alerts_page.empty_unread') : __('admin.alerts_page.empty') }}</x-ui.empty>
    @else
        <div class="grid gap-8 max-w-3xl">
            @foreach($days as $date => $items)
                <section aria-labelledby="day{{ $loop->index }}">
                    <h2 id="day{{ $loop->index }}" class="m-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $dayName($date) }}</h2>
                    <ul class="m-0 p-0 list-none border border-rule bg-paper divide-y divide-rule">
                        @foreach($items as $notification)
                            <li>
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit"
                                            class="group flex items-center gap-4 w-full px-4 sm:px-5 py-4 border-0 text-start cursor-pointer motion-safe:transition-colors {{ $notification->isUnread() ? 'bg-accent-wash/40 hover:bg-accent-wash/70' : 'bg-transparent hover:bg-paper-2' }} focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus">
                                        <span class="inline-flex items-center justify-center size-10 shrink-0 rounded-pill {{ $tint[$notification->tone()] ?? 'bg-paper-2 text-neutral' }}">
                                            <x-icon :name="$notification->icon()" class="size-4" />
                                        </span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm leading-snug {{ $notification->isUnread() ? 'text-ink font-medium' : 'text-neutral' }}">{{ $notification->text() }}</span>
                                            <span class="block mt-1 font-mono text-xs text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                        </span>
                                        @if($notification->isUnread())
                                            <span class="size-2 shrink-0 rounded-pill bg-accent-ink" aria-hidden="true"></span>
                                            <span class="sr-only">{{ __('admin.alerts_page.new') }}</span>
                                        @endif
                                        <x-icon name="chevron-right" class="size-3.5 text-muted motion-safe:transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-10">{{ $notifications->onEachSide(1)->links() }}</div>
    @endif
</x-app-layout>
