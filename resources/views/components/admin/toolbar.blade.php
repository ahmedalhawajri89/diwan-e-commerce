{{--
  The head of an admin list: a search, the status chips with their real
  counts, the sort, and the export of exactly what is on screen.

  The search is a GET form with a button, never submitted on change. The
  chips and the sort are links, so every view of a list is an address that
  can be kept, shared or reloaded. The filters a search does not own (the
  chip, the sort, a category) ride along as hidden fields.

  @param  string       $action       the list's own URL, without a query
  @param  string|null  $q
  @param  string       $placeholder
  @param  array        $chips        from AdminList::chips()
  @param  array        $sorts        from AdminList::sorts()
  @param  string|null  $export       the CSV address, or null for none
  @param  array        $keep         query parameters the search form carries along
  @slot   fields                     more inputs for the search form (a category)
--}}
@props(['action', 'q' => null, 'placeholder' => '', 'chips' => [], 'sorts' => [], 'export' => null, 'keep' => [], 'fields' => null])

@php
    $pill = 'inline-flex items-center gap-2 min-h-9 px-3.5 rounded-pill border text-sm no-underline whitespace-nowrap '
          . 'transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $sortLabel = collect($sorts)->firstWhere('active', true)['label'] ?? null;
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-4 mb-6']) }}>
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ $action }}" role="search" class="flex flex-1 min-w-64 max-w-xl">
            @foreach($keep as $name => $value)
                @if($value !== null && $value !== '')
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach
            {{ $fields }}
            <label class="relative flex-1">
                <span class="sr-only">{{ $placeholder }}</span>
                <x-icon name="search" class="absolute top-1/2 start-3.5 -translate-y-1/2 size-4 text-muted pointer-events-none" />
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ $placeholder }}" autocomplete="off"
                       class="w-full h-11 ps-10 pe-3 border border-rule-2 border-e-0 {{ $fields ? '' : 'rounded-s-control' }} bg-paper text-sm text-ink placeholder:text-muted focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus">
            </label>
            <button type="submit" class="h-11 px-5 border-0 rounded-e-control bg-ink text-paper text-sm cursor-pointer transition-colors hover:bg-accent-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                {{ __('admin.list.search') }}
            </button>
        </form>

        @if($q)
            <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}" class="text-sm text-accent-ink underline underline-offset-4">{{ __('admin.list.clear_search') }}</a>
        @endif

        <div class="flex items-center gap-2 ms-auto">
            @if($sorts)
                <div x-data="dropdown" @click.outside="close" @keydown.escape="dismiss" class="relative">
                    <button type="button" x-ref="button" @click="toggle" :aria-expanded="open" aria-expanded="false"
                            class="inline-flex items-center gap-2 h-11 px-4 border border-rule-2 rounded-control bg-paper text-sm text-ink cursor-pointer transition-colors hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                        <span class="text-muted">{{ __('admin.list.sort_by') }}</span> {{ $sortLabel }}
                        <x-icon name="chevron-down" class="size-3" />
                    </button>
                    <ul x-show="open" x-cloak
                        x-transition:enter="transition-[opacity,translate] duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute end-0 top-full z-20 mt-2 min-w-48 m-0 p-1.5 list-none bg-paper border border-rule">
                        @foreach($sorts as $sort)
                            <li>
                                <a href="{{ $sort['url'] }}" @if($sort['active']) aria-current="true" @endif
                                   class="flex items-center justify-between gap-4 min-h-10 px-3 rounded-control text-sm text-ink no-underline hover:bg-paper-2 aria-[current=true]:font-medium">
                                    {{ $sort['label'] }}
                                    @if($sort['active'])<x-icon name="check-lg" class="size-3.5 text-accent-ink" />@endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($export)
                <a href="{{ $export }}" data-no-prefetch
                   class="inline-flex items-center gap-2 h-11 px-4 border border-rule-2 rounded-control text-sm text-ink no-underline transition-colors hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    <x-icon name="download" class="size-4" />{{ __('admin.list.export') }}
                </a>
            @endif
        </div>
    </div>

    @if($chips)
        <nav aria-label="{{ __('admin.list.filter') }}" class="flex flex-wrap gap-2">
            @foreach($chips as $chip)
                <a href="{{ $chip['url'] }}" @if($chip['active']) aria-current="page" @endif
                   class="{{ $pill }} {{ $chip['active'] ? 'border-ink bg-ink text-paper' : 'border-rule bg-paper text-ink hover:border-ink' }}">
                    {{ $chip['label'] }}
                    <span class="font-mono text-xs {{ $chip['active'] ? 'text-paper/70' : 'text-muted' }}">{{ $chip['count'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif
</div>
