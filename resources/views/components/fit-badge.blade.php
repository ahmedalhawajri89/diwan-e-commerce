{{--
  The fit badge — layer 2 of the fit system.

  Status is never colour alone: every state ships an icon and a word, so it
  survives greyscale, colour-blindness, and a screen reader.

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

    $classes = [
        Fit::FITS  => 'hm-fit hm-fit--fits',
        Fit::TIGHT => 'hm-fit hm-fit--tight',
        Fit::NO    => 'hm-fit hm-fit--no',
    ][$status] ?? 'hm-fit hm-fit--unknown';
@endphp

@if($needsRoom && $onCard)
    {{-- Nothing. The scale rule beneath the card already carries the numbers,
         and the prompt lives once per page rather than once per card. --}}
@elseif($needsRoom)
    <button type="button" class="hm-fit hm-fit--prompt" data-bs-toggle="modal" data-bs-target="#roomModal">
        <i class="bi bi-rulers" aria-hidden="true"></i>
        <span>{{ __('diwan.fit.set_room') }}</span>
    </button>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        <i class="bi {{ Fit::icon($status) }}" aria-hidden="true"></i>
        <span>{{ __('diwan.fit.' . $status) }}</span>
    </span>
@endif

@if($withNote)
    @if($assessment['note'])
        <span class="hm-fit__note">{{ __('diwan.fit.note.' . $assessment['note']) }}</span>
    @elseif($status === Fit::UNKNOWN)
        <span class="hm-fit__note">{{ __('diwan.fit.explain.unknown') }}</span>
    @elseif($status === Fit::FITS)
        <span class="hm-fit__note">
            {{ __('diwan.fit.explain.fits') }}
            @if($assessment['share'])
                {{ __('diwan.fit.share', ['percent' => round($assessment['share'] * 100)]) }}
            @endif
        </span>
    @endif
@endif
