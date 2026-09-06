{{-- Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md --}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.messages.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.messages.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.messages.intro') }}</p>
        </div>
    </div>

    @if($messages->count() > 0)
        @foreach($messages as $message)
            <article class="hm-listrow">
                <div class="d-flex flex-wrap gap-2 align-items-baseline justify-content-between hm-mb-xs">
                    <div>
                        <span class="hm-strong">{{ $message->name }}</span>
                        <a href="mailto:{{ $message->email }}" class="hm-link hm-link--sm hm-num hm-t-xs" dir="ltr">{{ $message->email }}</a>
                    </div>

                    <div class="d-flex gap-2 align-items-center">
                        <span class="hm-muted hm-num hm-t-xs">
                            {{ $message->created_at->translatedFormat('j M Y') }}
                        </span>
                        <form method="POST" action="{{ route('admin.contacts.destroy', $message->id) }}"
                              class="delete-form" data-confirm-message="{{ __('admin.action.confirm_delete') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hm-btn hm-btn--quiet hm-danger"
                                    aria-label="{{ __('admin.action.delete') }}">
                                <x-icon name="trash" />
                            </button>
                        </form>
                    </div>
                </div>

                <p class="hm-label hm-mb-2xs">
                    {{ $message->subject ?: __('admin.messages.no_subject') }}
                </p>
                <p class="hm-measure mb-0 hm-t-sm hm-ink-2">
                    {{ $message->message }}
                </p>
            </article>
        @endforeach

        <div class="hm-mt-xl">{{ $messages->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.messages.empty') }}</p>
    @endif
</x-app-layout>
