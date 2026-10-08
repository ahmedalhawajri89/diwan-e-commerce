{{--
  The pieces «Ask Diwan» points to, drawn from the catalogue rows: the real
  photograph, name, price and the fit verdict on the visitor's room. Nothing
  here comes from the assistant's words but the choice of which pieces.

  @param  \Illuminate\Support\Collection<\App\Models\Product>  $products
--}}
@if($products->isNotEmpty())
    <ul class="m-0 mt-3 p-0 list-none grid gap-2">
        @foreach($products as $product)
            @php $variant = $product->primaryVariant(); @endphp
            <li>
                <a href="{{ route('furnish.product.show', $product->slug) }}"
                   class="group flex items-center gap-3 p-2 border border-rule bg-paper text-ink no-underline transition-colors hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    <span class="relative block w-14 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                        <x-picture :src="$product->imageUrl()" alt="" sizes="3.5rem" :width="112" :height="140" loading="lazy" class="absolute inset-0 size-full object-cover" />
                    </span>
                    <span class="flex-1 min-w-0 grid gap-1">
                        <span class="font-display text-base leading-snug truncate group-hover:underline underline-offset-4">{{ $product->name }}</span>
                        <span class="flex flex-wrap items-center gap-2">
                            @if($variant)
                                <span class="font-mono text-sm text-accent-ink" dir="ltr">{{ __('diwan.common.currency') }}{{ number_format($variant->price, 2) }}</span>
                            @endif
                            <x-fit-badge :variant="$variant" :room="$room" :onCard="true" />
                        </span>
                    </span>
                    <x-icon name="arrow-right" class="size-3.5 shrink-0 text-muted" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
