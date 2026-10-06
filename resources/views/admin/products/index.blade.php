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

    {{-- The category is chosen inside the search form, so a search keeps it. --}}
    <x-admin.toolbar :action="route('admin.products.index')" :q="$q" :placeholder="__('admin.products.search')"
                     :chips="$chips" :sorts="$sorts" :keep="['status' => $status, 'sort' => $sort, 'need' => $need]"
                     :export="route('admin.products.export', request()->query())">
        <x-slot name="fields">
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.products.category') }}</span>
                <select name="category" class="h-11 ps-3 pe-8 border border-rule-2 border-e-0 bg-paper text-sm text-ink first:rounded-s-control focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus">
                    <option value="">{{ __('admin.list.every_category') }}</option>
                    @foreach($categories as $option)
                        <option value="{{ $option->id }}" @selected($category === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </label>
        </x-slot>
    </x-admin.toolbar>

    @if($need)
        <x-ui.note icon="funnel" class="mb-6">
            {{ __('admin.filter.showing') }} <strong class="font-medium">{{ __('admin.dashboard.waiting.'.$need) }}</strong>
            <x-ui.link :href="route('admin.products.index')" class="ms-1">{{ __('admin.filter.clear') }}</x-ui.link>
        </x-ui.note>
    @endif

    @if($products->count() > 0)
        <div x-data="bulkSelect" data-selected="{{ __('admin.bulk.selected') }}" data-confirm="{{ __('admin.bulk.confirm_delete') }}">
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col" class="w-px"><input type="checkbox" data-select-all @change="all" class="size-4 accent-ink cursor-pointer" aria-label="{{ __('admin.bulk.select_all') }}"></th>
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
                        <td class="w-px"><input type="checkbox" name="ids[]" value="{{ $product->id }}" form="bulkForm" data-select-row @change="count" class="size-4 accent-ink cursor-pointer" aria-label="{{ __('admin.bulk.select_row', ['name' => $product->name]) }}"></td>
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
        <x-admin.bulk-bar :action="route('admin.products.bulk')" :options="['active' => __('admin.bulk.publish'), 'inactive' => __('admin.bulk.hide'), 'draft' => __('admin.bulk.draft'), 'delete' => __('admin.bulk.delete')]" />
        </div>

        <div class="mt-10">{{ $products->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ $q || $status || $need || $category ? __('admin.list.no_match') : __('admin.products.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
