{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  The two selects list exactly what the column now accepts. Before the enum was
  aligned, "processing" and "refunded" passed validation here and were then
  rejected by the database.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.orders.update') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.orders.update') }}</h1>
            <p class="hm-app__intro hm-num">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="hm-link hm-link--sm">
            <x-icon name="arrow-left" />
            {{ __('admin.action.back') }}
        </a>
    </div>

    <form class="hm-hold-field" method="POST" action="{{ route('admin.orders.update', $order->id) }}">
        @csrf
        @method('PUT')

        <label class="hm-field @error('status') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.orders.status') }}</span>
            <select name="status" required>
                @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>
                        {{ __('admin.orders.status_values.' . $status) }}
                    </option>
                @endforeach
            </select>
            @error('status')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <label class="hm-field @error('payment_status') hm-field--error @enderror">
            <span class="hm-field__label">{{ __('admin.orders.payment') }}</span>
            <select name="payment_status" required>
                @foreach(['pending', 'paid', 'failed', 'refunded'] as $status)
                    <option value="{{ $status }}" @selected(old('payment_status', $order->payment_status) === $status)>
                        {{ __('admin.orders.payment_values.' . $status) }}
                    </option>
                @endforeach
            </select>
            @error('payment_status')<span class="hm-field__error">{{ $message }}</span>@enderror
        </label>

        <div class="d-flex gap-2 hm-mt-lg">
            <button type="submit" class="hm-btn hm-btn--primary">{{ __('admin.orders.save') }}</button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="hm-btn hm-btn--ghost">{{ __('admin.action.cancel') }}</a>
        </div>
    </form>
</x-app-layout>
