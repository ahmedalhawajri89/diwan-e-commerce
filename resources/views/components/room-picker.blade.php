{{--
  Which of the visitor's rooms a saved piece belongs to: a select and a Move
  button. The select used to submit on change, so a keyboard user scanning the
  rooms with the arrow keys moved the piece, and reloaded the page, at the
  first one.

  @param  \App\Models\WishlistItem  $item
  @param  \Illuminate\Support\Collection  $rooms
--}}
@props(['item', 'rooms'])

<form class="mt-2 flex gap-2" method="POST" action="{{ route('rooms.assign', $item) }}">
    @csrf
    @method('PUT')

    <label class="block min-w-0 flex-1">
        <span class="sr-only">{{ __('diwan.rooms.move_to') }}</span>
        <select name="room_id"
                class="w-full h-11 px-2 bg-paper border border-rule-2 rounded-control text-xs text-ink cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus">
            <option value="" @selected($item->room_id === null)>{{ __('diwan.rooms.no_room') }}</option>
            @foreach($rooms as $option)
                <option value="{{ $option->id }}" @selected($item->room_id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </label>

    <button type="submit"
            class="shrink-0 inline-flex items-center min-h-11 px-3 border border-rule-2 rounded-control bg-transparent text-xs text-ink cursor-pointer hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        {{ __('diwan.rooms.move') }}
    </button>
</form>
