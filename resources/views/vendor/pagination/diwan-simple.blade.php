{{-- The pager for a simple paginator: previous and next, nothing to count. --}}
@if ($paginator->hasPages())
    @php
        $cell = 'inline-flex items-center gap-2 h-10 px-4 border border-rule text-xs no-underline transition-colors';
    @endphp

    <nav class="flex justify-between gap-4" aria-label="{{ __('diwan.common.pages') }}">
        @if ($paginator->onFirstPage())
            <span class="{{ $cell }} text-rule-2" aria-disabled="true"><x-icon name="chevron-left" class="size-3.5" />{!! __('diwan.common.previous') !!}</span>
        @else
            <a class="{{ $cell }} text-ink hover:border-ink" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" class="size-3.5" />{!! __('diwan.common.previous') !!}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="{{ $cell }} text-ink hover:border-ink" href="{{ $paginator->nextPageUrl() }}" rel="next">{!! __('diwan.common.next') !!}<x-icon name="chevron-right" class="size-3.5" /></a>
        @else
            <span class="{{ $cell }} text-rule-2" aria-disabled="true">{!! __('diwan.common.next') !!}<x-icon name="chevron-right" class="size-3.5" /></span>
        @endif
    </nav>
@endif
