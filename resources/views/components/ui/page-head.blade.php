{{--
  The top of an inner page: a small eyebrow, the one headline, a sentence of
  context, and an optional slot at the inline end (a count, a link).

  @param  string       $title
  @param  string|null  $lede
  @param  string|null  $eyebrow
  @param  string       $as  the heading element, h1 unless the page has one already
--}}
@props(['title', 'lede' => null, 'eyebrow' => null, 'as' => 'h1'])

<header {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-6 pt-12 pb-8 lg:pt-16 lg:pb-10 border-0 border-b border-rule']) }}>
    <div class="max-w-[52ch]">
        @if($eyebrow)
            <p class="m-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $eyebrow }}</p>
        @endif
        <{{ $as }} class="m-0 font-display font-medium rtl:font-semibold text-3xl lg:text-display-s leading-tight tracking-display">{{ $title }}</{{ $as }}>
        @if($lede)
            <p class="m-0 mt-4 leading-body text-muted">{{ $lede }}</p>
        @endif
    </div>

    @isset($aside)
        <div class="text-sm">{{ $aside }}</div>
    @endisset
</header>
