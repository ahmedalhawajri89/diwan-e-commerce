{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.users.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.users.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.users.intro') }}</p>
        </div>
    </div>

    @if($users->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.users.name') }}</th>
                        <th scope="col">{{ __('admin.users.email') }}</th>
                        <th scope="col">{{ __('admin.users.roles') }}</th>
                        <th scope="col">{{ __('admin.users.orders') }}</th>
                        <th scope="col">{{ __('admin.users.joined') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.action.delete') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" style="font-weight: 500;">{{ $user->name }}</a>
                                @if($user->id === auth()->id())
                                    <span class="hm-chip hm-chip--info">{{ __('admin.users.you') }}</span>
                                @endif
                                @if($user->isProtected())
                                    <span class="hm-chip" title="{{ __('admin.users.protected_note') }}">
                                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                        {{ __('admin.users.protected') }}
                                    </span>
                                @endif
                            </td>
                            <td class="hm-num" style="font-size: var(--text-xs);" dir="ltr">{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="hm-chip">{{ $role->name }}</span>
                                @empty
                                    <span class="hm-muted">{{ __('admin.users.no_role') }}</span>
                                @endforelse
                            </td>
                            <td class="hm-num">{{ $user->orders_count }}</td>
                            <td class="hm-num hm-muted" style="font-size: var(--text-xs);">
                                {{ $user->created_at->translatedFormat('j M Y') }}
                            </td>
                            <td>
                                <div class="hm-actions">
                                    @unless($user->isProtected())
                                        <a href="{{ route('admin.users.edit', $user) }}" class="hm-btn hm-btn--quiet"
                                           aria-label="{{ __('admin.action.edit') }}: {{ $user->name }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                    @endunless

                                    {{-- A protected account, or your own, gets no delete button
                                         at all. The controller refuses both anyway; hiding the
                                         control means nobody is offered an action that cannot
                                         succeed. --}}
                                    @if($user->canBeDeleted() && $user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              class="delete-form"
                                              data-confirm-message="{{ $user->name }} — {{ __('admin.action.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hm-btn hm-btn--quiet" style="color: var(--color-danger);"
                                                    aria-label="{{ __('admin.action.delete') }}: {{ $user->name }}">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: var(--space-xl);">{{ $users->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.users.empty') }}</p>
    @endif
</x-app-layout>
