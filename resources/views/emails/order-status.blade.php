{{--
  An order moved. Three statuses reach here — shipped, delivered, cancelled —
  and each supplies its own sentence rather than sharing a generic one with the
  status name substituted in. "Your order is now Delivered" is a database row
  read aloud, not a message to a person.

  @param  \App\Models\Order  $order
  @param  string             $status  one of OrderStatusChanged::NOTIFIABLE
--}}
<x-emails.layout :preview="__('diwan.mail.status.' . $status . '.preview', ['number' => $order->id])">

    <p style="margin:0 0 16px;">
        {{ __('diwan.mail.greeting', ['name' => $order->user?->name]) }}
    </p>

    <p style="margin:0 0 24px;">
        {{ __('diwan.mail.status.' . $status . '.body', ['number' => $order->id]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:#f2eee9; border:1px solid #dbd7d2; border-radius:6px; margin:0 0 24px;">
        <tr>
            <td style="padding:14px 16px; font-size:13px; color:#5d5751;">
                {{ __('diwan.orders.number') }}
            </td>
            <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                style="padding:14px 16px; font-family:'SFMono-Regular',Consolas,monospace;
                       font-size:14px; font-weight:600; color:#1a120b;">
                #{{ $order->id }}
            </td>
        </tr>
        <tr>
            <td style="padding:0 16px 14px; font-size:13px; color:#5d5751;">
                {{ __('diwan.orders.status') }}
            </td>
            <td align="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl' ? 'left' : 'right' }}"
                style="padding:0 16px 14px; font-size:14px; font-weight:600; color:#1a120b;">
                {{ __('diwan.orders.status_values.' . $status) }}
            </td>
        </tr>
    </table>

    <a href="{{ route('orders.show', $order) }}"
       style="display:inline-block; background:#02519f; color:#f9f6f2; text-decoration:none;
              padding:12px 24px; border-radius:4px; font-size:15px; font-weight:500;">
        {{ __('diwan.mail.status.cta') }}
    </a>

</x-emails.layout>
