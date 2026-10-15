{{--
  The two account emails: a link to set a new password, and a link to confirm
  an email address. Both were Laravel's stock template — English, left to
  right, a blue button, signed "Regards, Laravel" — on a site that is neither.

  @param  string  $kind  reset | verify
  @param  string  $url   the link
  @param  \App\Models\User  $user
--}}
@php
    $rtl = (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';
@endphp
<x-emails.layout :preview="__('diwan.mail.'.$kind.'.preview')">

    <p style="margin:0 0 16px;">
        {{ filled($user->name ?? null) ? __('diwan.mail.greeting', ['name' => $user->name]) : __('diwan.mail.greeting_plain') }}
    </p>

    <p style="margin:0 0 24px;">
        {{ __('diwan.mail.'.$kind.'.body', ['minutes' => config('auth.passwords.users.expire', 60)]) }}
    </p>

    <a href="{{ $url }}"
       style="display:inline-block; background:#20160f; color:#f7f3ec; text-decoration:none;
              padding:12px 24px; border-radius:2px; font-size:15px; font-weight:500;">
        {{ __('diwan.mail.'.$kind.'.cta') }}
    </a>

    <p style="margin:24px 0 8px; font-size:13px; color:#625952;">
        {{ __('diwan.mail.'.$kind.'.ignore') }}
    </p>

    {{-- The link in full, for a client that will not render the button. --}}
    <p dir="ltr" style="margin:0; font-size:12px; color:#625952; word-break:break-all; text-align:{{ $rtl ? 'right' : 'left' }};">
        {{ $url }}
    </p>

</x-emails.layout>
