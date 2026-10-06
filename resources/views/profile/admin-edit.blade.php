{{--
  The operator's own account, inside the panel: details, password, and —
  unless the account is protected — closing it.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('diwan.profile.title') }}</x-slot>

    @php
        $user = auth()->user();
        $sectionClass = 'py-10 border-0 border-b border-rule first:pt-0';
        $heading = 'm-0 font-display text-2xl';
        $note = 'm-0 mt-2 mb-7 max-w-measure text-sm leading-body text-muted';
    @endphp

    <x-admin.head :title="__('diwan.profile.title')" :intro="__('diwan.profile.intro')">
        @if($user->isProtected())
            <x-ui.chip title="{{ __('admin.users.protected_note') }}">{{ __('admin.users.protected') }}</x-ui.chip>
        @endif
    </x-admin.head>

    <div class="max-w-xl">
        <section class="{{ $sectionClass }}">
            <h2 class="{{ $heading }}">{{ __('diwan.profile.details') }}</h2>
            <p class="{{ $note }}">{{ __('diwan.profile.details_note') }}</p>

            <form method="POST" action="{{ route('profile.update') }}" class="grid gap-7">
                @csrf
                @method('PATCH')
                <x-ui.field name="name" :label="__('diwan.auth.name')" :value="old('name', $user->name)" required autocomplete="name" maxlength="255" />
                <x-ui.field name="email" type="email" :label="__('diwan.auth.email')" :value="old('email', $user->email)" required autocomplete="username" dir="ltr" />
                <div><x-ui.button type="submit">{{ __('diwan.profile.save') }}</x-ui.button></div>
            </form>
        </section>

        <section class="{{ $sectionClass }}">
            <h2 class="{{ $heading }}">{{ __('diwan.profile.password') }}</h2>
            <p class="{{ $note }}">{{ __('diwan.profile.password_note') }}</p>

            <form method="POST" action="{{ route('password.update') }}" class="grid gap-7">
                @csrf
                @method('PUT')
                <x-ui.field name="current_password" type="password" bag="updatePassword" :label="__('diwan.profile.current')" autocomplete="current-password" dir="ltr" />
                <x-ui.field name="password" type="password" bag="updatePassword" :label="__('diwan.profile.new')" autocomplete="new-password" dir="ltr" id="new-password" />
                <x-ui.field name="password_confirmation" type="password" :label="__('diwan.profile.confirm')" autocomplete="new-password" dir="ltr" />
                <div><x-ui.button type="submit">{{ __('diwan.profile.save') }}</x-ui.button></div>
            </form>
        </section>

        <section class="{{ $sectionClass }} border-b-0">
            <h2 class="{{ $heading }} text-danger">{{ __('diwan.profile.danger') }}</h2>

            @if($user->isProtected())
                {{-- No form at all for a protected account. The route refuses it
                     too, but offering a control that cannot succeed is its own
                     kind of lie. --}}
                <x-ui.note icon="shield-lock" class="mt-4">{{ __('diwan.profile.protected') }}</x-ui.note>
            @else
                <p class="{{ $note }}">{{ __('diwan.profile.danger_note') }}</p>

                <form method="POST" action="{{ route('profile.destroy') }}" class="grid gap-7 p-6 border border-danger">
                    @csrf
                    @method('DELETE')
                    <x-ui.field name="password" type="password" bag="userDeletion" :label="__('diwan.auth.password')" required dir="ltr" id="delete-password" />
                    <div>
                        <x-ui.button type="submit" variant="danger"><x-icon name="trash" class="size-4" />{{ __('diwan.profile.delete') }}</x-ui.button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>
