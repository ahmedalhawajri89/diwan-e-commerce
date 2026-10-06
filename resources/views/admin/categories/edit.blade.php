<x-app-layout>
    <x-slot name="title">{{ __('admin.categories.edit') }}</x-slot>

    <x-admin.head :title="__('admin.categories.edit')" :intro="$category->name">
        <x-ui.link :href="route('admin.categories.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-md" x-data="submitOnce" @submit="lock">
        @csrf
        @method('PUT')
        @include('admin.categories._form', ['category' => $category, 'categories' => $categories])

        <div class="flex flex-wrap gap-3 mt-10">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.save') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.categories.index')">{{ __('admin.action.cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
