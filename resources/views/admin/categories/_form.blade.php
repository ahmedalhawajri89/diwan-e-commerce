{{--
  The category form, shared by create and edit.

  @param  \App\Models\Category|null       $category    null when creating
  @param  \Illuminate\Support\Collection  $categories  the possible parents
--}}
@php $category ??= null; @endphp

<div class="grid gap-7">
    <x-ui.field name="name" :label="__('admin.categories.name')" :value="old('name', $category?->name)" required maxlength="255" autofocus />
    <x-ui.field name="slug" :label="__('admin.categories.slug')" :value="old('slug', $category?->slug)" required maxlength="255" dir="ltr" />
    <x-ui.select name="parent_id" :label="__('admin.categories.parent')">
        <option value="">{{ __('admin.categories.none') }}</option>
        @foreach($categories as $option)
            <option value="{{ $option->id }}" @selected(old('parent_id', $category?->parent_id) == $option->id)>{{ $option->name }}</option>
        @endforeach
    </x-ui.select>
</div>
