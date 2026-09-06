{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  Layer 4 of the fit system, and the page the saved list never had — there was
  only a dropdown returning JSON.

  A room is not a folder. It states what its pieces occupy together and what
  share of its own floor that is, which is the whole point: a sofa that fits and
  a table that fits can still not fit together.

  An unmeasured room says so instead of printing a total it cannot compute.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.rooms.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title">{{ __('diwan.rooms.title') }}</h1>
                    <p class="hm-app__intro">{{ __('diwan.rooms.intro') }}</p>
                </div>
                @if($total > 0)
                    <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', $total) }}</span>
                @endif
            </div>

            @if($total === 0 && $rooms->isEmpty())
                <div class="hm-empty">
                    <p>{{ __('diwan.rooms.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.rooms.browse') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @endif

            {{-- ══ Each room, with what it adds up to ═══════════════════ --}}
            @foreach($rooms as $room)
                @php $totals = $room->totals(); @endphp

                <section class="hm-listrow hm-listrow--xl hm-listrow--top">
                    <div class="hm-head">
                        <div>
                            <h2 class="hm-head__title hm-t-xl">{{ $room->name }}</h2>
                            <p class="hm-muted mb-0 hm-t-sm">
                                @if($room->isMeasured())
                                    <span class="hm-num">{{ $room->width_cm }}&times;{{ $room->length_cm }}</span>
                                    {{ __('diwan.room.unit') }} ·
                                    {{ __('diwan.rooms.floor') }}
                                    <span class="hm-num">{{ number_format($room->floorArea(), 1) }} m&sup2;</span>
                                @else
                                    {{ __('diwan.rooms.unmeasured') }}
                                @endif
                            </p>
                        </div>

                        <div class="hm-head__aside d-flex gap-2">
                            <button type="button" class="hm-btn hm-btn--quiet"
                                    data-bs-toggle="collapse" data-bs-target="#editRoom{{ $room->id }}"
                                    aria-expanded="false" aria-controls="editRoom{{ $room->id }}">
                                <x-icon name="pencil" />
                                {{ __('diwan.rooms.edit') }}
                            </button>
                        </div>
                    </div>

                    {{-- Edit / delete, folded away until asked for. --}}
                    <div class="collapse" id="editRoom{{ $room->id }}">
                        <div class="hm-panel hm-mb-lg">
                            <form method="POST" action="{{ route('rooms.update', $room) }}" class="row g-3 align-items-end">
                                @csrf
                                @method('PUT')

                                <div class="col-md-5">
                                    <label class="hm-field hm-m-0">
                                        <span class="hm-field__label">{{ __('diwan.rooms.name') }}</span>
                                        <input type="text" name="name" value="{{ old('name', $room->name) }}"
                                               required maxlength="60">
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label class="hm-field hm-m-0">
                                        <span class="hm-field__label">{{ __('diwan.room.width') }}</span>
                                        <input type="number" name="width_cm" value="{{ old('width_cm', $room->width_cm) }}"
                                               min="100" max="2000" inputmode="numeric" dir="ltr">
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label class="hm-field hm-m-0">
                                        <span class="hm-field__label">{{ __('diwan.room.length') }}</span>
                                        <input type="number" name="length_cm" value="{{ old('length_cm', $room->length_cm) }}"
                                               min="100" max="2000" inputmode="numeric" dir="ltr">
                                    </label>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="hm-btn hm-btn--primary hm-btn--block">
                                        {{ __('diwan.profile.save') }}
                                    </button>
                                </div>
                            </form>

                            <form class="hm-mt-md hm-pt-md hm-ruled-t" method="POST" action="{{ route('rooms.destroy', $room) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hm-btn hm-btn--danger">{{ __('diwan.rooms.delete') }}</button>
                                <span class="hm-muted hm-t-xs hm-mis-xs">
                                    {{ __('diwan.rooms.delete_note') }}
                                </span>
                            </form>
                        </div>
                    </div>

                    {{-- The room's verdict on itself. --}}
                    @if($room->items->isNotEmpty())
                        @php $share = $totals['share']; @endphp
                        <p class="hm-note {{ $share !== null && $share > 0.5 ? 'hm-note--warn' : 'hm-note--info' }} hm-mb-lg">
                            <x-icon name="rulers" />
                            <span>
                                @if($share !== null)
                                    {{ __('diwan.rooms.occupies', [
                                        'count'   => $room->items->count(),
                                        'area'    => number_format($totals['footprint'], 2),
                                        'percent' => round($share * 100),
                                    ]) }}
                                    @if($share > 0.5) {{ __('diwan.rooms.crowded') }} @endif
                                @else
                                    {{ __('diwan.rooms.occupies_bare', [
                                        'count' => $room->items->count(),
                                        'area'  => number_format($totals['footprint'], 2),
                                    ]) }}
                                    {{ __('diwan.rooms.unmeasured_note') }}
                                @endif

                                @if($totals['unknown'] > 0)
                                    <span class="hm-muted d-block hm-t-xs">
                                        {{ __('diwan.rooms.missing', ['count' => $totals['unknown']]) }}
                                    </span>
                                @endif
                            </span>
                        </p>
                    @endif

                    @if($room->items->isEmpty())
                        <p class="hm-muted hm-t-sm">{{ __('diwan.rooms.empty_room') }}</p>
                    @else
                        <div class="hm-grid">
                            @foreach($room->items as $i => $item)
                                @if($item->product)
                                    <div>
                                        <x-product-card :product="$item->product" :room="$room->dimensions()" :i="$i" />
                                        <x-room-picker :item="$item" :rooms="$rooms" />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach

            {{-- ══ Saved but not filed anywhere ════════════════════════ --}}
            @if($unassigned->isNotEmpty())
                <section class="hm-listrow hm-listrow--xl hm-listrow--top">
                    <div class="hm-head">
                        <div>
                            <h2 class="hm-head__title hm-t-xl">{{ __('diwan.rooms.unassigned') }}</h2>
                            <p class="hm-muted mb-0 hm-t-sm">{{ __('diwan.rooms.unassigned_note') }}</p>
                        </div>
                        <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', $unassigned->count()) }}</span>
                    </div>

                    <div class="hm-grid">
                        @foreach($unassigned as $i => $item)
                            @if($item->product)
                                <div>
                                    <x-product-card :product="$item->product" :room="$room ?? null" :i="$i" />
                                    <x-room-picker :item="$item" :rooms="$rooms" />
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ══ New room ═══════════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl hm-listrow--top">
                <h2 class="hm-head__title hm-t-lg hm-mb-2xs">
                    {{ __('diwan.rooms.new') }}
                </h2>
                <p class="hm-muted hm-measure hm-t-sm">{{ __('diwan.rooms.new_note') }}</p>

                <form method="POST" action="{{ route('rooms.store') }}" class="row g-3 align-items-end hm-hold-text">
                    @csrf

                    <div class="col-md-5">
                        <label class="hm-field @error('name') hm-field--error @enderror hm-m-0">
                            <span class="hm-field__label">{{ __('diwan.rooms.name') }}</span>
                            <input type="text" name="name" value="{{ old('name') }}" required maxlength="60"
                                   placeholder="{{ __('diwan.lookbook.scene_living') }}">
                            @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-3">
                        <label class="hm-field @error('width_cm') hm-field--error @enderror hm-m-0">
                            <span class="hm-field__label">{{ __('diwan.room.width') }}</span>
                            <input type="number" name="width_cm" value="{{ old('width_cm') }}"
                                   min="100" max="2000" inputmode="numeric" dir="ltr" placeholder="350">
                            @error('width_cm')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-3">
                        <label class="hm-field @error('length_cm') hm-field--error @enderror hm-m-0">
                            <span class="hm-field__label">{{ __('diwan.room.length') }}</span>
                            <input type="number" name="length_cm" value="{{ old('length_cm') }}"
                                   min="100" max="2000" inputmode="numeric" dir="ltr" placeholder="480">
                            @error('length_cm')<span class="hm-field__error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="hm-btn hm-btn--primary hm-btn--block">
                            <x-icon name="plus-lg" />
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </section>

@endsection
