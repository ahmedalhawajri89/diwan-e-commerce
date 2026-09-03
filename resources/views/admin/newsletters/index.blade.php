{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.newsletter.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.newsletter.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.newsletter.intro') }}</p>
        </div>
        <span class="hm-head__aside hm-num hm-muted">{{ $subscribers->total() }}</span>
    </div>

    @if($subscribers->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.newsletter.email') }}</th>
                        <th scope="col">{{ __('admin.newsletter.joined') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.action.delete') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscribers as $subscriber)
                        <tr>
                            <td>
                                <a href="mailto:{{ $subscriber->email }}" class="hm-num" dir="ltr"
                                   style="font-size: var(--text-sm);">{{ $subscriber->email }}</a>
                            </td>
                            <td class="hm-num hm-muted" style="font-size: var(--text-xs);">
                                {{ $subscriber->created_at->translatedFormat('j M Y') }}
                            </td>
                            <td>
                                <div class="hm-actions">
                                    <form method="POST" action="{{ route('admin.newsletters.destroy', $subscriber) }}"
                                          class="delete-form" data-confirm-message="{{ $subscriber->email }} — {{ __('admin.action.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hm-btn hm-btn--quiet" style="color: var(--color-danger);"
                                                aria-label="{{ __('admin.action.delete') }}: {{ $subscriber->email }}">
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
        <div style="margin-top: var(--space-xl);">{{ $subscribers->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.newsletter.empty') }}</p>
    @endif
</x-app-layout>
