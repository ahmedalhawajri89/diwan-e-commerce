{{--
  "The way in" — the second layer of the fit system, after the room.

  Whether a piece can reach the room at all: the narrowest door on the way,
  how it gets up (ground floor, lift, stairs), and a balcony if a hoist is an
  option. Every field is optional; one left blank is reported as unchecked,
  never assumed. Each measurement has a small drawing of where the tape goes.
  Stored in the session, like the room.

  Opened by any `data-open-dialog="routeModal"` (dialogs.js).

  @param  array|null  $wayIn
--}}
@props(['wayIn' => null])

@php
    $access = old('access', $wayIn['access'] ?? 'lift');
    $value = fn (string $field, ?string $step, string $key) => old($field, $step ? ($wayIn[$step][$key] ?? '') : '');
    $field = 'block w-full h-11 px-3 bg-paper text-ink font-mono text-base border border-rule-2 rounded-control '
           . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus';
    // A field that came back with an error is ringed in red, as well as named in the message.
    $bad = fn (string $name) => isset($errors) && $errors->has($name);
    $fieldFor = fn (string $name) => $bad($name) ? str_replace('border-rule-2', 'border-danger', $field) : $field;
    $label = 'block mb-1.5 text-xs text-muted';
    $group = 'grid grid-cols-[4.5rem_1fr] gap-4 items-start pt-5 mt-5 border-0 border-t border-rule';
    $legend = 'm-0 mb-1 p-0 text-sm font-medium text-ink';
    $hint = 'm-0 mb-3 text-xs leading-body text-muted';
    // The drawings: lines in the ink of the text, a dimension line in brass.
    $svg = 'block w-full h-auto text-muted';
    // An error page draws the layout without the session's error bag.
    $failed = isset($errors) && $errors->hasAny(['access', 'door_w', 'door_h', 'lift_door_w', 'lift_door_h', 'lift_w', 'lift_d', 'lift_h', 'stairs_w', 'stairs_landing', 'inner_w', 'inner_landing', 'balcony_w', 'balcony_h']);
@endphp

<dialog id="routeModal" aria-labelledby="routeModalLabel" @if($failed) data-open-on-load @endif
        class="m-auto w-[min(36rem,calc(100vw-2rem))] max-w-none max-h-[calc(100dvh-2rem)] p-0 border-0 bg-paper text-ink">
    <form method="POST" action="{{ route('room.route.store') }}" x-data="routeForm" @change="pick" class="p-7 sm:p-9">
        @csrf

        <div class="flex items-start justify-between gap-6 mb-3">
            <h2 id="routeModalLabel" class="m-0 font-display font-medium text-2xl leading-snug max-w-[24ch]">
                {{ __('diwan.route.title') }}
            </h2>
            <button type="button" data-close-dialog aria-label="{{ __('diwan.nav.close') }}"
                    class="shrink-0 inline-flex items-center justify-center size-10 -me-2 p-0 border-0 bg-transparent text-ink cursor-pointer hover:text-accent-ink">
                <x-icon name="x-lg" class="size-4" />
            </button>
        </div>
        <p class="m-0 text-sm leading-body text-muted">{{ __('diwan.route.body') }}</p>

        {{-- The narrowest door on the way, the room's own included. --}}
        <fieldset class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
            <svg viewBox="0 0 72 96" class="{{ $svg }}" aria-hidden="true">
                <rect x="14" y="8" width="44" height="80" fill="none" stroke="currentColor" stroke-width="2" />
                <circle cx="50" cy="50" r="2" fill="currentColor" />
                <path d="M14 93 H58 M14 90 v6 M58 90 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M65 8 V88 M62 8 h6 M62 88 h6" class="stroke-brass" stroke-width="1.5" fill="none" />
            </svg>
            <div>
                <legend class="{{ $legend }}">{{ __('diwan.route.door') }}</legend>
                <p class="{{ $hint }}">{{ __('diwan.route.door_hint') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.width') }}</span>
                        <input type="text" name="door_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('door_w', 'door', 'w') }}" class="{{ $fieldFor('door_w') }}" @if($bad('door_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.height') }}</span>
                        <input type="text" name="door_h" inputmode="numeric" data-digits dir="ltr" value="{{ $value('door_h', 'door', 'h') }}" class="{{ $fieldFor('door_h') }}" @if($bad('door_h')) aria-invalid="true" @endif></label>
                </div>
            </div>
        </fieldset>

        {{-- How it gets up. --}}
        <fieldset class="pt-5 mt-5 border-0 border-t border-rule mx-0 px-0 pb-0">
            <legend class="{{ $legend }} float-start w-full">{{ __('diwan.route.access') }}</legend>
            <div class="clear-both grid grid-cols-3 gap-2">
                @foreach(['ground', 'lift', 'stairs'] as $way)
                    <label class="flex items-center justify-center gap-2 min-h-11 px-3 border border-rule-2 rounded-control text-sm cursor-pointer transition-colors hover:border-ink has-checked:border-ink has-checked:bg-ink has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus">
                        <input type="radio" name="access" value="{{ $way }}" @checked($access === $way) class="sr-only">
                        {{ __('diwan.route.access_'.$way) }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- The lift: its door, then inside the car. --}}
        <fieldset x-show="byLift" class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
            <svg viewBox="0 0 72 96" class="{{ $svg }}" aria-hidden="true">
                <path d="M10 20 H62 V84 H10 V20 M24 84 v-4 M48 84 v-4" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M24 90 H48 M24 87 v6 M48 87 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M10 12 H62 M10 9 v6 M62 9 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M4 20 V84 M1 20 h6 M1 84 h6" class="stroke-brass" stroke-width="1.5" fill="none" />
            </svg>
            <div>
                <legend class="{{ $legend }}">{{ __('diwan.route.lift') }}</legend>
                <p class="{{ $hint }}">{{ __('diwan.route.lift_hint') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.lift_door_w') }}</span>
                        <input type="text" name="lift_door_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('lift_door_w', 'lift', 'door_w') }}" class="{{ $fieldFor('lift_door_w') }}" @if($bad('lift_door_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.lift_door_h') }}</span>
                        <input type="text" name="lift_door_h" inputmode="numeric" data-digits dir="ltr" value="{{ $value('lift_door_h', 'lift', 'door_h') }}" class="{{ $fieldFor('lift_door_h') }}" @if($bad('lift_door_h')) aria-invalid="true" @endif></label>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.inside_w') }}</span>
                        <input type="text" name="lift_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('lift_w', 'lift', 'w') }}" class="{{ $fieldFor('lift_w') }}" @if($bad('lift_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.inside_d') }}</span>
                        <input type="text" name="lift_d" inputmode="numeric" data-digits dir="ltr" value="{{ $value('lift_d', 'lift', 'd') }}" class="{{ $fieldFor('lift_d') }}" @if($bad('lift_d')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.inside_h') }}</span>
                        <input type="text" name="lift_h" inputmode="numeric" data-digits dir="ltr" value="{{ $value('lift_h', 'lift', 'h') }}" class="{{ $fieldFor('lift_h') }}" @if($bad('lift_h')) aria-invalid="true" @endif></label>
                </div>
            </div>
        </fieldset>

        {{-- The stairs: the flight, and the landing where it turns. --}}
        <fieldset x-show="byStairs" x-cloak class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
            <svg viewBox="0 0 72 96" class="{{ $svg }}" aria-hidden="true">
                <path d="M12 90 V38 H60 V12" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M34 90 V60 H60" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M12 82 H34 M12 74 H34 M12 66 H34 M40 38 V60 M48 38 V60" fill="none" stroke="currentColor" stroke-width="1" />
                <path d="M12 94 H34 M12 91 v6 M34 91 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M66 38 V60 M63 38 h6 M63 60 h6" class="stroke-brass" stroke-width="1.5" fill="none" />
            </svg>
            <div>
                <legend class="{{ $legend }}">{{ __('diwan.route.stairs') }}</legend>
                <p class="{{ $hint }}">{{ __('diwan.route.stairs_hint') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.stairs_w') }}</span>
                        <input type="text" name="stairs_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('stairs_w', 'stairs', 'w') }}" class="{{ $fieldFor('stairs_w') }}" @if($bad('stairs_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.stairs_landing') }}</span>
                        <input type="text" name="stairs_landing" inputmode="numeric" data-digits dir="ltr" value="{{ $value('stairs_landing', 'stairs', 'landing') }}" class="{{ $fieldFor('stairs_landing') }}" @if($bad('stairs_landing')) aria-invalid="true" @endif></label>
                </div>
            </div>
        </fieldset>

        {{-- Inside the home: a villa's or a duplex's own stairs up to the room,
             asked whatever the way to the door was, and only if there are some. --}}
        <fieldset class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
            <svg viewBox="0 0 72 96" class="{{ $svg }}" aria-hidden="true">
                <path d="M12 90 V38 H60 V12" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M34 90 V60 H60" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M12 82 H34 M12 74 H34 M12 66 H34 M40 38 V60 M48 38 V60" fill="none" stroke="currentColor" stroke-width="1" />
                <path d="M12 94 H34 M12 91 v6 M34 91 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M66 38 V60 M63 38 h6 M63 60 h6" class="stroke-brass" stroke-width="1.5" fill="none" />
            </svg>
            <div>
                <legend class="{{ $legend }}">{{ __('diwan.route.inner') }} <span class="font-normal text-muted">({{ __('diwan.route.inner_optional') }})</span></legend>
                <p class="{{ $hint }}">{{ __('diwan.route.inner_hint') }}</p>
                <label class="inline-flex items-center gap-2 min-h-10 text-sm cursor-pointer">
                    <input type="checkbox" name="has_inner" value="1" @checked(old('has_inner', ($wayIn['inner'] ?? null) !== null)) class="size-4 accent-ink">
                    {{ __('diwan.route.has_inner') }}
                </label>
                <div x-show="hasInner" x-cloak class="grid grid-cols-2 gap-3 mt-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.stairs_w') }}</span>
                        <input type="text" name="inner_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('inner_w', 'inner', 'w') }}" class="{{ $fieldFor('inner_w') }}" @if($bad('inner_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.stairs_landing') }}</span>
                        <input type="text" name="inner_landing" inputmode="numeric" data-digits dir="ltr" value="{{ $value('inner_landing', 'inner', 'landing') }}" class="{{ $fieldFor('inner_landing') }}" @if($bad('inner_landing')) aria-invalid="true" @endif></label>
                </div>
            </div>
        </fieldset>

        {{-- The balcony, if a hoist can lift it in from outside. --}}
        <fieldset class="{{ $group }} border-x-0 border-b-0 mx-0 px-0 pb-0">
            <svg viewBox="0 0 72 96" class="{{ $svg }}" aria-hidden="true">
                <rect x="8" y="14" width="56" height="70" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M8 62 H64 M18 62 V84 M28 62 V84 M38 62 V84 M48 62 V84 M58 62 V84" fill="none" stroke="currentColor" stroke-width="1" />
                <path d="M8 8 H64 M8 5 v6 M64 5 v6" class="stroke-brass" stroke-width="1.5" fill="none" />
                <path d="M68 14 V84 M65 14 h6 M65 84 h6" class="stroke-brass" stroke-width="1.5" fill="none" />
            </svg>
            <div>
                <legend class="{{ $legend }}">{{ __('diwan.route.balcony') }} <span class="font-normal text-muted">({{ __('diwan.route.optional') }})</span></legend>
                <p class="{{ $hint }}">{{ __('diwan.route.balcony_hint') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <label><span class="{{ $label }}">{{ __('diwan.route.width') }}</span>
                        <input type="text" name="balcony_w" inputmode="numeric" data-digits dir="ltr" value="{{ $value('balcony_w', 'balcony', 'w') }}" class="{{ $fieldFor('balcony_w') }}" @if($bad('balcony_w')) aria-invalid="true" @endif></label>
                    <label><span class="{{ $label }}">{{ __('diwan.route.height') }}</span>
                        <input type="text" name="balcony_h" inputmode="numeric" data-digits dir="ltr" value="{{ $value('balcony_h', 'balcony', 'h') }}" class="{{ $fieldFor('balcony_h') }}" @if($bad('balcony_h')) aria-invalid="true" @endif></label>
                </div>
            </div>
        </fieldset>

        @if($failed)
            <p class="m-0 mt-5 text-sm text-danger" role="alert">{{ __('diwan.route.invalid') }}</p>
        @endif

        <p class="m-0 mt-6 text-xs leading-body text-muted">{{ __('diwan.route.unknown_note') }}</p>

        <x-ui.button type="submit" :block="true" class="mt-4">{{ __('diwan.route.save') }}</x-ui.button>

        @if($wayIn)
            <button type="submit" form="routeClear"
                    class="block w-full mt-2 min-h-11 border-0 bg-transparent text-sm text-muted underline underline-offset-4 cursor-pointer hover:text-ink">
                {{ __('diwan.route.clear') }}
            </button>
        @endif
    </form>

    @if($wayIn)
        <form id="routeClear" method="POST" action="{{ route('room.route.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</dialog>
