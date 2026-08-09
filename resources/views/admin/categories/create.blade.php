<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Category') }}
            </h2>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.categories.store') }}" method="POST" class="p-6 bg-white border-b border-gray-200">
                    @csrf

                    <!-- Category Name -->
                    <div class="mb-6">
                        <x-input-label for="name" :value="__('Category Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Category Slug -->
                    <div class="mb-6">
                        <x-input-label for="slug" :value="__('Slug (URL mapping)')" />
                        <x-text-input id="slug" class="block mt-1 w-full bg-gray-50" type="text" name="slug" :value="old('slug')" required />
                        <p class="text-xs text-gray-500 mt-1">Unique identifier for URLs (e.g. living-room).</p>
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>

                    <!-- Parent Category -->
                    <div class="mb-6">
                        <x-input-label for="parent_id" :value="__('Parent Category (Optional)')" />
                        <select id="parent_id" name="parent_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            <option value="">-- No Parent (Main Category) --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ml-4">
                            {{ __('Save Category') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
