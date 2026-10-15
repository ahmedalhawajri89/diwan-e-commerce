{{--
  One line of the activity log: who, what they did to what, when and from
  where, opening to each field before and after. Used by the log itself and
  by the short "latest changes" on a piece's or a person's page.

  @param  \App\Models\ActivityLog  $log      with its user loaded
  @param  bool                     $compact  no avatar, no address (the short lists)
--}}
@props(['log', 'compact' => false])

@php
    $changes = collect($log->changes ?? [])->except(['_product', 'agent']);
    $who = $log->user?->name ?? __('admin.activity.someone');
    $initials = collect(preg_split('/\s+/u', trim($who)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $subject = __('admin.activity.subjects.'.$log->subject());
    $rtl = (config('localization.supported')[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';
    $url = $log->url();

    // Field values that are words, said in the reader's language.
    $words = function (string $field, $value) use ($log) {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value) || in_array($field, ['is_primary', 'is_protected'], true)) {
            return $value ? __('admin.activity.yes') : __('admin.activity.no');
        }

        return match (true) {
            $field === 'status' && $log->subject() === 'order' => __('admin.orders.status_values.'.$value),
            $field === 'status' && $log->subject() === 'product' => __('admin.products.status_values.'.$value),
            $field === 'status' && $log->subject() === 'review' => __('admin.reviews.values.'.$value),
            $field === 'payment_status' => __('admin.orders.payment_values.'.$value),
            default => (string) $value,
        };
    };
    $field = fn (string $name) => \Illuminate\Support\Facades\Lang::has('admin.activity.fields.'.$name) ? __('admin.activity.fields.'.$name) : $name;

    $tone = ['created' => 'bg-ok', 'updated' => 'bg-accent-ink', 'deleted' => 'bg-danger', 'signed_in' => 'bg-rule-2', 'sign_in_failed' => 'bg-warn'][$log->action] ?? 'bg-rule-2';

    // One change reads in the sentence itself; several open in a table.
    $single = $log->action === 'updated' && $changes->count() === 1 ? $changes->keys()->first() : null;
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3']) }}>
    @unless($compact)
        <span class="relative inline-flex items-center justify-center size-9 shrink-0 rounded-pill bg-paper-2 font-label text-xs font-semibold text-neutral" aria-hidden="true">
            {{ $initials }}
            <span class="absolute -bottom-0.5 -end-0.5 size-2.5 rounded-pill ring-2 ring-paper {{ $tone }}"></span>
        </span>
    @endunless

    <div class="flex-1 min-w-0">
        <p class="m-0 text-sm leading-snug">
            @if(in_array($log->action, ['signed_in', 'sign_in_failed'], true))
                {{ __('admin.activity.sentence.'.$log->action, ['who' => $who]) }}
            @else
                <span class="font-medium">{{ $who }}</span>
                {{ __('admin.activity.sentence.'.$log->action, ['subject' => $subject]) }}
                @if($url)
                    <a href="{{ $url }}" class="text-accent-ink underline underline-offset-4" dir="auto">{{ $log->entity_label }}</a>
                @else
                    <span class="font-medium" dir="auto">{{ $log->entity_label }}</span>
                @endif
                @if($single)
                    <span class="text-muted">: {{ $field($single) }}</span>
                    {{-- Before, then after, in the reading direction: → in English, ← in Arabic. --}}
                    <bdi class="font-mono text-xs">{{ $words($single, $changes[$single][0] ?? null) }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span><span class="sr-only">{{ __('admin.activity.to') }}</span> {{ $words($single, $changes[$single][1] ?? null) }}</bdi>
                @endif
            @endif
        </p>

        <p class="m-0 mt-1 font-mono text-xs text-muted">
            <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}">{{ $log->created_at->diffForHumans() }}</span>
            @if(! $compact && $log->ip_address) · <bdi dir="ltr">{{ $log->ip_address }}</bdi>@endif
        </p>

        @if(! $single && $changes->isNotEmpty() && $log->action === 'updated')
            <details class="group mt-2">
                <summary class="inline-flex items-center gap-1.5 min-h-8 cursor-pointer list-none [&::-webkit-details-marker]:hidden text-xs text-accent-ink">
                    <x-icon name="chevron-down" class="size-3 transition-transform group-open:rotate-180" />
                    {{ trans_choice('admin.activity.fields_changed', $changes->count(), ['count' => $changes->count()]) }}
                </summary>
                <table class="w-full mt-2 border-collapse text-xs motion-safe:group-open:animate-unfold">
                    <thead>
                        <tr class="text-muted">
                            <th scope="col" class="py-1.5 pe-3 text-start font-medium">{{ __('admin.activity.field') }}</th>
                            <th scope="col" class="py-1.5 pe-3 text-start font-medium">{{ __('admin.activity.before') }}</th>
                            <th scope="col" class="py-1.5 text-start font-medium">{{ __('admin.activity.after') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($changes as $name => $pair)
                            <tr class="border-0 border-t border-rule align-top">
                                <td class="py-1.5 pe-3 text-muted whitespace-nowrap">{{ $field($name) }}</td>
                                <td class="py-1.5 pe-3 [overflow-wrap:anywhere] text-danger/80 line-through decoration-danger/40" dir="auto">{{ $words($name, $pair[0] ?? null) }}</td>
                                <td class="py-1.5 [overflow-wrap:anywhere] text-ink" dir="auto">{{ $words($name, $pair[1] ?? null) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif
    </div>
</div>
