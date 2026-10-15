{{--
  Contact: the one piece of help people actually ask for — how to measure a
  room — beside the form itself.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.contact.title'))
@section('description', __('diwan.contact.body'))

@section('content')
    <x-ui.page class="grid gap-14 py-16 lg:py-24 lg:grid-cols-[5fr_6fr] lg:gap-24 pb-(--section-gap)">
        <div>
            <h1 class="m-0 font-display font-medium rtl:font-semibold text-display-s leading-tight tracking-display">{{ __('diwan.contact.lede') }}</h1>
            <p class="m-0 mt-5 max-w-[44ch] leading-body text-muted">{{ __('diwan.contact.body') }}</p>

            <div class="mt-12 p-6 bg-deep text-on-deep">
                <p class="flex items-center gap-2 m-0 font-label text-xs font-medium uppercase tracking-label text-brass">
                    <x-icon name="rulers" class="size-4" />{{ __('diwan.contact.measure') }}
                </p>
                <p class="m-0 mt-3 text-sm leading-body text-on-deep-muted">{{ __('diwan.contact.measure_b') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('contact.store') }}" x-data="submitOnce" @submit="lock" class="grid gap-8 content-start">
            @csrf

            <div class="grid gap-8 sm:grid-cols-2">
                <x-ui.field name="name" :label="__('diwan.contact.name')" :value="old('name', auth()->user()?->name)"
                            required maxlength="255" autocomplete="name" />
                <x-ui.field name="email" type="email" :label="__('diwan.contact.email')" :value="old('email', auth()->user()?->email)"
                            required maxlength="255" autocomplete="email" dir="ltr" />
            </div>

            <x-ui.field name="subject" :label="__('diwan.contact.subject').' ('.__('diwan.common.optional').')'" :value="old('subject')" maxlength="255" />

            <x-ui.field name="message" type="textarea" :label="__('diwan.contact.message')" :value="old('message')" required />

            <div>
                <x-ui.button type="submit" ::disabled="busy">
                    <span x-show="idle">{{ __('diwan.contact.send') }}</span>
                    <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.page>
@endsection
