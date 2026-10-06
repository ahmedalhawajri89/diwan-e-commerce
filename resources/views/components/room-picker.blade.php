{{--
  Which of the visitor's rooms a saved piece belongs to. Changing the select
  submits it (data-autosubmit, in interactions.js); without JavaScript the
  button inside <noscript> does the same.

  @param  \App\Models\WishlistItem  $item
  @param  \Illuminate\Support\Collection  $rooms
--}}
@props(['item', 'rooms'])

<form class="mt-2" method="POST" action="{{ route('rooms.assign', $item) }}">
    @csrf
    @method('PUT')

    <label class="block">
        <span class="sr-only">{{ __('diwan.rooms.move_to') }}</span>
        <select name="room_id" data-autosubmit
                class="w-full h-9 px-2 bg-paper border border-rule-2 rounded-control text-xs text-ink cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus">
            <option value="" @selected($item->room_id === null)>{{ __('diwan.rooms.no_room') }}</option>
            @foreach($rooms as $option)
                <option value="{{ $option->id }}" @selected($item->room_id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </label>

    <noscript>
        <x-ui.button type="submit" variant="ghost" class="mt-2">{{ __('diwan.rooms.move_to') }}</x-ui.button>
    </noscript>
</form>
