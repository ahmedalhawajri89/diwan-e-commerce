{{--
  The order receipt.

  Reads the snapshot columns rather than the live product, so a piece that is
  later renamed or delisted does not rewrite a receipt somebody already has.
  That is the same reason `order_items` carries those columns at all.

  @param  \App\Models\Order  $order
--}}
<x-emails.layout :preview="__('diwan.mail.placed.preview', ['number' => $order->reference()])">

    <p style="margin:0 0 16px;">
        {{ __('diwan.mail.greeting', ['name' => $order->user?->name]) }}
    </p>

    <p style="margin:0 0 24px;">
        {{ __('diwan.mail.placed.body') }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:#f2eee9; border:1px solid #dbd7d2; border-radius:6px; margin:0 0 24px;">
        <tr>
            <td style="padding:14px 16px; font-size:13px; color:#5d5751;">
                {{ __('diwan.orders.number') }}
            </td>
            <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                style="padding:14px 16px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace;
                       font-size:14px; font-weight:600; color:#1a120b;">
                {{ $order->number() }}
            </td>
        </tr>
        <tr>
            <td style="padding:0 16px 14px; font-size:13px; color:#5d5751;">
                {{ __('diwan.orders.placed') }}
            </td>
            <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                style="padding:0 16px 14px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace;
                       font-size:14px; color:#1a120b;">
                {{ $order->created_at?->translatedFormat('j M Y') }}
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;">
        @foreach($order->items as $item)
            <tr>
                <td style="padding:10px 0; border-bottom:1px solid #dbd7d2; font-size:14px; color:#1a120b;">
                    {{ $item->product_name_snapshot }}
                    <span style="color:#5d5751; font-family:'SFMono-Regular',Consolas,monospace; font-size:13px;">
                        &times;{{ $item->quantity }}
                    </span>
                </td>
                <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                    style="padding:10px 0; border-bottom:1px solid #dbd7d2; white-space:nowrap;
                           font-family:'SFMono-Regular',Consolas,monospace; font-size:14px; color:#1a120b;">
                    ${{ number_format($item->price * $item->quantity, 2) }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:14px 0 0; font-size:15px; font-weight:600; color:#1a120b;">
                {{ __('diwan.bag.grand') }}
            </td>
            <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                style="padding:14px 0 0; font-family:'SFMono-Regular',Consolas,monospace;
                       font-size:16px; font-weight:600; color:#1a120b;">
                ${{ number_format($order->total_price, 2) }}
            </td>
        </tr>
    </table>

    {{-- Nothing has been paid, and the email says so rather than implying a
         completed transaction. Same rule as the checkout page it followed. --}}
    <p style="margin:20px 0 24px; font-size:13px; color:#5d5751;">
        {{ __('diwan.mail.placed.payment_note') }}
    </p>

    @if($order->address)
        <p style="margin:0 0 4px; font-size:12px; letter-spacing:0.09em; text-transform:uppercase; color:#5d5751;">
            {{ __('diwan.mail.placed.ship_to') }}
        </p>
        <p style="margin:0 0 24px; font-size:14px; color:#1a120b;">
            {{ $order->address->street }}<br>
            {{ $order->address->city }}, {{ $order->address->country }}
        </p>
    @endif

    <a href="{{ route('orders.show', $order) }}"
       style="display:inline-block; background:#02519f; color:#f9f6f2; text-decoration:none;
              padding:12px 24px; border-radius:4px; font-size:15px; font-weight:500;">
        {{ __('diwan.mail.placed.cta') }}
    </a>

</x-emails.layout>
