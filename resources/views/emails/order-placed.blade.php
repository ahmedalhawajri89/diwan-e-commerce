{{--
  The order receipt.

  Reads the snapshot columns rather than the live product, so a piece that is
  later renamed or delisted does not rewrite a receipt somebody already has.
  That is the same reason `order_items` carries those columns at all.

  @param  \App\Models\Order  $order
--}}
@php
    $rtl = (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';
    $dir = $rtl ? 'rtl' : 'ltr';
    $end = $rtl ? 'left' : 'right';
    $start = $rtl ? 'right' : 'left';
@endphp
<x-emails.layout :preview="__('diwan.mail.placed.preview', ['number' => $order->reference()])">

    <p style="margin:0 0 16px;">
        {{ filled($order->user?->name) ? __('diwan.mail.greeting', ['name' => $order->user->name]) : __('diwan.mail.greeting_plain') }}
    </p>

    <p style="margin:0 0 24px;">
        {{ __('diwan.mail.placed.body') }}
    </p>

    <table role="presentation" dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:#efe9df; border:1px solid #d6d0c9; border-radius:6px; margin:0 0 24px;">
        <tr>
            <td style="padding:14px 16px; font-size:13px; color:#625952;">
                {{ __('diwan.orders.number') }}
            </td>
            <td align="{{ $end }}"
                style="padding:14px 16px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace;
                       font-size:14px; font-weight:600; color:#20160f;">
                {{ $order->number() }}
            </td>
        </tr>
        <tr>
            <td style="padding:0 16px 14px; font-size:13px; color:#625952;">
                {{ __('diwan.orders.placed') }}
            </td>
            <td align="{{ $end }}"
                style="padding:0 16px 14px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace;
                       font-size:14px; color:#20160f;">
                {{ $order->created_at?->translatedFormat('j M Y') }}
            </td>
        </tr>
    </table>

    <table role="presentation" dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;">
        @foreach($order->items as $item)
            <tr>
                <td style="padding:10px 0; border-bottom:1px solid #d6d0c9; font-size:14px; color:#20160f;">
                    {{ $item->product_name_snapshot }}
                    <span style="color:#625952; font-family:'SFMono-Regular',Consolas,monospace; font-size:13px;">
                        <bdi dir="ltr">&times;{{ $item->quantity }}</bdi>
                    </span>
                </td>
                <td align="{{ $end }}"
                    style="padding:10px 0; border-bottom:1px solid #d6d0c9; white-space:nowrap;
                           font-family:'SFMono-Regular',Consolas,monospace; font-size:14px; color:#20160f;">
                    @money($item->price * $item->quantity)
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:14px 0 0; font-size:15px; font-weight:600; color:#20160f;">
                {{ __('diwan.bag.grand') }}
            </td>
            <td align="{{ $end }}"
                style="padding:14px 0 0; font-family:'SFMono-Regular',Consolas,monospace;
                       font-size:16px; font-weight:600; color:#20160f;">
                @money($order->total_price)
            </td>
        </tr>
    </table>

    {{-- Nothing has been paid, and the email says so rather than implying a
         completed transaction. Same rule as the checkout page it followed. --}}
    <p style="margin:20px 0 24px; font-size:13px; color:#625952;">
        {{ __('diwan.mail.placed.payment_note') }}
    </p>

    @if($order->address)
        <p style="margin:0 0 4px; font-size:12px; {{ $rtl ? '' : 'letter-spacing:0.09em; text-transform:uppercase;' }} color:#625952;">
            {{ __('diwan.mail.placed.ship_to') }}
        </p>
        <p dir="auto" style="margin:0 0 24px; font-size:14px; color:#20160f; text-align:{{ $start }};">
            {{ $order->address->street }}<br>
            {{ $order->address->city }}{{ $rtl ? '، ' : ', ' }}{{ $order->address->country }}
        </p>
    @endif

    <a href="{{ route('orders.show', $order) }}"
       style="display:inline-block; background:#20160f; color:#f7f3ec; text-decoration:none;
              padding:12px 24px; border-radius:4px; font-size:15px; font-weight:500;">
        {{ __('diwan.mail.placed.cta') }}
    </a>

</x-emails.layout>
