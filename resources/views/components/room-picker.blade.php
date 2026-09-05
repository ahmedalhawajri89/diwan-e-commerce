{{--
  Move one saved piece between rooms.

  A plain select that submits on change — no drag and drop. Dragging is
  unreachable by keyboard, awkward on a touch screen, and buys nothing here
  that a two-click select does not.

  @param  \App\Models\WishlistItem  $item
  @param  \Illuminate\Support\Collection  $rooms
--}}
@props(['item', 'rooms'])

<form method="POST" action="{{ route('rooms.assign', $item) }}" style="margin-top: var(--space-xs);">
    @csrf
    @method('PUT')

    <label class="hm-field" style="margin: 0;">
        <span class="hm-visually-hidden">{{ __('diwan.rooms.move_to') }}</span>
        <select name="room_id" onchange="this.form.submit()"
                style="font-size: var(--text-xs); padding: var(--space-2xs) var(--space-xs);">
            <option value="" @selected($item->room_id === null)>{{ __('diwan.rooms.no_room') }}</option>
            @foreach($rooms as $option)
                <option value="{{ $option->id }}" @selected($item->room_id === $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </label>

    <noscript>
        <button type="submit" class="hm-btn hm-btn--quiet">{{ __('diwan.rooms.move_to') }}</button>
    </noscript>
</form>
