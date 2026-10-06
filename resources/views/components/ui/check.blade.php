{{--
  A checkbox and its words, as one target. Attributes (name, value, checked,
  data-autosubmit…) pass through to the <input>.
--}}
<label class="flex items-center gap-2.5 py-1 text-sm text-neutral cursor-pointer hover:text-ink">
    <input type="checkbox" {{ $attributes->merge(['class' => 'size-4 m-0 shrink-0 accent-ink cursor-pointer']) }}>
    <span>{{ $slot }}</span>
</label>
