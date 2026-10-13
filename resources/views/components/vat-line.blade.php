{{--
  "Of which VAT (15%)": the tax inside a total, never added to it. Prices in
  the shop already include VAT, so this line explains the total rather than
  changing it.

  For the bag and the checkout, give the amount and the current rate is
  used. For a placed order, give the rate and tax it was placed under; an
  order from before the shop counted VAT has none, and shows no line.

  @param  float       $amount  the VAT-inclusive total
  @param  float|null  $rate    an order's own rate; omit for today's
  @param  float|null  $vat     an order's own tax; omit to work it out
  @param  bool        $order   true when reading a placed order's figures
--}}
@props(['amount' => 0, 'rate' => null, 'vat' => null, 'order' => false])

@php
    use App\Support\Money;

    $show = ! $order || $rate !== null;
    $tax = $vat ?? Money::vatIn($amount, $rate);
@endphp

@if($show)
    <div {{ $attributes->merge(['class' => 'flex justify-between gap-4 text-xs text-muted']) }}>
        <span>{{ __('diwan.money.vat_line', ['rate' => Money::rate($rate)]) }}</span>
        <span class="font-mono">@money($tax)</span>
    </div>
@endif
