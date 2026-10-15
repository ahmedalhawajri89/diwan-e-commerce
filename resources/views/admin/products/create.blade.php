{{-- A new product. The form is admin/products/_form, shared with edit. --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.create') }}</x-slot>

    <x-admin.head :title="__('admin.products.create')">
        <x-ui.link :href="route('admin.products.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="max-w-3xl" x-data="submitOnce" @submit="lock">
        @csrf

        @include('admin.products._form', ['categories' => $categories])

        <div class="flex flex-wrap gap-3 mt-10">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.create') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.products.index')">{{ __('admin.action.cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
