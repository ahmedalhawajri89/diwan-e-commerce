{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.categories.create') }}</x-slot>

    <div class="hm-app__head">
        <h1 class="hm-app__title">{{ __('admin.categories.create') }}</h1>
        <a href="{{ route('admin.categories.index') }}" class="hm-link hm-link--sm">
            <x-icon name="arrow-left" />
            {{ __('admin.action.back') }}
        </a>
    </div>

    <form class="hm-hold-field" method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        <label class="hm-field @error('name') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.categories.name') }}</span>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autofocus>
            @error('name')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('slug') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.categories.slug') }}</span>
            <input type="text" name="slug" value="{{ old('slug') }}" required maxlength="255" dir="ltr">
            @error('slug')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('parent_id') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.categories.parent') }}</span>
            <select name="parent_id">
                <option value="">{{ __('admin.categories.none') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('parent_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('parent_id')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <div class="d-flex gap-2 hm-mt-lg">
            <button type="submit" class="hm-btn hm-btn--primary">{{ __('admin.action.create') }}</button>
            <a href="{{ route('admin.categories.index') }}" class="hm-btn hm-btn--ghost">{{ __('admin.action.cancel') }}</a>
        </div>
    </form>
</x-app-layout>
