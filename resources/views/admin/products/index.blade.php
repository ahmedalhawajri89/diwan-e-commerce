{{--
  The list carries a "measured / not recorded" column, because the fit system
  is only as good as this table.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.title') }}</x-slot>

    <x-admin.head :title="__('admin.products.title')" :intro="__('admin.products.intro')">
        <x-ui.button :href="route('admin.products.create')">
            <x-icon name="plus-lg" class="size-4" />{{ __('admin.products.new') }}
        </x-ui.button>
    </x-admin.head>

    @if($need)
        <x-ui.note icon="funnel" class="mb-6">
            {{ __('admin.filter.showing') }} <strong class="font-medium">{{ __('admin.dashboard.waiting.'.$need) }}</strong>
            <x-ui.link :href="route('admin.products.index')" class="ms-1">{{ __('admin.filter.clear') }}</x-ui.link>
        </x-ui.note>
    @endif

    @if($products->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.products.name') }}</th>
                    <th scope="col">{{ __('admin.products.category') }}</th>
                    <th scope="col">{{ __('admin.products.price') }}</th>
                    <th scope="col">{{ __('admin.products.stock') }}</th>
                    <th scope="col">{{ __('admin.products.size') }}</th>
                    <th scope="col">{{ __('admin.products.status') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.edit') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    @php $variant = $product->primaryVariant(); @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-56">
                                <span class="relative block w-10 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                                    <x-picture :src="$product->imageUrl()" alt="" sizes="2.5rem" :width="80" :height="100" loading="lazy"
                                               class="absolute inset-0 size-full object-cover" />
                                </span>
                                <span class="min-w-0">
                                    <a href="{{ route('admin.products.show', $product) }}" class="block font-medium text-ink no-underline hover:underline">{{ $product->name }}</a>
                                    <span class="block font-mono text-xs text-muted">{{ $product->slug }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td class="font-mono">
                            @if($variant){{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}@else<span class="text-muted">—</span>@endif
                        </td>
                        <td class="font-mono">{{ $variant?->stock ?? '—' }}</td>
                        <td>
                            @if($variant?->width_cm && $variant?->depth_cm)
                                <span class="font-mono text-xs" dir="ltr">{{ $variant->width_cm }}&times;{{ $variant->depth_cm }}@if($variant->height_cm)&times;{{ $variant->height_cm }}@endif</span>
                            @else
                                <x-ui.chip tone="warn">{{ __('admin.products.unmeasured') }}</x-ui.chip>
                            @endif
                        </td>
                        <td><x-ui.chip :tone="$product->status === 'active' ? 'ok' : 'neutral'">{{ __('admin.products.status_values.'.$product->status) }}</x-ui.chip></td>
                        <td>
                            <x-admin.row-actions :name="$product->name"
                                                 :consequence="__('admin.cascade.product')"
                                                 :edit="route('admin.products.edit', $product)"
                                                 :delete="route('admin.products.destroy', $product)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $products->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.products.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
