{{--
  Who did what in the panel, newest first, grouped by day. Filtered by the
  kind of thing, by person, or by the name of what was touched; the export
  downloads exactly what is shown. A year is kept.
--}}
@php
    $dayName = function (string $date) {
        $day = \Illuminate\Support\Carbon::parse($date);

        return match (true) {
            $day->isToday() => __('admin.alerts_page.today'),
            $day->isYesterday() => __('admin.alerts_page.yesterday'),
            default => $day->translatedFormat(__('admin.format.day')),
        };
    };
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.activity.title') }}</x-slot>

    <x-admin.head :title="__('admin.activity.title')" :intro="__('admin.activity.intro')" />

    <x-admin.toolbar :action="route('admin.activity.index')" :q="$q" :placeholder="__('admin.activity.search')"
                     :chips="$chips" :keep="['kind' => $kind]"
                     :export="route('admin.activity.export', request()->query())">
        <x-slot name="fields">
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.activity.who') }}</span>
                <select name="person" class="max-w-32 sm:max-w-none h-11 ps-3 pe-8 border border-rule-2 border-e-0 bg-paper text-sm text-ink first:rounded-s-control focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus">
                    <option value="">{{ __('admin.activity.everyone') }}</option>
                    @foreach($people as $option)
                        <option value="{{ $option->id }}" @selected($person === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </label>
        </x-slot>
    </x-admin.toolbar>

    @if($logs->isEmpty())
        <x-ui.empty>{{ $q || $kind || $person ? __('admin.list.no_match') : __('admin.activity.empty') }}</x-ui.empty>
    @else
        <div class="grid gap-8 max-w-4xl">
            @foreach($days as $date => $items)
                <section aria-labelledby="day{{ $loop->index }}">
                    <h2 id="day{{ $loop->index }}" class="m-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $dayName($date) }}</h2>
                    <ol class="m-0 p-0 list-none border border-rule bg-paper divide-y divide-rule">
                        @foreach($items as $log)
                            <li class="px-4 sm:px-5 py-4"><x-admin.activity-line :log="$log" /></li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        </div>

        <div class="mt-10">{{ $logs->onEachSide(1)->links() }}</div>
    @endif
</x-app-layout>
