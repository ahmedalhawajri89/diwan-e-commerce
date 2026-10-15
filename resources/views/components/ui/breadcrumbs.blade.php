{{--
  Where the visitor is: the path from home to this page, the last step not a
  link. The same trail the page publishes to search engines as BreadcrumbList,
  so the two cannot disagree — pass it the same array.

  The separator is a chevron the icon component mirrors in Arabic, and it is
  hidden from screen readers: the list already says "one, then the next".

  @param  array<string, string|null>  $items  label => url; the current page last
--}}
@props(['items'])

<nav {{ $attributes->merge(['class' => 'pt-8 pb-6']) }} aria-label="{{ __('diwan.nav.breadcrumbs') }}">
    <ol class="m-0 p-0 list-none flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
        @foreach($items as $label => $url)
            <li class="inline-flex items-center gap-2 min-w-0">
                @if(! $loop->last && $url)
                    <a href="{{ $url }}" class="inline-flex items-center min-h-11 text-muted no-underline hover:text-ink hover:underline underline-offset-4">{{ $label }}</a>
                    <x-icon name="chevron-right" class="size-3 text-rule-2" />
                @else
                    <span aria-current="page" class="truncate max-w-[24ch] text-ink-2">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
