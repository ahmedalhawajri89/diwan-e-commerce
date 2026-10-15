<x-app-layout>
    <x-slot name="title">{{ __('admin.zones.edit') }}</x-slot>

    <x-admin.head :title="__('admin.zones.edit')" :intro="$zone->name">
        <x-ui.link :href="route('admin.delivery-zones.index')" :arrow="false" tone="ink">{{ __('admin.action.back') }}</x-ui.link>
    </x-admin.head>

    <form method="POST" action="{{ route('admin.delivery-zones.update', $zone) }}" class="max-w-2xl" x-data="submitOnce" @submit="lock">
        @csrf
        @method('PUT')
        @include('admin.delivery-zones._form', ['zone' => $zone])
        <div class="flex flex-wrap gap-3 mt-10">
            <x-ui.button type="submit" ::disabled="busy">{{ __('admin.action.save') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.delivery-zones.index')">{{ __('admin.action.cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
