{{--
  The body of an error page for a healthy app — 403, 404, 419, 429. The
  visitor simply cannot have this page, so the storefront stays around them
  and they are offered the way back. 500 and 503 are the opposite case and
  stand alone; see errors/minimal.

  @param  string  $code
  @param  bool    $search  offer the catalogue search (a missing page)
--}}
@props(['code', 'search' => false])

<x-ui.page class="max-w-3xl py-20 lg:py-28">
    <p class="m-0 font-display text-[clamp(5rem,14vw,9rem)] leading-none text-rule" dir="ltr" aria-hidden="true">{{ $code }}</p>

    <h1 class="m-0 mt-4 font-display font-medium rtl:font-semibold text-3xl leading-snug">{{ __('diwan.error.'.$code.'_title') }}</h1>
    <p class="m-0 mt-4 max-w-[52ch] leading-body text-muted">{{ __('diwan.error.'.$code.'_body') }}</p>

    @if($search)
        <form method="GET" action="{{ route('furnish.products') }}"
              class="flex items-center gap-4 max-w-md mt-10 border-0 border-b border-ink">
            <label for="notFoundSearch" class="sr-only">{{ __('diwan.search.label') }}</label>
            <x-icon name="search" class="size-4 text-muted" />
            <input type="search" id="notFoundSearch" name="q" placeholder="{{ __('diwan.search.placeholder') }}"
                   class="flex-1 min-w-0 h-12 p-0 border-0 bg-transparent text-base text-ink outline-none">
            <button type="submit" class="p-0 border-0 bg-transparent font-label text-xs font-medium uppercase tracking-label text-ink cursor-pointer hover:text-accent-ink">{{ __('diwan.util.search') }}</button>
        </form>
    @endif

    <nav class="flex flex-wrap gap-x-8 gap-y-4 mt-12 pt-8 border-0 border-t border-rule" aria-label="{{ __('diwan.nav.help') }}">
        <x-ui.link :href="route('furnish.index')">{{ __('diwan.nav.home') }}</x-ui.link>
        <x-ui.link :href="route('furnish.products')">{{ __('diwan.nav.catalogue') }}</x-ui.link>
        @if($search)
            <x-ui.link :href="route('furnish.lookbook')">{{ __('diwan.nav.rooms') }}</x-ui.link>
        @endif
        <x-ui.link :href="route('furnish.contact')">{{ __('diwan.nav.contact') }}</x-ui.link>
    </nav>
</x-ui.page>
