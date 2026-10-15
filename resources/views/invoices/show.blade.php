{{--
  An order's paper, A4, to keep or print: a simplified tax invoice with its
  ZATCA QR code once the sale has happened and the shop has a VAT number, a
  plain order receipt until then, saying why (App\Support\Invoice). In the
  reader's language; the buttons and the background do not print.

  @param  \App\Models\Order  $order    with items, address and user
  @param  array              $invoice  from App\Support\Invoice::for()
  @param  string             $back     where "back" goes (the customer's order, or the panel's)
--}}
@php
    use App\Support\Invoice;
    use App\Support\Money;

    $locales = config('localization.supported');
    $current = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $tax = $invoice['kind'] === Invoice::TAX;
    $label = 'm-0 font-label text-xs font-medium uppercase tracking-label text-muted';
    $address = $order->address;
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('diwan.invoice.'.$invoice['kind']) }} {{ $invoice['number'] }} — {{ __('diwan.brand.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-paper-2 text-ink font-body print:bg-paper">
    @include('partials.icon-sprite')

    <div class="flex justify-center gap-3 py-4 print:hidden" x-data="printPage">
        <button type="button" @click="print"
                class="inline-flex items-center gap-2 h-11 px-5 rounded-control border-0 bg-ink text-paper text-sm cursor-pointer hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            <x-icon name="printer" class="size-4" />{{ __('diwan.invoice.print') }}
        </button>
        <a href="{{ $back }}" class="inline-flex items-center h-11 px-4 text-sm text-ink underline underline-offset-4">{{ __('diwan.invoice.back') }}</a>
    </div>

    <main class="mx-auto mb-10 w-full max-w-[210mm] min-h-[297mm] p-[16mm] bg-paper print:m-0 print:min-h-0 print:p-0">
        {{-- Who issued it, what it is, and — on a tax invoice — the code to check it. --}}
        <header class="flex items-start justify-between gap-8 pb-6 border-0 border-b-2 border-ink">
            <div>
                <p class="m-0 font-display text-2xl">{{ __('diwan.brand.name') }}</p>
                <h1 class="m-0 mt-3 font-display text-3xl leading-tight">{{ __('diwan.invoice.'.$invoice['kind']) }}</h1>
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 m-0 mt-4 text-sm">
                    <dt class="text-muted">{{ __('diwan.invoice.number') }}</dt>
                    <dd class="m-0 font-mono" dir="ltr">{{ $invoice['number'] }}</dd>
                    @if($tax)
                        <dt class="text-muted">{{ __('diwan.invoice.issued') }}</dt>
                        <dd class="m-0 font-mono" dir="ltr">{{ $invoice['issued_at']->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd>
                    @else
                        <dt class="text-muted">{{ __('diwan.invoice.ordered') }}</dt>
                        <dd class="m-0 font-mono" dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</dd>
                    @endif
                </dl>
            </div>

            @if($tax)
                <figure class="m-0 shrink-0 w-36 text-center">
                    <div class="size-36 [&>svg]:size-full" role="img" aria-label="{{ __('diwan.invoice.scan') }}">{!! $invoice['qr'] !!}</div>
                    <figcaption class="mt-1 text-[0.6875rem] leading-snug text-muted">{{ __('diwan.invoice.scan') }}</figcaption>
                </figure>
            @endif
        </header>

        {{-- Seller and buyer. --}}
        <section class="grid grid-cols-2 gap-8 py-6 border-0 border-b border-rule">
            <div>
                <p class="{{ $label }}">{{ __('diwan.invoice.seller') }}</p>
                <p class="m-0 mt-1 font-medium" dir="auto">{{ $invoice['seller'] ?: __('diwan.brand.name') }}</p>
                @if($tax)
                    <p class="m-0 mt-1 text-sm"><span class="text-muted">{{ __('diwan.invoice.vat_number') }}:</span> <bdi dir="ltr" class="font-mono">{{ $invoice['vat_number'] }}</bdi></p>
                @endif
            </div>
            <div>
                <p class="{{ $label }}">{{ __('diwan.invoice.buyer') }}</p>
                <p class="m-0 mt-1 font-medium" dir="auto">{{ $order->user?->name }}</p>
                @if($address)
                    <p class="m-0 mt-1 text-sm leading-body text-neutral" dir="auto">{{ collect([$address->street, $address->city, $address->country])->filter()->join(app()->getLocale() === 'ar' ? '، ' : ', ') }}</p>
                @endif
            </div>
        </section>

        {{-- What was sold. Unit prices include VAT, as the shop shows them. --}}
        <table class="w-full mt-6 border-collapse text-sm">
            <thead>
                <tr class="border-0 border-b border-ink text-muted">
                    <th scope="col" class="py-2 pe-3 text-start font-medium">{{ __('diwan.invoice.item') }}</th>
                    <th scope="col" class="py-2 px-3 text-center font-medium">{{ __('diwan.invoice.qty') }}</th>
                    <th scope="col" class="py-2 px-3 text-end font-medium">{{ __('diwan.invoice.unit') }}</th>
                    <th scope="col" class="py-2 ps-3 text-end font-medium">{{ __('diwan.invoice.line') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr class="border-0 border-b border-rule align-top">
                        <td class="py-2.5 pe-3" dir="auto">{{ $item->product_name_snapshot }}</td>
                        <td class="py-2.5 px-3 text-center font-mono">{{ $item->quantity }}</td>
                        <td class="py-2.5 px-3 text-end font-mono whitespace-nowrap">@money($item->price)</td>
                        <td class="py-2.5 ps-3 text-end font-mono whitespace-nowrap">@money($item->price * $item->quantity)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- The totals: before VAT, the VAT, and what was paid. --}}
        <dl class="ms-auto mt-6 w-full max-w-xs grid gap-1.5 m-0 text-sm">
            @if($invoice['vat'] !== null)
                <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('diwan.invoice.net') }}</dt><dd class="m-0 font-mono">@money($invoice['net'])</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('diwan.invoice.vat', ['rate' => Money::rate($order->vat_rate)]) }}</dt><dd class="m-0 font-mono">@money($invoice['vat'])</dd></div>
            @endif
            <div class="flex justify-between gap-4 pt-2 mt-1 border-0 border-t-2 border-ink text-base font-medium">
                <dt>{{ __('diwan.invoice.gross') }}</dt><dd class="m-0 font-mono">@money($order->total_price)</dd>
            </div>
        </dl>

        @unless($tax)
            <p class="m-0 mt-10 p-4 bg-paper-2 text-sm leading-body text-neutral">{{ __('diwan.invoice.why.'.$invoice['reason']) }}</p>
        @endunless
    </main>
</body>
</html>
