{{--
  A row of pieces that moves sideways (resources/js/components/rail.js).

  Nothing moves on its own and nothing loops. The shopper swipes, drags, or
  presses the arrows; the tape under the row shows how much of it is in view
  and how far along they are, a length on a measure rather than a row of
  dots. With every piece in view, the controls do not appear at all.

  Each item in the slot is an <li>. Cards enter one after another as the row
  arrives, and only for visitors who accept motion (data-reveal).

  @param  string  $label  what the row is, for screen readers
--}}
@props(['label'])

<section x-data="rail" {{ $attributes->merge(['class' => 'relative']) }}
         role="region" aria-roledescription="{{ __('diwan.rail.role') }}" aria-label="{{ $label }}">

    <ul x-ref="track" tabindex="0" data-reveal-group
        class="m-0 p-0 list-none flex gap-4 lg:gap-6 overflow-x-auto overscroll-x-contain snap-x snap-mandatory
               [scrollbar-width:none] [&::-webkit-scrollbar]:hidden
               cursor-grab data-dragging:cursor-grabbing data-dragging:snap-none data-dragging:select-none
               focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">
        {{ $slot }}
    </ul>

    {{-- The tape and the arrows. Hidden while everything fits. --}}
    <div class="flex items-center gap-6 mt-8" x-show="scrollable" x-cloak>
        <div x-ref="tape" aria-hidden="true"
             class="relative flex-1 h-px bg-rule [--size:1] [--pos:0] [--dir:1] rtl:[--dir:-1]">
            {{-- The marks: a hairline tick every two rem, like a rule's. --}}
            <span class="absolute inset-x-0 -top-1.5 h-1.5 bg-[repeating-linear-gradient(to_right,var(--color-rule-2)_0_1px,transparent_1px_2rem)] rtl:bg-[repeating-linear-gradient(to_left,var(--color-rule-2)_0_1px,transparent_1px_2rem)]"></span>
            {{-- What is in view: its length is the share shown, its place how far along. --}}
            <span class="absolute inset-x-0 -top-px h-[3px] bg-ink origin-left rtl:origin-right
                         [transform:translateX(calc(var(--pos)*(1-var(--size))*100%*var(--dir)))_scaleX(var(--size))]"></span>
        </div>

        <div class="flex gap-2 shrink-0">
            @php
                $arrow = 'inline-flex items-center justify-center size-11 rounded-pill border border-rule-2 bg-paper text-ink cursor-pointer '
                       . 'transition-colors hover:border-ink disabled:opacity-35 disabled:cursor-default disabled:hover:border-rule-2 '
                       . 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
            @endphp
            <button type="button" class="{{ $arrow }}" @click="prev" :disabled="atStart" aria-label="{{ __('diwan.rail.previous') }}">
                <x-icon name="chevron-left" class="size-4" />
            </button>
            <button type="button" class="{{ $arrow }}" @click="next" :disabled="atEnd" aria-label="{{ __('diwan.rail.next') }}">
                <x-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>
</section>
