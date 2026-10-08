{{--
  «Ask Diwan»: a button at the foot of every storefront page and a drawer
  with a short conversation (assistant.js). Present only when a Claude key
  is configured. The answers come from the live catalogue; the pieces under
  each answer are drawn by the server, with their real prices.
--}}
@inject('assistant', 'App\Support\Ai\ShoppingAssistant')

@if($assistant->available())
    @php
        $suggestions = __('diwan.assistant.suggestions');
        // A piece's page has the add-to-bag bar at the foot of a phone's
        // screen; the door sits above it there.
        $raised = request()->routeIs('furnish.product.show') ? 'max-lg:bottom-24' : '';
    @endphp

    <div x-data="assistant" data-url="{{ route('assistant.ask') }}" data-forget="{{ route('assistant.forget') }}"
         data-failed="{{ __('diwan.assistant.failed') }}" data-thinking="{{ __('diwan.assistant.thinking') }}">

        {{-- The door: a pill at the inline-start foot, out of the bag's way. --}}
        <button type="button" @click="show" x-ref="opener" :aria-expanded="open" aria-expanded="false" aria-controls="assistantPanel"
                class="fixed bottom-5 {{ $raised }} start-5 z-30 inline-flex items-center gap-2 h-12 ps-4 pe-5 rounded-pill border-0 bg-deep text-on-deep text-sm cursor-pointer
                       transition-[translate,opacity] duration-300 ease-out hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus
                       data-[hidden]:opacity-0 data-[hidden]:pointer-events-none" :data-hidden="open">
            <x-icon name="stars" class="size-4 text-brass" />{{ __('diwan.assistant.open') }}
        </button>

        <dialog id="assistantPanel" x-ref="dialog" @close="closed" aria-labelledby="assistantTitle"
                class="m-0 me-auto h-dvh max-h-none w-[min(26rem,94vw)] max-w-none p-0 border-0 bg-paper text-ink
                       motion-safe:transition-[translate,overlay,display] motion-safe:duration-300 motion-safe:ease-out motion-safe:transition-discrete
                       starting:open:-translate-x-full rtl:starting:open:translate-x-full">
            <div class="flex flex-col h-full">
                <header class="flex items-center justify-between gap-3 px-5 h-16 shrink-0 border-0 border-b border-rule">
                    <p id="assistantTitle" class="flex items-center gap-2 m-0 font-display text-xl">
                        <span class="inline-flex items-center justify-center size-8 rounded-pill bg-deep text-brass"><x-icon name="stars" class="size-3.5" /></span>
                        {{ __('diwan.assistant.title') }}
                    </p>
                    <span class="flex items-center gap-1">
                        <button type="button" @click="restart" class="inline-flex items-center min-h-10 px-2 border-0 bg-transparent text-xs text-muted underline underline-offset-4 cursor-pointer hover:text-ink">{{ __('diwan.assistant.restart') }}</button>
                        <button type="button" @click="hide" aria-label="{{ __('diwan.nav.close') }}"
                                class="inline-flex items-center justify-center size-10 rounded-pill border-0 bg-transparent text-ink cursor-pointer hover:bg-paper-2">
                            <x-icon name="x-lg" class="size-4" />
                        </button>
                    </span>
                </header>

                {{-- The conversation. Each answer is announced as it arrives. --}}
                <div x-ref="log" class="flex-1 overflow-y-auto px-5 py-5 grid content-start gap-4" aria-live="polite">
                    <div class="grid gap-3">
                        <p class="m-0 text-sm leading-body text-neutral">{{ __('diwan.assistant.hello') }}</p>
                        <ul x-ref="suggestions" class="m-0 p-0 list-none flex flex-wrap gap-2">
                            @foreach($suggestions as $suggestion)
                                <li>
                                    <button type="button" @click="suggest" data-text="{{ $suggestion }}"
                                            class="inline-flex items-center min-h-9 px-3.5 rounded-pill border border-rule bg-paper text-sm text-ink text-start cursor-pointer transition-colors hover:border-ink">
                                        {{ $suggestion }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <form @submit.prevent="send" class="shrink-0 p-4 border-0 border-t border-rule">
                    <label for="assistantInput" class="sr-only">{{ __('diwan.assistant.label') }}</label>
                    <div class="flex items-end gap-2">
                        <textarea id="assistantInput" x-ref="input" rows="1" maxlength="500" required dir="auto"
                                  @keydown.enter="enter" placeholder="{{ __('diwan.assistant.placeholder') }}"
                                  class="flex-1 min-h-11 max-h-32 py-2.5 px-3 border border-rule-2 rounded-control bg-paper text-sm leading-snug text-ink resize-none placeholder:text-muted focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus"></textarea>
                        <button type="submit" :disabled="busy" aria-label="{{ __('diwan.assistant.send') }}"
                                class="inline-flex items-center justify-center size-11 shrink-0 rounded-control border-0 bg-ink text-paper cursor-pointer transition-opacity hover:opacity-90 disabled:opacity-50 disabled:cursor-wait focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            <x-icon name="arrow-right" class="size-4" />
                        </button>
                    </div>
                    <p class="m-0 mt-2 text-[0.6875rem] leading-snug text-muted">{{ __('diwan.assistant.note') }}</p>
                </form>
            </div>
        </dialog>
    </div>
@endif
