{{--
  Layer 4 of the fit system, and the page the saved list never had — there was
  only a dropdown returning JSON.

  A room is not a folder. It states what its pieces occupy together and what
  share of its own floor that is, which is the whole point: a sofa that fits and
  a table that fits can still not fit together.

  An unmeasured room says so instead of printing a total it cannot compute.

  The loop variable is $space, not $room: $room is the visitor's own room,
  shared with every view, and overwriting it here leaked into the layout.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.rooms.title'))

@php
    $sectionClass = 'py-12 border-0 border-b border-rule';
    $grid = 'grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-12 lg:gap-x-6';
@endphp

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.rooms.title')" :lede="__('diwan.rooms.intro')">
            <x-slot:aside>
                @if($total > 0)
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $total) }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        @if($total === 0 && $rooms->isEmpty())
            <x-ui.empty :title="__('diwan.rooms.empty')" class="mt-10">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.rooms.browse') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>
        @endif

        {{-- ══ Each room, with what it adds up to ══════════════════════════ --}}
        @foreach($rooms as $space)
            @php $totals = $space->totals(); $share = $totals['share']; @endphp

            {{-- Opens itself when its own form was refused. It looked for an
                 old('room_id') the form never sent, so it never did. --}}
            @php
                $bag = 'room'.$space->id;   // RoomBoardController::bagFor()
                $mine = old('room_form') === $bag;
            @endphp
            <section class="{{ $sectionClass }}" x-data="disclosure" @if($errors->getBag($bag)->any()) data-open @endif>
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
                    <div>
                        <h2 class="m-0 font-display text-3xl">{{ $space->name }}</h2>
                        <p class="m-0 mt-2 text-sm text-muted">
                            @if($space->isMeasured())
                                <span class="font-mono" dir="ltr">{{ $space->width_cm }}&times;{{ $space->length_cm }} {{ __('diwan.room.unit') }}</span>
                                · {{ __('diwan.rooms.floor') }}
                                <span class="font-mono" dir="ltr">{{ number_format($space->floorArea(), 1) }} m&sup2;</span>
                            @else
                                {{ __('diwan.rooms.unmeasured') }}
                            @endif
                        </p>
                    </div>

                    <button type="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="editRoom{{ $space->id }}"
                            class="inline-flex items-center gap-2 h-10 px-4 border border-rule-2 bg-transparent text-sm text-ink cursor-pointer hover:border-ink">
                        <x-icon name="pencil" class="size-3.5" />
                        {{ __('diwan.rooms.edit') }}
                    </button>
                </div>

                {{-- Edit / delete, folded away until asked for. --}}
                <div id="editRoom{{ $space->id }}" x-show="open" x-cloak class="mb-8 p-6 bg-paper-2">
                    <form method="POST" action="{{ route('rooms.update', $space) }}" class="grid gap-6 sm:grid-cols-[2fr_1fr_1fr_auto] sm:items-end">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="room_form" value="{{ $bag }}">
                        <x-ui.field name="name" :label="__('diwan.rooms.name')" :value="$mine ? old('name') : $space->name" :bag="$bag"
                                    required maxlength="60" :id="'room-name-'.$space->id" />
                        <x-ui.field name="width_cm" type="number" :label="__('diwan.room.width')" :value="$mine ? old('width_cm') : $space->width_cm" :bag="$bag"
                                    min="100" max="2000" inputmode="numeric" dir="ltr" :id="'room-width-'.$space->id" />
                        <x-ui.field name="length_cm" type="number" :label="__('diwan.room.length')" :value="$mine ? old('length_cm') : $space->length_cm" :bag="$bag"
                                    min="100" max="2000" inputmode="numeric" dir="ltr" :id="'room-length-'.$space->id" />
                        <x-ui.button type="submit">{{ __('diwan.profile.save') }}</x-ui.button>
                    </form>

                    <form method="POST" action="{{ route('rooms.destroy', $space) }}" class="flex flex-wrap items-center gap-4 mt-6 pt-6 border-0 border-t border-rule">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 p-0 border-0 bg-transparent text-sm text-danger cursor-pointer hover:underline">
                            <x-icon name="trash" class="size-3.5" />{{ __('diwan.rooms.delete') }}
                        </button>
                        <span class="text-xs text-muted">{{ __('diwan.rooms.delete_note') }}</span>
                    </form>
                </div>

                {{-- The room's verdict on itself. --}}
                @if($space->items->isNotEmpty())
                    <x-ui.note :tone="$share !== null && $share > 0.5 ? 'warn' : 'info'" icon="rulers" class="mb-8">
                        @if($share !== null)
                            {{ __('diwan.rooms.occupies', ['count' => $space->items->count(), 'area' => number_format($totals['footprint'], 2), 'percent' => round($share * 100)]) }}
                            @if($share > 0.5) {{ __('diwan.rooms.crowded') }} @endif
                        @else
                            {{ __('diwan.rooms.occupies_bare', ['count' => $space->items->count(), 'area' => number_format($totals['footprint'], 2)]) }}
                            {{ __('diwan.rooms.unmeasured_note') }}
                        @endif

                        @if($totals['unknown'] > 0)
                            <span class="block mt-1 text-xs opacity-80">{{ __('diwan.rooms.missing', ['count' => $totals['unknown']]) }}</span>
                        @endif
                    </x-ui.note>

                    <div class="{{ $grid }}">
                        @foreach($space->items as $i => $item)
                            @if($item->product)
                                <div>
                                    <x-product-card :product="$item->product" :room="$space->dimensions()" :i="$i" />
                                    <x-room-picker :item="$item" :rooms="$rooms" />
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="m-0 text-sm text-muted">{{ __('diwan.rooms.empty_room') }}</p>
                @endif
            </section>
        @endforeach

        {{-- ══ Saved but not filed anywhere ═══════════════════════════════ --}}
        @if($unassigned->isNotEmpty())
            <section class="{{ $sectionClass }}">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="m-0 font-display text-3xl">{{ __('diwan.rooms.unassigned') }}</h2>
                        <p class="m-0 mt-2 text-sm text-muted">{{ __('diwan.rooms.unassigned_note') }}</p>
                    </div>
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $unassigned->count()) }}</span>
                </div>

                <div class="{{ $grid }}">
                    @foreach($unassigned as $i => $item)
                        @if($item->product)
                            <div>
                                <x-product-card :product="$item->product" :room="$room" :i="$i" />
                                <x-room-picker :item="$item" :rooms="$rooms" />
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ══ New room ════════════════════════════════════════════════════ --}}
        <section class="pt-12">
            <h2 class="m-0 font-display text-2xl">{{ __('diwan.rooms.new') }}</h2>
            <p class="m-0 mt-2 max-w-measure text-sm text-muted">{{ __('diwan.rooms.new_note') }}</p>

            <form method="POST" action="{{ route('rooms.store') }}" class="grid gap-6 mt-8 max-w-3xl sm:grid-cols-[2fr_1fr_1fr_auto] sm:items-end">
                @csrf
                @php $isNew = old('room_form') === 'newRoom'; @endphp
                <input type="hidden" name="room_form" value="newRoom">
                <x-ui.field name="name" :label="__('diwan.rooms.name')" :value="$isNew ? old('name') : null" bag="newRoom" required maxlength="60"
                            :placeholder="__('diwan.lookbook.scene_living')" id="new-room-name" />
                <x-ui.field name="width_cm" type="number" :label="__('diwan.room.width')" :value="$isNew ? old('width_cm') : null" bag="newRoom"
                            min="100" max="2000" inputmode="numeric" dir="ltr" placeholder="350" id="new-room-width" />
                <x-ui.field name="length_cm" type="number" :label="__('diwan.room.length')" :value="$isNew ? old('length_cm') : null" bag="newRoom"
                            min="100" max="2000" inputmode="numeric" dir="ltr" placeholder="480" id="new-room-length" />
                <x-ui.button type="submit" aria-label="{{ __('diwan.rooms.new') }}">
                    <x-icon name="plus-lg" class="size-4" />
                </x-ui.button>
            </form>
        </section>
    </x-ui.page>
@endsection
