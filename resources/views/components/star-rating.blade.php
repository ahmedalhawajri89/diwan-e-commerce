{{--
  A review's rating, out of five.

  The stars are hidden from the accessibility tree and the rating is given as
  real text instead: `aria-label` on a paragraph is not reliably announced,
  because a paragraph has no role to name.

  The filled and empty stars are one run of text so they stay on one line and
  keep the mono face's even advance.

  @param  int  $rating  1-5
--}}
@props(['rating'])

<p {{ $attributes->merge(['class' => 'font-mono text-xs tracking-[0.2em] text-accent-ink']) }}>
    <span class="sr-only">{{ __('diwan.testimonials.rating', ['rating' => $rating]) }}</span>
    <span aria-hidden="true">{{ str_repeat('★', $rating) }}<span class="text-rule">{{ str_repeat('★', 5 - $rating) }}</span></span>
</p>
