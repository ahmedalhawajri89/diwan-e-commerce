{{--
  "My room" — layer 1 of the fit system.

  Two numbers, stored in the session. No account, no upsell, no wizard. The
  copy says what it is for and what happens to it, because asking a stranger
  to measure their living room needs a reason attached.

  @param  array|null  $room
--}}
@props(['room' => null])

<div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body hm-p-xl">
                <div class="d-flex justify-content-between align-items-start hm-mb-md">
                    <h2 class="hm-t-xl hm-m-0 hm-hold-22ch" id="roomModalLabel">
                        {{ __('diwan.room.title') }}
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('diwan.nav.close') }}"></button>
                </div>

                <p class="hm-muted hm-t-sm hm-mb-lg">
                    {{ __('diwan.room.body') }}
                </p>

                <form method="POST" action="{{ route('room.store') }}" class="hm-room__form">
                    @csrf

                    <div class="hm-room__pair">
                        <label class="hm-field hm-m-0">
                            <span class="hm-field__label">{{ __('diwan.room.width') }} ({{ __('diwan.room.unit') }})</span>
                            <input type="number" name="width" inputmode="numeric"
                                   min="100" max="2000" step="1" required
                                   value="{{ old('width', $room['width'] ?? '') }}"
                                   placeholder="350" dir="ltr">
                        </label>

                        <span class="hm-room__times" aria-hidden="true">&times;</span>

                        <label class="hm-field hm-m-0">
                            <span class="hm-field__label">{{ __('diwan.room.length') }} ({{ __('diwan.room.unit') }})</span>
                            <input type="number" name="length" inputmode="numeric"
                                   min="100" max="2000" step="1" required
                                   value="{{ old('length', $room['length'] ?? '') }}"
                                   placeholder="480" dir="ltr">
                        </label>
                    </div>

                    <p class="hm-field__hint hm-m-0">{{ __('diwan.room.hint') }}</p>

                    @if($room)
                        <div class="hm-room__floor">
                            <span class="hm-muted">{{ __('diwan.room.floor') }}</span>
                            <span class="hm-num">{{ number_format(($room['width'] * $room['length']) / 10000, 1) }} m&sup2;</span>
                        </div>
                    @endif

                    <div class="d-flex gap-2 hm-mt-xs">
                        <button type="submit" class="hm-btn hm-btn--primary hm-flex-auto">
                            {{ __('diwan.room.save') }}
                        </button>
                    </div>
                </form>

                @if($room)
                    <form class="hm-mt-xs" method="POST" action="{{ route('room.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hm-btn hm-btn--quiet hm-btn--block">
                            {{ __('diwan.room.clear') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
