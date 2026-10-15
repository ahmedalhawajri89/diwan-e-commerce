{{--
  One figure in an <x-admin.stats> row.

  @param  string  $label
  @param  mixed   $value
  @param  bool    $flag   the figure wants attention (pending orders, unmeasured pieces)
--}}
@props(['label', 'value', 'flag' => false])

<div class="min-w-0 p-5 bg-paper {{ $flag ? 'bg-warn-wash' : '' }}">
    <dt class="font-label text-xs font-medium uppercase tracking-label text-muted">{{ $label }}</dt>
    <dd class="m-0 mt-2 font-mono text-xl sm:text-2xl [overflow-wrap:anywhere] {{ $flag ? 'text-warn' : 'text-ink' }}">{{ $value }}</dd>
</div>
