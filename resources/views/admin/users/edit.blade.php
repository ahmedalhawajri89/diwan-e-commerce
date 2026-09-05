{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  Roles are checkboxes rather than a single select: the schema is many-to-many,
  and a control that can only express one role would quietly discard the others.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.users.edit') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.users.edit') }}</h1>
            <p class="hm-app__intro">{{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="hm-link hm-link--sm">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            {{ __('admin.action.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" style="max-width: 34rem;">
        @csrf
        @method('PUT')

        <label class="hm-field @error('name') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.users.name') }}</span>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autofocus>
            @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('email') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.users.email') }}</span>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required dir="ltr">
            @error('email')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <fieldset style="margin-block: var(--space-lg); padding-top: var(--space-md); border-top: var(--rule-hair) solid var(--color-rule);">
            <legend class="hm-label" style="padding: 0;">{{ __('admin.users.roles') }}</legend>
            <p class="hm-field__hint" style="margin-bottom: var(--space-sm);">{{ __('admin.users.role_note') }}</p>

            @php $current = old('roles', $user->roles->pluck('id')->all()); @endphp

            @foreach($roles as $role)
                <label class="hm-check">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           @checked(in_array($role->id, (array) $current))>
                    <span>{{ $role->name }}</span>
                </label>
            @endforeach

            @error('roles')<span class="hm-field__error">{{ $message }}</span>@enderror
        </fieldset>

        <div class="d-flex gap-2">
            <button type="submit" class="hm-btn hm-btn--primary">{{ __('admin.action.save') }}</button>
            <a href="{{ route('admin.users.show', $user) }}" class="hm-btn hm-btn--ghost">{{ __('admin.action.cancel') }}</a>
        </div>
    </form>
</x-app-layout>
