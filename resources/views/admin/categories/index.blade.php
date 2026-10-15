<x-app-layout>
    <x-slot name="title">{{ __('admin.categories.title') }}</x-slot>

    <x-admin.head :title="__('admin.categories.title')" :intro="__('admin.categories.intro')">
        <x-ui.button :href="route('admin.categories.create')">
            <x-icon name="plus-lg" class="size-4" />{{ __('admin.categories.new') }}
        </x-ui.button>
    </x-admin.head>

    @if($categories->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.categories.name') }}</th>
                    <th scope="col">{{ __('admin.categories.slug') }}</th>
                    <th scope="col">{{ __('admin.categories.parent') }}</th>
                    <th scope="col">{{ __('admin.categories.count') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.edit') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td class="font-medium">{{ $category->name }}</td>
                        <td class="font-mono text-xs text-muted">{{ $category->slug }}</td>
                        <td>{{ $category->parent?->name ?? '—' }}</td>
                        <td class="font-mono">{{ $category->products_count }}</td>
                        <td>
                            {{-- Its products go with it (category_id cascades), so the
                                 question says how many before anyone answers it. --}}
                            <x-admin.row-actions :name="$category->name"
                                                 :consequence="$category->products_count > 0 ? trans_choice('admin.cascade.category', $category->products_count) : null"
                                                 :edit="route('admin.categories.edit', $category)"
                                                 :delete="route('admin.categories.destroy', $category)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $categories->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.categories.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
