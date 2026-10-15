{{--
  The top of an admin page: the title, a line saying what the page is for, and
  the page's own actions at the inline end.

  @param  string       $title
  @param  string|null  $intro
--}}
@props(['title', 'intro' => null])

<header class="flex flex-wrap items-end justify-between gap-4 mb-8 pb-6 border-0 border-b border-rule">
    <div class="min-w-0">
        <h1 class="m-0 font-display font-medium rtl:font-semibold text-3xl leading-snug">{{ $title }}</h1>
        @if($intro)
            <p class="m-0 mt-2 max-w-measure text-sm leading-body text-muted">{{ $intro }}</p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-4">{{ $slot }}</div>
    @endif
</header>
