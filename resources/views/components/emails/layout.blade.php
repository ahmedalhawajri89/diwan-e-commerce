{{--
  The shell every transactional email uses.

  Inline CSS and a table layout, because that is what email clients render.
  Outlook still uses Word to lay out HTML; Gmail strips <style> blocks in some
  contexts; nothing here can rely on the stylesheet the site uses.

  The colours and type are hard-coded hex and named families rather than tokens
  for the same reason — this is the second place in the project allowed to do
  that, after errors/minimal, and for the same kind of reason: it has to render
  somewhere the design system cannot reach. Values are copied from resources/css/app.css
  and listed here so a future change can find them:

    paper   #f7f3ec   paper-2 #efe9df   paper-3 #e4ddd3
    rule    #d6d0c9   muted   #625952   ink     #20160f
    accent  #20160f

  Fonts fall back to system faces. A webfont in email is a wasted request at
  best and a blocked one at worst.

  @param  string  $preview  the one-line summary inboxes show beside the subject
--}}
@props(['preview' => ''])

@php
    // Arabic letters join: spacing them apart breaks the word.
    $rtl = (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $preview }}</title>
</head>
<body style="margin:0; padding:0; background:#e4ddd3; -webkit-font-smoothing:antialiased;">

    {{-- Shown in the inbox list next to the subject, and nowhere else. Without
         it clients scrape the first visible text, which here is the wordmark. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preview }}</div>

    <table role="presentation" dir="{{ $rtl ? 'rtl' : 'ltr' }}" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:#e4ddd3; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" dir="{{ $rtl ? 'rtl' : 'ltr' }}" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px; background:#f7f3ec; border:1px solid #d6d0c9; border-radius:6px;">

                    <tr>
                        <td style="padding:28px 28px 0;">
                            <p style="margin:0; font-family:Georgia,'Times New Roman',serif; font-size:20px;
                                      {{ $rtl ? '' : 'letter-spacing:0.06em;' }} font-weight:700; color:#20160f;">
                                {{ __('diwan.brand.name') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 28px 28px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                                   font-size:15px; line-height:1.6; color:#20160f;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 28px;">
                            <hr style="border:0; border-top:1px solid #d6d0c9; margin:0 0 16px;">
                            <p style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                                      font-size:12px; line-height:1.6; color:#625952;">
                                {{ __('diwan.brand.tagline') }}<br>
                                <a href="{{ route('furnish.index') }}" style="color:#20160f; text-decoration:none;">{{ __('diwan.brand.name') }}</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
