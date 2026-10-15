{{--
  The shop's own facts and policies. Three groups, each with what the
  storefront does with it now: the invoice (a tax invoice only with the
  seller's name and VAT number), returns (a request button only with a
  return period), deliveries (a date to choose only with the days the shop
  delivers). Every field may stay empty; empty means "agreed on contact".

  @param  \App\Models\ShopSetting  $settings
--}}
@php
    $group = 'grid gap-6 pt-8 mt-8 border-0 border-t border-rule first-of-type:pt-0 first-of-type:mt-0 first-of-type:border-t-0';
    $title = 'm-0 font-display text-xl';
    $status = fn (bool $on, string $key) => [
        'tone' => $on ? 'ok' : 'neutral',
        'text' => __('admin.settings.status.'.$key.'.'.($on ? 'on' : 'off')),
    ];
    $invoice = $status($settings->issuesTaxInvoices(), 'invoice');
    $returns = $status($settings->return_days !== null, 'returns');
    $delivery = $status($settings->booksDeliveries(), 'delivery');
    $days = old('delivery_weekdays', $settings->delivery_weekdays ?? []);
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.settings.title') }}</x-slot>

    <x-admin.head :title="__('admin.settings.title')" :intro="__('admin.settings.intro')" />

    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl" x-data="submitOnce" @submit="lock">
        @csrf
        @method('PUT')

        <section class="{{ $group }}" aria-labelledby="invoiceTitle">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="invoiceTitle" class="{{ $title }}">{{ __('admin.settings.invoice') }}</h2>
                <x-ui.chip :tone="$invoice['tone']">{{ $invoice['text'] }}</x-ui.chip>
            </div>
            <p class="m-0 -mt-3 text-sm text-muted">{{ __('admin.settings.invoice_hint') }}</p>
            <x-ui.field name="legal_name" :label="__('admin.settings.legal_name')" :value="old('legal_name', $settings->legal_name)" maxlength="160" dir="auto" />
            <x-ui.field name="vat_number" :label="__('admin.settings.vat_number')" :value="old('vat_number', $settings->vat_number)"
                        :hint="__('admin.settings.vat_number_hint')" inputmode="numeric" data-digits maxlength="15" dir="ltr" />
        </section>

        <section class="{{ $group }}" aria-labelledby="returnsTitle">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="returnsTitle" class="{{ $title }}">{{ __('admin.settings.returns') }}</h2>
                <x-ui.chip :tone="$returns['tone']">{{ $returns['text'] }}</x-ui.chip>
            </div>
            <x-ui.field name="return_days" type="number" :label="__('admin.settings.return_days')" :value="old('return_days', $settings->return_days)"
                        :hint="__('admin.settings.return_days_hint')" min="1" max="365" inputmode="numeric" dir="ltr" />
        </section>

        <section class="{{ $group }}" aria-labelledby="deliveryTitle">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="deliveryTitle" class="{{ $title }}">{{ __('admin.settings.delivery') }}</h2>
                <x-ui.chip :tone="$delivery['tone']">{{ $delivery['text'] }}</x-ui.chip>
            </div>
            <p class="m-0 -mt-3 text-sm text-muted">{{ __('admin.settings.delivery_hint') }}</p>
            <x-ui.field name="delivery_lead_days" type="number" :label="__('admin.settings.lead_days')" :value="old('delivery_lead_days', $settings->delivery_lead_days)"
                        :hint="__('admin.settings.lead_days_hint')" min="0" max="60" inputmode="numeric" dir="ltr" />

            <fieldset class="m-0 p-0 border-0">
                <legend class="block mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.settings.weekdays') }}</legend>
                <div class="flex flex-wrap gap-2">
                    {{-- Saturday first: the Saudi week. --}}
                    @foreach([6, 0, 1, 2, 3, 4, 5] as $day)
                        <label class="inline-flex items-center gap-2 min-h-10 px-3.5 border border-rule-2 rounded-pill text-sm cursor-pointer transition-colors hover:border-ink has-checked:border-ink has-checked:bg-ink has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus">
                            <input type="checkbox" name="delivery_weekdays[]" value="{{ $day }}" @checked(in_array($day, array_map('intval', (array) $days), true)) class="sr-only">
                            {{ \Illuminate\Support\Carbon::now()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY)->addDays($day)->translatedFormat('l') }}
                        </label>
                    @endforeach
                </div>
                @error('delivery_weekdays')<p class="m-0 mt-2 text-sm text-danger">{{ $message }}</p>@enderror
            </fieldset>
        </section>

        <div class="flex flex-wrap gap-3 mt-10">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.save') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
