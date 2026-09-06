{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 6/6 · right = form · divider = negative space
  The evidence side is the form itself; the claim side carries the one piece of
  help people actually ask for — how to measure a room.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.contact.title'))
@section('description', __('diwan.contact.body'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-rise align-items-start">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title">{{ __('diwan.contact.lede') }}</h1>
                    <p class="hm-dip__body">{{ __('diwan.contact.body') }}</p>

                    <div class="hm-note hm-note--info hm-measure">
                        <x-icon name="rulers" />
                        <span>
                            <strong>{{ __('diwan.contact.measure') }}</strong><br>
                            {{ __('diwan.contact.measure_b') }}
                        </span>
                    </div>
                </div>

                <div class="hm-dip__proof">
                    <form method="POST" action="{{ route('contact.store') }}" class="hm-panel">
                        @csrf

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="hm-field @error('name') hm-field--error @enderror hm-mb-0">
                                    <span class="hm-field__label">{{ __('diwan.contact.name') }}</span>
                                    <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}"
                                           required maxlength="255" autocomplete="name">
                                    @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label class="hm-field @error('email') hm-field--error @enderror hm-mb-0">
                                    <span class="hm-field__label">{{ __('diwan.contact.email') }}</span>
                                    <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                                           required maxlength="255" autocomplete="email" dir="ltr">
                                    @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-12">
                                <label class="hm-field @error('subject') hm-field--error @enderror hm-mb-0">
                                    <span class="hm-field__label">
                                        {{ __('diwan.contact.subject') }}
                                        <span class="hm-muted">({{ __('diwan.common.optional') }})</span>
                                    </span>
                                    <input type="text" name="subject" value="{{ old('subject') }}" maxlength="255">
                                    @error('subject')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-12">
                                <label class="hm-field @error('message') hm-field--error @enderror hm-mb-0">
                                    <span class="hm-field__label">{{ __('diwan.contact.message') }}</span>
                                    <textarea name="message" required>{{ old('message') }}</textarea>
                                    @error('message')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="hm-btn hm-btn--primary hm-btn--block hm-mt-lg">
                            {{ __('diwan.contact.send') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
