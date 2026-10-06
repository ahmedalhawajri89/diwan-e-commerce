{{--
  The pager. Numbers in the mono face, the current page filled in ink, and a
  line of honest context ("Showing 1–12 of 29") beside it. Set as Laravel's
  default view in AppServiceProvider.
--}}
@if ($paginator->hasPages())
    @php
        $cell = 'inline-flex items-center justify-center min-w-10 h-10 px-2 border border-rule '
              . 'font-mono text-xs no-underline transition-colors';
        $link = $cell.' text-ink hover:border-ink';
        $off = $cell.' text-rule-2 cursor-default';
    @endphp

    <nav class="flex flex-wrap items-center justify-between gap-4" aria-label="{{ __('diwan.common.pages') }}">
        <ul class="flex flex-wrap gap-1.5 m-0 p-0 list-none">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $off }}" aria-disabled="true" aria-label="{{ __('diwan.common.previous') }}"><x-icon name="chevron-left" class="size-3.5" /></span>
                @else
                    <a class="{{ $link }}" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('diwan.common.previous') }}"><x-icon name="chevron-left" class="size-3.5" /></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $off }}" aria-disabled="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $cell }} bg-ink border-ink text-paper" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="{{ $link }}" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="{{ $link }}" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('diwan.common.next') }}"><x-icon name="chevron-right" class="size-3.5" /></a>
                @else
                    <span class="{{ $off }}" aria-disabled="true" aria-label="{{ __('diwan.common.next') }}"><x-icon name="chevron-right" class="size-3.5" /></span>
                @endif
            </li>
        </ul>

        <p class="m-0 text-xs text-muted">
            {{ __('diwan.catalogue.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </p>
    </nav>
@endif
