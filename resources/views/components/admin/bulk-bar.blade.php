{{--
  The bar that acts on a list's selection. Fixed to the foot of the screen
  and moved off it while nothing is chosen (transform only), so it never
  pushes the list about. Lives inside an x-data="bulkSelect" element; the
  rows' boxes reach this form through form="bulkForm".

  @param  string  $action   the bulk URL
  @param  array   $options  [value => label]; "delete" is confirmed first
--}}
@props(['action', 'options'])

<form id="bulkForm" x-ref="form" method="POST" action="{{ $action }}"
      :aria-hidden="none" aria-hidden="true" :inert="none" inert
      class="fixed bottom-4 inset-x-4 z-30 mx-auto w-fit max-w-[calc(100vw-2rem)] flex flex-wrap items-center gap-3 p-2 ps-4 rounded-pill bg-deep text-on-deep
             transition-[translate,opacity] duration-300 ease-out translate-y-[150%] opacity-0 data-[open]:translate-y-0 data-[open]:opacity-100"
      :data-open="any">
    @csrf
    <span class="text-sm font-medium whitespace-nowrap" x-text="label" role="status" aria-live="polite"></span>

    <label class="flex items-center">
        <span class="sr-only">{{ __('admin.bulk.choose') }}</span>
        <select name="action" x-ref="action" @change="chose" required
                class="h-10 ps-3 pe-8 rounded-pill border border-deep-rule bg-deep-2 text-sm text-on-deep focus-visible:outline-2 focus-visible:outline-brass">
            <option value="">{{ __('admin.bulk.choose') }}</option>
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <button type="submit" class="h-10 px-5 rounded-pill border-0 bg-brass text-deep text-sm font-medium cursor-pointer transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass">
        {{ __('admin.bulk.apply') }}
    </button>
    <button type="button" @click="clear" aria-label="{{ __('admin.bulk.clear') }}"
            class="inline-flex items-center justify-center size-10 rounded-pill border-0 bg-transparent text-on-deep-muted cursor-pointer hover:bg-deep-2 hover:text-on-deep focus-visible:outline-2 focus-visible:outline-brass">
        <x-icon name="x-lg" class="size-3.5" />
    </button>
</form>
