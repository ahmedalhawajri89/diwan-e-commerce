{{--
  A piece's stock where it is listed: every colour together, marked when it
  is out or low, and a button that opens a small editor with one field per
  colour (stockEditor). Each save is a line in the stock log.

  @param  \App\Models\Product  $product  with its variants loaded
--}}
@props(['product'])

@php
    $total = $product->totalStock();
    $state = \App\Support\Stock::state($total);
    $chip = ['out' => 'bg-danger-wash text-danger', 'low' => 'bg-warn-wash text-warn'][$state] ?? '';
    $field = 'w-16 h-9 border-y border-x-0 border-rule-2 bg-paper text-center font-mono text-sm text-ink [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-focus';
    $stepper = 'inline-flex items-center justify-center size-9 border border-rule-2 bg-paper text-ink cursor-pointer hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus';
@endphp

@if($product->variants->isEmpty())
    <span class="text-muted">—</span>
@else
    <div x-data="stockEditor" @click.outside="close" @keydown.escape="dismiss" class="relative inline-block"
         data-saved="{{ __('admin.stock.saved') }}" data-failed="{{ __('admin.stock.failed') }}"
         data-out="{{ __('admin.stock.out') }}" data-low="{{ __('admin.stock.low') }}">
        <button type="button" x-ref="button" @click="toggle" :aria-expanded="open" aria-expanded="false"
                aria-label="{{ __('admin.stock.edit_for', ['name' => $product->name]) }}"
                class="group inline-flex items-center gap-2 min-h-9 px-2 -mx-2 rounded-control border-0 bg-transparent text-ink cursor-pointer hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus">
            <span x-ref="total" class="font-mono">{{ $total }}</span>
            <span x-ref="chip" @if(! $chip) hidden @endif class="inline-flex items-center px-2 py-0.5 rounded-pill font-label text-[0.6875rem] font-medium leading-none {{ $chip }}">{{ $chip ? __('admin.stock.'.$state) : '' }}</span>
            <x-icon name="pencil" class="size-3 text-muted opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100 transition-opacity" />
        </button>

        <div x-show="open" x-cloak @keydown.enter.prevent="save"
             x-transition:enter="transition-[opacity,translate] duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="absolute top-full start-0 z-30 mt-2 w-72 p-4 bg-paper border border-rule text-start">
            <p class="m-0 mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('admin.stock.by_colour') }}</p>

            <div class="grid gap-2.5">
                @foreach($product->variants->sortBy('id') as $variant)
                    <div data-stock-row data-url="{{ route('admin.variants.stock', $variant) }}" data-original="{{ $variant->stock }}"
                         class="flex items-center justify-between gap-3">
                        <label for="stock{{ $variant->id }}" class="min-w-0 truncate text-sm">{{ $variant->colourLabel() ?? __('admin.stock.only_colour') }}</label>
                        <span class="flex items-center shrink-0" dir="ltr">
                            <button type="button" data-step="-1" @click="step" class="{{ $stepper }} rounded-s-control" aria-label="{{ __('admin.stock.less') }}">−</button>
                            <input id="stock{{ $variant->id }}" type="number" name="stock" min="0" inputmode="numeric" value="{{ $variant->stock }}" class="{{ $field }}">
                            <button type="button" data-step="1" @click="step" class="{{ $stepper }} rounded-e-control" aria-label="{{ __('admin.stock.more') }}">+</button>
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between gap-3 mt-4 pt-3 border-0 border-t border-rule">
                <span x-ref="status" role="status" aria-live="polite" class="text-xs"></span>
                <button type="button" @click="save" :disabled="busy"
                        class="inline-flex items-center gap-2 h-9 px-4 rounded-control border-0 bg-ink text-paper text-sm cursor-pointer transition-opacity hover:opacity-90 disabled:opacity-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    {{ __('admin.stock.save') }}
                </button>
            </div>
        </div>
    </div>
@endif
