{{--
  A review's rating, out of five.

  The stars are hidden from the accessibility tree and the rating is given as
  real text instead. Both call sites previously put an `aria-label` on the
  <p> itself, which is not reliably announced: `aria-label` only names an
  element that has a role, and a paragraph has none. A screen reader was
  therefore reading out five identical star characters — or, depending on the
  engine, nothing at all.

  The filled and empty stars are one run of text so they stay on one line and
  keep the mono face's even advance.

  @param  int  $rating  1-5
--}}
@props(['rating'])

<p {{ $attributes->merge(['class' => 'hm-num hm-muted hm-t-xs']) }}>
    <span class="hm-visually-hidden">
        {{ __('diwan.testimonials.rating', ['rating' => $rating]) }}
    </span>

    <span aria-hidden="true">{{ str_repeat('★', $rating) }}<span class="hm-faint">{{ str_repeat('★', 5 - $rating) }}</span></span>
</p>
