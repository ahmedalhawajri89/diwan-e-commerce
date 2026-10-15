<x-app-layout>
    <x-slot name="title">{{ __('admin.newsletter.title') }}</x-slot>

    <x-admin.head :title="__('admin.newsletter.title')" :intro="__('admin.newsletter.intro')">
        <span class="font-mono text-sm text-muted">{{ $subscribers->total() }}</span>
    </x-admin.head>

    @if($subscribers->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.newsletter.email') }}</th>
                    <th scope="col">{{ __('admin.newsletter.joined') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.delete') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscribers as $subscriber)
                    <tr>
                        <td><a href="mailto:{{ $subscriber->email }}" class="font-mono text-sm text-ink underline underline-offset-4" dir="ltr">{{ $subscriber->email }}</a></td>
                        <td class="font-mono text-xs text-muted whitespace-nowrap">{{ $subscriber->created_at->translatedFormat('j M Y') }}</td>
                        <td><x-admin.row-actions :name="$subscriber->email" :delete="route('admin.newsletters.destroy', $subscriber)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $subscribers->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.newsletter.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
