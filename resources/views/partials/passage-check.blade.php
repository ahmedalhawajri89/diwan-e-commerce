{{--
  Before ordering: the pieces in the bag that may not reach the visitor's
  room, by the way in they gave, each with its reason. Said in the bag and
  again at checkout, before the courier is at the door, not after.

  Without a way in, a quiet offer to check one, and, at checkout, the old
  rule of thumb still stands: anything wider than a common doorway is named.

  @param  \Illuminate\Support\Collection  $items   cart items, variant and product loaded
  @param  array|null  $wayIn
  @param  bool        $doorway  also name pieces wider than a common doorway
                                when there is no way in (checkout)
--}}
@php
    use App\Support\Fit;
    use App\Support\Passage;

    $doorway ??= false;
    $join = app()->getLocale() === 'ar' ? '، ' : ', ';

    $flagged = $wayIn
        ? $items->filter(fn ($item) => in_array(Passage::assess($item->variant, $wayIn)['status'], [Passage::NO, Passage::HOIST, Passage::TIGHT], true))
        : collect();

    // The rule of thumb, for a visitor who has not given their way in.
    $tooWide = ! $wayIn && $doorway
        ? $items->filter(function ($item) {
            $v = $item->variant;
            if (! $v || ! $v->width_cm || ! $v->depth_cm) {
                return false;
            }

            return min($v->width_cm, $v->depth_cm, $v->height_cm ?: PHP_INT_MAX) > Fit::DOORWAY_CM;
        })
        : collect();
@endphp

@if($flagged->isNotEmpty())
    <x-ui.note tone="warn" class="mt-8">
        <strong>{{ __('diwan.passage.bag_title') }}</strong> — {{ __('diwan.passage.bag_note') }}
        <ul class="m-0 mt-3 p-0 list-none grid gap-3">
            @foreach($flagged as $item)
                <li>
                    <span class="block mb-1.5 text-sm font-medium text-ink">{{ $item->product?->name }}</span>
                    <x-passage-badge :variant="$item->variant" :way-in="$wayIn" :withNote="true" />
                </li>
            @endforeach
        </ul>
    </x-ui.note>
@elseif($tooWide->isNotEmpty())
    <x-ui.note tone="warn" class="mt-8">
        <strong>{{ __('diwan.checkout.doorway') }}</strong> —
        {{ __('diwan.checkout.doorway_note') }}
        <span class="opacity-80">({{ $tooWide->map(fn ($i) => $i->product?->name)->filter()->join($join) }})</span>
        <button type="button" data-open-dialog="routeModal"
                class="inline p-0 ms-1 border-0 bg-transparent text-sm text-ink underline underline-offset-4 cursor-pointer">{{ __('diwan.passage.bag_check') }}</button>
    </x-ui.note>
@elseif(! $wayIn && $items->isNotEmpty())
    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 m-0 mt-6 text-sm text-muted">
        <x-icon name="door-open" class="size-4 text-accent-ink" />{{ __('diwan.passage.bag_unset') }}
        <button type="button" data-open-dialog="routeModal"
                class="inline-flex items-center min-h-9 p-0 border-0 bg-transparent text-sm font-medium text-ink underline underline-offset-4 cursor-pointer hover:text-accent-ink">{{ __('diwan.passage.bag_check') }}</button>
    </p>
@endif
