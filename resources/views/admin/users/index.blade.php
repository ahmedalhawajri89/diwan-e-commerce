<x-app-layout>
    <x-slot name="title">{{ __('admin.users.title') }}</x-slot>

    <x-admin.head :title="__('admin.users.title')" :intro="__('admin.users.intro')" />

    <x-admin.toolbar :action="route('admin.users.index')" :q="$q" :placeholder="__('admin.users.search')"
                     :chips="$chips" :sorts="$sorts" :keep="['role' => $role, 'sort' => $sort]" />

    @if($users->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.users.name') }}</th>
                    <th scope="col">{{ __('admin.users.email') }}</th>
                    <th scope="col">{{ __('admin.users.roles') }}</th>
                    <th scope="col">{{ __('admin.users.orders') }}</th>
                    <th scope="col">{{ __('admin.users.joined') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.delete') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-ink no-underline hover:underline">{{ $user->name }}</a>
                                @if($user->id === auth()->id())
                                    <x-ui.chip>{{ __('admin.users.you') }}</x-ui.chip>
                                @endif
                                @if($user->isProtected())
                                    <x-ui.chip title="{{ __('admin.users.protected_note') }}">{{ __('admin.users.protected') }}</x-ui.chip>
                                @endif
                            </div>
                        </td>
                        <td class="font-mono text-xs" dir="ltr">{{ $user->email }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <x-ui.chip>{{ $role->name }}</x-ui.chip>
                                @empty
                                    <span class="text-muted">{{ __('admin.users.no_role') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="font-mono">{{ $user->orders_count }}</td>
                        <td class="font-mono text-xs text-muted whitespace-nowrap">{{ $user->created_at->translatedFormat('j M Y') }}</td>
                        <td>
                            {{-- A protected account, or your own, gets no delete button
                                 at all. The controller refuses both anyway; hiding the
                                 control means nobody is offered an action that cannot
                                 succeed. --}}
                            <x-admin.row-actions :name="$user->name"
                                                 :consequence="trans_choice('admin.cascade.user', $user->orders_count)"
                                                 :edit="$user->isProtected() ? null : route('admin.users.edit', $user)"
                                                 :delete="$user->canBeDeleted() && $user->id !== auth()->id() ? route('admin.users.destroy', $user) : null" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $users->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ $q || $role ? __('admin.list.no_match') : __('admin.users.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
