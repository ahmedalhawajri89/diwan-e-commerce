{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  The operator's own account, inside the panel. It used to pull
  profile/partials/*, which are Tailwind components built for the Breeze
  layout — so once the panel moved onto the token layer this page rendered as
  unstyled markup with inert Alpine attributes. The forms are written out here
  instead, and the partials are retired with the rest of the Breeze scaffold.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('diwan.profile.title') }}</x-slot>

    @php $user = auth()->user(); @endphp

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('diwan.profile.title') }}</h1>
            <p class="hm-app__intro">{{ __('diwan.profile.intro') }}</p>
        </div>
        @if($user->isProtected())
            <span class="hm-chip" title="{{ __('admin.users.protected_note') }}">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                {{ __('admin.users.protected') }}
            </span>
        @endif
    </div>

    <div style="max-width: 34rem;">

        {{-- ══ Details ════════════════════════════════════════════════ --}}
        <section style="padding-bottom: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
            <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs);">
                {{ __('diwan.profile.details') }}
            </h2>
            <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.details_note') }}</p>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <label class="hm-field @error('name') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('diwan.auth.name') }}</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           autocomplete="name" maxlength="255">
                    @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>

                <label class="hm-field @error('email') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('diwan.auth.email') }}</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           autocomplete="username" dir="ltr">
                    @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>

                <button type="submit" class="hm-btn hm-btn--primary">{{ __('diwan.profile.save') }}</button>
            </form>
        </section>

        {{-- ══ Password ═══════════════════════════════════════════════ --}}
        <section style="padding-block: var(--space-xl); border-bottom: var(--rule-hair) solid var(--color-rule);">
            <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs);">
                {{ __('diwan.profile.password') }}
            </h2>
            <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.profile.password_note') }}</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <label class="hm-field @error('current_password', 'updatePassword') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('diwan.profile.current') }}</span>
                    <input type="password" name="current_password" autocomplete="current-password" dir="ltr">
                    @error('current_password', 'updatePassword')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>

                <label class="hm-field @error('password', 'updatePassword') hm-field--error @enderror">
                    <span class="hm-field__label">{{ __('diwan.profile.new') }}</span>
                    <input type="password" name="password" autocomplete="new-password" dir="ltr">
                    @error('password', 'updatePassword')<span class="hm-field__error">{{ $message }}</span>@enderror
                </label>

                <label class="hm-field">
                    <span class="hm-field__label">{{ __('diwan.profile.confirm') }}</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" dir="ltr">
                </label>

                <button type="submit" class="hm-btn hm-btn--primary">{{ __('diwan.profile.save') }}</button>
            </form>
        </section>

        {{-- ══ Close the account ══════════════════════════════════════ --}}
        <section style="padding-top: var(--space-xl);">
            <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-2xs); color: var(--color-danger);">
                {{ __('diwan.profile.danger') }}
            </h2>

            @if($user->isProtected())
                {{-- No form at all for a protected account. The route refuses
                     it too, but offering a control that cannot succeed is its
                     own kind of lie. --}}
                <p class="hm-note">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>{{ __('diwan.profile.protected') }}</span>
                </p>
            @else
                <p class="hm-muted hm-measure" style="font-size: var(--text-sm);">{{ __('diwan.profile.danger_note') }}</p>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <label class="hm-field @error('password', 'userDeletion') hm-field--error @enderror">
                        <span class="hm-field__label">{{ __('diwan.auth.password') }}</span>
                        <input type="password" name="password" required dir="ltr">
                        @error('password', 'userDeletion')<span class="hm-field__error">{{ $message }}</span>@enderror
                    </label>

                    <button type="submit" class="hm-btn hm-btn--danger">{{ __('diwan.profile.delete') }}</button>
                </form>
            @endif
        </section>

    </div>
</x-app-layout>
