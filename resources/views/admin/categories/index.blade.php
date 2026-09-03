{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.categories.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.categories.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.categories.intro') }}</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="hm-btn hm-btn--primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            {{ __('admin.categories.new') }}
        </a>
    </div>

    @if($categories->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.categories.name') }}</th>
                        <th scope="col">{{ __('admin.categories.slug') }}</th>
                        <th scope="col">{{ __('admin.categories.parent') }}</th>
                        <th scope="col">{{ __('admin.categories.count') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.action.edit') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td style="font-weight: 500;">{{ $category->name }}</td>
                            <td class="hm-num hm-muted" style="font-size: var(--text-xs);">{{ $category->slug }}</td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td class="hm-num">{{ $category->products_count }}</td>
                            <td>
                                <div class="hm-actions">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="hm-btn hm-btn--quiet"
                                       aria-label="{{ __('admin.action.edit') }}: {{ $category->name }}">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          class="delete-form"
                                          data-confirm-message="{{ $category->name }} — {{ __('admin.action.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hm-btn hm-btn--quiet" style="color: var(--color-danger);"
                                                aria-label="{{ __('admin.action.delete') }}: {{ $category->name }}">
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
        <div style="margin-top: var(--space-xl);">{{ $categories->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.categories.empty') }}</p>
    @endif
</x-app-layout>
