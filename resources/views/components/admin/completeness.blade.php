{{--
  How whole a piece's page is: one short bar per part (photo, price,
  dimensions, Arabic text, description), filled for what it has. The parts
  it lacks are named beside it for a screen reader and on hover for the eye.

  @param  \App\Models\Product  $product  with images and variants loaded
--}}
@props(['product'])

@php
    $parts = $product->completeness();
    $done = count(array_filter($parts));
    $total = count($parts);
    $missing = collect($parts)->reject()->keys()->map(fn ($part) => __('admin.complete.'.$part))->implode(__('admin.complete.joiner'));
    $fill = $done === $total ? 'bg-ok' : ($done <= 2 ? 'bg-warn' : 'bg-accent-ink');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }} @if($missing) title="{{ __('admin.complete.missing', ['parts' => $missing]) }}" @endif>
    <span class="inline-flex gap-0.5" aria-hidden="true">
        @foreach($parts as $part => $has)
            <span class="w-2.5 h-1.5 rounded-pill {{ $has ? $fill : 'bg-rule' }}"></span>
        @endforeach
    </span>
    <span class="font-mono text-xs {{ $done === $total ? 'text-ok' : 'text-muted' }}" dir="ltr">{{ $done }}/{{ $total }}</span>
    @if($missing)<span class="sr-only">{{ __('admin.complete.missing', ['parts' => $missing]) }}</span>@endif
</span>
