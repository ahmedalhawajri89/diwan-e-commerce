{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  The list carries a "measured / not recorded" column, because the fit system
  is only as good as this table.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.products.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.products.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.products.intro') }}</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="hm-btn hm-btn--primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            {{ __('admin.products.new') }}
        </a>
    </div>

    @if($products->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.products.name') }}</th>
                        <th scope="col">{{ __('admin.products.category') }}</th>
                        <th scope="col">{{ __('admin.products.price') }}</th>
                        <th scope="col">{{ __('admin.products.stock') }}</th>
                        <th scope="col">{{ __('admin.products.size') }}</th>
                        <th scope="col">{{ __('admin.products.status') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.action.edit') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php $variant = $product->primaryVariant(); @endphp
                        <tr>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="{{ $product->imageUrl() }}" alt="" class="hm-thumb"
                                         loading="lazy" width="44" height="44">
                                    <div style="min-width: 0;">
                                        <a href="{{ route('furnish.product.show', $product->slug) }}"
                                           style="font-weight: 500;">{{ $product->name }}</a>
                                        <p class="hm-muted hm-num mb-0" style="font-size: var(--text-xs);">{{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td class="hm-num">
                                @if($variant)
                                    {{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}
                                @else
                                    <span class="hm-muted">—</span>
                                @endif
                            </td>
                            <td class="hm-num">{{ $variant?->stock ?? '—' }}</td>
                            <td>
                                @if($variant?->width_cm && $variant?->depth_cm)
                                    <span class="hm-num" style="font-size: var(--text-xs);">
                                        {{ $variant->width_cm }}&times;{{ $variant->depth_cm }}@if($variant->height_cm)&times;{{ $variant->height_cm }}@endif
                                    </span>
                                @else
                                    <span class="hm-chip hm-chip--warn">
                                        <i class="bi bi-dash-lg" aria-hidden="true"></i>
                                        {{ __('admin.products.unmeasured') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="hm-chip {{ $product->status === 'active' ? 'hm-chip--ok' : '' }}">
                                    {{ $product->status }}
                                </span>
                            </td>
                            <td>
                                <div class="hm-actions">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="hm-btn hm-btn--quiet"
                                       aria-label="{{ __('admin.action.edit') }}: {{ $product->name }}">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                          class="delete-form"
                                          data-confirm-message="{{ $product->name }} — {{ __('admin.action.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hm-btn hm-btn--quiet"
                                                style="color: var(--color-danger);"
                                                aria-label="{{ __('admin.action.delete') }}: {{ $product->name }}">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: var(--space-xl);">{{ $products->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.products.empty') }}</p>
    @endif

</x-app-layout>
