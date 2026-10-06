{{--
  The category form, shared by create and edit.

  @param  \App\Models\Category|null       $category    null when creating
  @param  \Illuminate\Support\Collection  $categories  the possible parents
--}}
@php $category ??= null; @endphp

<div class="grid gap-7">
    {{-- Raw values, never the locale-aware accessor: see SpeaksArabic. --}}
    <x-ui.field name="name" :label="__('admin.categories.name')" :value="old('name', $category?->raw('name'))" required maxlength="255" autofocus dir="ltr" />
    <x-ui.field name="name_ar" :label="__('admin.categories.name_ar')" :value="old('name_ar', $category?->raw('name_ar'))" maxlength="255" dir="rtl" lang="ar" />
    <x-ui.field name="slug" :label="__('admin.categories.slug')" :value="old('slug', $category?->slug)" required maxlength="255" dir="ltr" />
    <x-ui.select name="parent_id" :label="__('admin.categories.parent')">
        <option value="">{{ __('admin.categories.none') }}</option>
        @foreach($categories as $option)
            <option value="{{ $option->id }}" @selected(old('parent_id', $category?->parent_id) == $option->id)>{{ $option->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.field name="description" type="textarea" :label="__('admin.categories.description')" :value="old('description', $category?->raw('description'))" dir="ltr" />
    <x-ui.field name="description_ar" type="textarea" :label="__('admin.categories.description_ar')" :value="old('description_ar', $category?->raw('description_ar'))" dir="rtl" lang="ar" />
</div>
