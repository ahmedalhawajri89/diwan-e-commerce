{{--
  The sheet that goes in the box: what to pack (a box to tick beside each
  piece), where it goes and who to call, and what the courier collects when
  the order is paid on delivery. A page of its own, A4, in the panel's
  language; the button and the background do not print.
--}}
@php
    $locales = config('localization.supported');
    $current = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $currency = __('diwan.common.currency');
    $money = fn ($v) => $currency.number_format($v, 2);
    $address = $order->address;
    $collect = $order->payment_status !== 'paid';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('admin.slip.title') }} {{ $order->number() }} — {{ __('diwan.brand.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-paper-2 text-ink font-body print:bg-paper">
    @include('partials.icon-sprite')

    <div class="flex justify-center gap-3 py-4 print:hidden" x-data="printPage">
        <button type="button" @click="print"
                class="inline-flex items-center gap-2 h-11 px-5 rounded-control border-0 bg-ink text-paper text-sm cursor-pointer hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            <x-icon name="printer" class="size-4" />{{ __('admin.slip.print') }}
        </button>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center h-11 px-4 text-sm text-ink underline underline-offset-4">{{ __('admin.action.back') }}</a>
    </div>

    <main class="mx-auto mb-10 w-[210mm] max-w-full min-h-[297mm] p-[14mm] bg-paper print:m-0 print:w-auto print:min-h-0 print:p-0">
        {{-- Who it is from, and which order. --}}
        <header class="flex items-start justify-between gap-6 pb-5 border-0 border-b-2 border-ink">
            <div>
                <p class="m-0 font-display font-semibold text-2xl tracking-[0.2em] rtl:tracking-normal">{{ __('diwan.brand.name') }}</p>
                <p class="m-0 mt-1 font-label text-xs uppercase tracking-label text-muted">{{ __('admin.slip.title') }}</p>
            </div>
            <div class="text-end">
                <p class="m-0 font-mono text-3xl leading-none" dir="ltr">{{ $order->number() }}</p>
                <p class="m-0 mt-2 text-xs text-muted">{{ __('admin.slip.placed') }}: {{ $order->created_at->translatedFormat('j F Y') }}</p>
            </div>
        </header>

        {{-- Where it goes, and what to collect. --}}
        <section class="grid grid-cols-2 gap-6 py-6 border-0 border-b border-rule">
            <div>
                <p class="m-0 mb-2 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.slip.ship_to') }}</p>
                <p class="m-0 text-lg font-medium">{{ $order->user?->name ?? __('admin.orders.guest') }}</p>
                @if($address)
                    <address class="not-italic mt-1 text-sm leading-body" dir="auto">{{ $address->street }}<br>{{ $address->city }}<br>{{ $address->country }}</address>
                    @if($address->phone)
                        <p class="m-0 mt-2 font-mono text-lg" dir="ltr">{{ $address->phone }}</p>
                    @endif
                @else
                    <p class="m-0 mt-1 text-sm text-muted">{{ __('admin.orders.no_address') }}</p>
                @endif
            </div>
            <div class="p-4 border-2 {{ $collect ? 'border-ink' : 'border-rule' }}">
                <p class="m-0 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $collect ? __('admin.slip.collect') : __('admin.slip.paid') }}</p>
                <p class="m-0 mt-2 font-mono text-3xl">{{ $money($order->total_price) }}</p>
                <p class="m-0 mt-1 text-xs text-muted">{{ $collect ? __('admin.slip.collect_note') : __('admin.slip.paid_note') }}</p>
            </div>
        </section>

        {{-- What to pack. --}}
        <section class="py-6">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-0 border-b border-ink">
                        <th scope="col" class="w-10 py-2 text-start font-label text-xs font-medium uppercase tracking-label text-muted"><span class="sr-only">{{ __('admin.slip.packed') }}</span></th>
                        <th scope="col" class="py-2 text-start font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.slip.piece') }}</th>
                        <th scope="col" class="py-2 text-start font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.slip.colour') }}</th>
                        <th scope="col" class="w-20 py-2 text-end font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.slip.quantity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="border-0 border-b border-rule">
                            <td class="py-3"><span class="inline-block size-5 border-2 border-ink" aria-hidden="true"></span></td>
                            <td class="py-3 font-medium">{{ $item->product_name_snapshot }}</td>
                            <td class="py-3">{{ $item->variant?->colourLabel() ?? '—' }}</td>
                            <td class="py-3 text-end font-mono text-lg">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="2" class="pt-3 text-xs text-muted">{{ __('admin.slip.pieces') }}</td>
                        <td class="pt-3 text-end font-mono text-lg">{{ $order->items->sum('quantity') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        @if($order->notes)
            <section class="p-4 border border-ink">
                <p class="m-0 mb-1 font-label text-xs font-medium uppercase tracking-label">{{ __('admin.slip.customer_note') }}</p>
                <p class="m-0 text-sm leading-body whitespace-pre-line" dir="auto">{{ $order->notes }}</p>
            </section>
        @endif

        {{-- For whoever packs and whoever carries it. --}}
        <footer class="grid grid-cols-2 gap-10 mt-12 text-xs text-muted">
            <p class="m-0 pt-2 border-0 border-t border-rule-2">{{ __('admin.slip.packed_by') }}</p>
            <p class="m-0 pt-2 border-0 border-t border-rule-2">{{ __('admin.slip.received_by') }}</p>
        </footer>
    </main>
</body>
</html>
