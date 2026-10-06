{{--
  Roles are checkboxes rather than a single select: the schema is many-to-many,
  and a control that can only express one role would quietly discard the others.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.users.edit') }}</x-slot>

    <x-admin.head :title="__('admin.users.edit')" :intro="$user->name">
        <x-ui.link :href="route('admin.users.show', $user)" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-7 max-w-md" x-data="submitOnce" @submit="lock">
        @csrf
        @method('PUT')

        <x-ui.field name="name" :label="__('admin.users.name')" :value="old('name', $user->name)" required maxlength="255" autofocus />
        <x-ui.field name="email" type="email" :label="__('admin.users.email')" :value="old('email', $user->email)" required dir="ltr" />

        @php $current = old('roles', $user->roles->pluck('id')->all()); @endphp

        <fieldset class="m-0 p-0 pt-7 border-0 border-t border-rule">
            <legend class="float-start w-full p-0 mb-2 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.users.roles') }}</legend>
            <p class="m-0 mb-3 text-sm text-muted">{{ __('admin.users.role_note') }}</p>

            @foreach($roles as $role)
                <x-ui.check name="roles[]" :value="$role->id" :checked="in_array($role->id, (array) $current)">{{ $role->name }}</x-ui.check>
            @endforeach

            @error('roles')
                <p class="m-0 mt-2 text-xs text-danger">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="flex flex-wrap gap-3">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.save') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.users.show', $user)">{{ __('admin.action.cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
