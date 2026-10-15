{{--
  The fit badge — layer 2 of the fit system.

  Status is never colour alone: every state ships an icon and a word, so it
  survives greyscale, colour-blindness, and a screen reader. It sits on paper
  even over a photograph, so the word stays legible whatever is behind it.

  @param  \App\Models\ProductVariant|null  $variant
  @param  array|null  $room     the visitor's saved room, or null
  @param  bool        $withNote render the explanatory line beneath
  @param  bool        $onCard   true inside a product card, where the
                                "set your room" prompt is suppressed — twelve
                                identical prompts in one grid is noise, and the
                                filter rail already carries the invitation once
--}}
@props(['variant' => null, 'room' => null, 'withNote' => false, 'onCard' => false])

@php
    use App\Support\Fit;

    $assessment = Fit::assess($variant, $room);
    $status = $assessment['status'];

    // A visitor who has not set a room is offered the control rather than a
    // verdict — the honest answer is "we cannot know yet". A piece with no
    // dimensions on file is a different answer, and says so.
    $needsRoom = $status === Fit::NO_ROOM;

    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-pill bg-paper '
          . 'font-label text-[0.6875rem] font-medium leading-none';

    $tone = [
        Fit::FITS  => 'text-ok',
        Fit::TIGHT => 'text-warn',
        Fit::NO    => 'text-danger',
    ][$status] ?? 'text-muted';

    $note = 'block mt-2 text-xs leading-body text-muted';
@endphp

@if($needsRoom && $onCard)
    {{-- Nothing. The scale rule beneath the card already carries the numbers,
         and the prompt lives once per page rather than once per card. --}}
@elseif($needsRoom)
    <button type="button" data-open-dialog="roomModal"
            class="{{ $pill }} border border-rule-2 text-ink cursor-pointer transition-colors hover:border-ink">
        <x-icon name="rulers" class="size-3.5" />
        <span>{{ __('diwan.fit.set_room') }}</span>
    </button>
@else
    <span {{ $attributes->merge(['class' => $pill.' '.$tone]) }}>
        <x-icon :name="Fit::icon($status)" class="size-3.5" />
        <span>{{ __('diwan.fit.'.$status) }}</span>
    </span>
@endif

@if($withNote)
    @if($assessment['note'])
        <span class="{{ $note }}">{{ __('diwan.fit.note.'.$assessment['note']) }}</span>
    @elseif($status === Fit::UNKNOWN)
        <span class="{{ $note }}">{{ __('diwan.fit.explain.unknown') }}</span>
    @elseif($status === Fit::FITS)
        <span class="{{ $note }}">
            {{ __('diwan.fit.explain.fits') }}
            @if($assessment['share'])
                {{ __('diwan.fit.share', ['percent' => round($assessment['share'] * 100)]) }}
            @endif
        </span>
    @endif
@endif
