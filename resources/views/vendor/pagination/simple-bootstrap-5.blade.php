{{--
    Published from the framework so the "Showing x to y" line can use this
    project's own type and colour classes instead of Bootstrap's `.small` and
    `.text-muted`.

    `.small` lives in Bootstrap's _type.scss, which style.scss no longer
    imports — the app draws its own headings and body type. Pulling that whole
    partial back for one line would undo the point of the selective import.

    Being published, this view no longer tracks framework updates. It is a
    dozen lines of markup; the trade is worth it, but check it on a major
    upgrade. Everything else here is untouched: _hallmark.scss already styles
    `.page-item` and `.page-link`.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{!! __('pagination.previous') !!}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        {!! __('pagination.previous') !!}
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{!! __('pagination.next') !!}</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{!! __('pagination.next') !!}</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
