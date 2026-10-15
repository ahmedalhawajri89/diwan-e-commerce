{{-- Messages from the contact form, newest first, each answerable by email. --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.messages.title') }}</x-slot>

    <x-admin.head :title="__('admin.messages.title')" :intro="__('admin.messages.intro')" />

    @if($messages->count() > 0)
        <div class="grid gap-4">
            @foreach($messages as $message)
                <article class="p-6 border border-rule">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="m-0 font-medium">{{ $message->name }}</p>
                            <a href="mailto:{{ $message->email }}" class="font-mono text-xs text-accent-ink underline underline-offset-4" dir="ltr">{{ $message->email }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-muted">{{ $message->created_at->translatedFormat('j M Y') }}</span>
                            <x-admin.row-actions :name="$message->name" :delete="route('admin.contacts.destroy', $message->id)" />
                        </div>
                    </div>

                    <p class="m-0 mt-4 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $message->subject ?: __('admin.messages.no_subject') }}</p>
                    <p class="m-0 mt-2 max-w-measure text-sm leading-body text-ink-2 whitespace-pre-line">{{ $message->message }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-10">{{ $messages->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.messages.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
