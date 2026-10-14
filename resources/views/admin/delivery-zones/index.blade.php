{{--
  The cities the shop delivers to, with their fees, and a form to add one.
  With none, the checkout asks for a city as free text and says the fee is
  agreed on contact.

  @param  \Illuminate\Support\Collection  $zones  with orders_count
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.zones.title') }}</x-slot>

    <x-admin.head :title="__('admin.zones.title')" :intro="__('admin.zones.intro')" />

    @if($zones->isNotEmpty())
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.zones.name') }}</th>
                    <th scope="col">{{ __('admin.zones.fee') }}</th>
                    <th scope="col">{{ __('admin.zones.orders') }}</th>
                    <th scope="col">{{ __('admin.zones.state') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.edit') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($zones as $zone)
                    <tr>
                        <td class="font-medium">{{ $zone->name }}</td>
                        <td class="font-mono">
                            @if($zone->fee !== null)@money($zone->fee)@else<span class="font-body text-muted">{{ __('admin.zones.on_contact') }}</span>@endif
                        </td>
                        <td class="font-mono">{{ $zone->orders_count }}</td>
                        <td><x-ui.chip :tone="$zone->active ? 'ok' : 'neutral'">{{ $zone->active ? __('admin.zones.offered') : __('admin.zones.hidden') }}</x-ui.chip></td>
                        <td>
                            <x-admin.row-actions :name="$zone->name"
                                                 :edit="route('admin.delivery-zones.edit', $zone)"
                                                 :delete="route('admin.delivery-zones.destroy', $zone)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-ui.empty>{{ __('admin.zones.empty') }}</x-ui.empty>
    @endif

    <section class="max-w-2xl mt-12 pt-8 border-0 border-t border-rule" aria-labelledby="addZone">
        <h2 id="addZone" class="m-0 mb-6 font-display text-xl">{{ __('admin.zones.add') }}</h2>
        <form method="POST" action="{{ route('admin.delivery-zones.store') }}" x-data="submitOnce" @submit="lock">
            @csrf
            @include('admin.delivery-zones._form')
            <div class="mt-8"><x-ui.button type="submit" ::disabled="busy">{{ __('admin.zones.add') }}</x-ui.button></div>
        </form>
    </section>
</x-app-layout>
