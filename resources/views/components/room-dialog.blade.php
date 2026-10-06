{{--
  "My room" — the first layer of the fit system.

  Two numbers, stored in the session. No account, no upsell, no wizard. The
  copy says what it is for and what happens to it, because asking a stranger
  to measure their living room needs a reason attached.

  A native <dialog>: anything with `data-open-dialog="roomModal"` opens it,
  on any page. See resources/js/components/dialogs.js.

  @param  array|null  $room
--}}
@props(['room' => null])

@php
    $field = 'block w-full h-12 px-3 bg-paper text-ink font-mono text-base '
           . 'border border-rule-2 rounded-control '
           . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
    $label = 'block mb-2 text-xs uppercase tracking-label text-muted';
@endphp

<dialog id="roomModal" aria-labelledby="roomModalLabel"
        class="m-auto w-[min(32rem,calc(100vw-2rem))] max-w-none p-0 border-0 bg-paper text-ink">
    <div class="p-8 sm:p-10">
        <div class="flex items-start justify-between gap-6 mb-4">
            <h2 id="roomModalLabel" class="m-0 font-display font-medium text-2xl leading-snug max-w-[22ch]">
                {{ __('diwan.room.title') }}
            </h2>
            <button type="button" data-close-dialog aria-label="{{ __('diwan.nav.close') }}"
                    class="shrink-0 inline-flex items-center justify-center size-10 -me-2 p-0 border-0 bg-transparent text-ink cursor-pointer hover:text-accent-ink">
                <x-icon name="x-lg" class="size-4" />
            </button>
        </div>

        <p class="m-0 mb-8 text-sm leading-body text-muted">
            {{ __('diwan.room.body') }}
        </p>

        <form method="POST" action="{{ route('room.store') }}">
            @csrf

            <div class="grid grid-cols-[1fr_auto_1fr] items-end gap-3">
                <label class="block">
                    <span class="{{ $label }}">{{ __('diwan.room.width') }} ({{ __('diwan.room.unit') }})</span>
                    <input type="number" name="width" inputmode="numeric"
                           min="100" max="2000" step="1" required
                           value="{{ old('width', $room['width'] ?? '') }}"
                           placeholder="350" dir="ltr" class="{{ $field }}">
                </label>

                <span class="pb-3 font-mono text-muted" aria-hidden="true">&times;</span>

                <label class="block">
                    <span class="{{ $label }}">{{ __('diwan.room.length') }} ({{ __('diwan.room.unit') }})</span>
                    <input type="number" name="length" inputmode="numeric"
                           min="100" max="2000" step="1" required
                           value="{{ old('length', $room['length'] ?? '') }}"
                           placeholder="480" dir="ltr" class="{{ $field }}">
                </label>
            </div>

            <p class="m-0 mt-3 text-xs text-muted">{{ __('diwan.room.hint') }}</p>

            @if($room)
                <div class="flex justify-between mt-6 pt-4 border-t border-rule text-sm">
                    <span class="text-muted">{{ __('diwan.room.floor') }}</span>
                    <span class="font-mono">{{ number_format(($room['width'] * $room['length']) / 10000, 1) }} m&sup2;</span>
                </div>
            @endif

            <x-ui.button type="submit" :block="true" class="mt-6">
                {{ __('diwan.room.save') }}
            </x-ui.button>
        </form>

        @if($room)
            <form class="mt-2" method="POST" action="{{ route('room.destroy') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="ghost" :block="true">
                    {{ __('diwan.room.clear') }}
                </x-ui.button>
            </form>
        @endif
    </div>
</dialog>
