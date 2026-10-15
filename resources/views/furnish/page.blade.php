{{--
  Shipping, privacy and terms. One template for all three — they differ only in
  which copy block they read, and a policy page that reads as a document rather
  than a landing page is the right shape for a document.

  The copy states what the code actually does, which is the only claim we can
  make honestly: the room lives in the session, payment is cash on delivery
  because no gateway exists, dimensions are recorded rather than estimated.
  Anything needing a real figure the owner has not supplied — a returns window,
  a delivery radius — is left as a labelled hole rather than invented.

  @param  string  $page  shipping | privacy | terms
--}}
@extends('layouts.furnish')

@section('title', __("diwan.pages.{$page}.title"))

@section('content')

    @php
        // Sections are numbered in the copy file, so the template does not need
        // to know how many any given page has.
        $sections = collect(range(1, 6))
            ->map(fn ($n) => [
                'title' => "diwan.pages.{$page}.s{$n}_title",
                'body'  => "diwan.pages.{$page}.s{$n}_body",
            ])
            ->filter(fn ($s) => Lang::has($s['title']));

        $others = collect(['shipping', 'privacy', 'terms'])->reject(fn ($p) => $p === $page);
    @endphp

    <x-ui.page class="max-w-4xl pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.pages.'.$page.'.title')" :lede="__('diwan.pages.'.$page.'.lede')" />

        <ol class="m-0 p-0 list-none">
            @foreach($sections->values() as $i => $section)
                <li class="grid gap-3 py-8 border-0 border-b border-rule md:grid-cols-[3rem_1fr] md:gap-8">
                    <span class="font-mono text-xs text-accent-ink pt-1.5" dir="ltr">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h2 class="m-0 font-display text-2xl leading-snug">{{ __($section['title']) }}</h2>
                        <p class="m-0 mt-3 max-w-measure leading-body text-ink-2">{{ __($section['body']) }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- The honest hole: a policy with an invented number is worse than a
             policy that says the number is coming. --}}
        <x-ui.note tone="warn" icon="dash-lg" class="mt-10">
            <strong>{{ __('diwan.pages.awaiting') }}</strong> — {{ __('diwan.pages.awaiting_note') }}
        </x-ui.note>

        <nav class="flex flex-wrap gap-x-8 gap-y-4 mt-12" aria-label="{{ __('diwan.nav.help') }}">
            @foreach($others as $other)
                <x-ui.link :href="route('furnish.page', $other)">{{ __('diwan.pages.'.$other.'.title') }}</x-ui.link>
            @endforeach
            <x-ui.link :href="route('furnish.contact')">{{ __('diwan.nav.contact') }}</x-ui.link>
        </nav>
    </x-ui.page>

@endsection
