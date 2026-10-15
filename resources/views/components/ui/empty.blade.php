{{--
  An empty list, said plainly, with the one way forward.

  @param  string|null  $title
--}}
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-5 px-6 py-20 text-center border border-dashed border-rule-2']) }}>
    @if($title)
        <p class="m-0 font-display text-2xl">{{ $title }}</p>
    @endif
    <div class="max-w-[44ch] text-sm leading-body text-muted">{{ $slot }}</div>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
