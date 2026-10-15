{{--
  Returns: what customers asked to send back and why, newest first, with
  the shop's answer. A requested one is approved or declined; an approved
  one is marked received when the pieces are back, which returns them to
  stock. Status chips carry their real counts.

  @param  \Illuminate\Pagination\LengthAwarePaginator  $returns
  @param  string|null                                  $status
  @param  \Illuminate\Support\Collection               $counts  status => n
--}}
@php
    $tone = ['requested' => 'warn', 'approved' => 'ok', 'received' => 'ok', 'rejected' => 'danger'];
    $chip = 'inline-flex items-center gap-2 min-h-9 px-3.5 rounded-pill border text-sm no-underline transition-colors';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.returns.title') }}</x-slot>

    <x-admin.head :title="__('admin.returns.title')" :intro="__('admin.returns.intro')" />

    <nav class="flex flex-wrap gap-2 mb-6" aria-label="{{ __('admin.returns.filter') }}">
        <a href="{{ route('admin.returns.index') }}" @if(! $status) aria-current="page" @endif
           class="{{ $chip }} {{ ! $status ? 'border-ink bg-ink text-paper' : 'border-rule-2 text-ink hover:border-ink' }}">
            {{ __('admin.returns.all') }} <span class="font-mono text-xs">{{ $counts->sum() }}</span>
        </a>
        @foreach(App\Support\Returns::STATUSES as $s)
            <a href="{{ route('admin.returns.index', ['status' => $s]) }}" @if($status === $s) aria-current="page" @endif
               class="{{ $chip }} {{ $status === $s ? 'border-ink bg-ink text-paper' : 'border-rule-2 text-ink hover:border-ink' }}">
                {{ __('diwan.returns.status.'.$s) }} <span class="font-mono text-xs">{{ $counts[$s] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>

    @if($returns->isNotEmpty())
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.returns.order') }}</th>
                    <th scope="col">{{ __('admin.returns.customer') }}</th>
                    <th scope="col">{{ __('admin.returns.reason') }}</th>
                    <th scope="col">{{ __('admin.returns.asked') }}</th>
                    <th scope="col">{{ __('admin.returns.state') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.returns.act') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($returns as $return)
                    <tr class="align-top">
                        <td><a href="{{ route('admin.orders.show', $return->order_id) }}" class="font-mono text-accent-ink underline underline-offset-4" dir="ltr">{{ $return->order?->number() }}</a></td>
                        <td>{{ $return->user?->name ?? '—' }}</td>
                        <td>
                            <span class="font-medium">{{ __('diwan.returns.reasons.'.$return->reason) }}</span>
                            @if($return->note)
                                <span class="block mt-1 max-w-xs text-xs leading-body text-muted [overflow-wrap:anywhere]" dir="auto">{{ $return->note }}</span>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-muted whitespace-nowrap">{{ $return->created_at->translatedFormat(__('admin.format.datetime_short')) }}</td>
                        <td><x-ui.chip :tone="$tone[$return->status] ?? 'neutral'">{{ __('diwan.returns.status.'.$return->status) }}</x-ui.chip></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                @if($return->status === 'requested')
                                    <form method="POST" action="{{ route('admin.returns.approve', $return) }}" x-data="submitOnce" @submit="lock">
                                        @csrf
                                        <x-ui.button type="submit" variant="ghost" ::disabled="busy">{{ __('admin.returns.approve') }}</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.returns.reject', $return) }}" class="delete-form"
                                          data-confirm-title="{{ __('admin.returns.reject_title', ['number' => $return->order?->number()]) }}"
                                          data-confirm-message="{{ __('admin.returns.reject_message') }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center min-h-11 px-3 border-0 bg-transparent text-sm text-danger cursor-pointer hover:underline underline-offset-4">{{ __('admin.returns.reject') }}</button>
                                    </form>
                                @elseif($return->status === 'approved')
                                    <form method="POST" action="{{ route('admin.returns.receive', $return) }}" x-data="submitOnce" @submit="lock">
                                        @csrf
                                        <x-ui.button type="submit" ::disabled="busy"><x-icon name="box-seam" class="size-4" />{{ __('admin.returns.receive') }}</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $returns->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ __('admin.returns.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
