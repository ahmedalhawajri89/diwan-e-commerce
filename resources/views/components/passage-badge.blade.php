{{--
  The way-in badge: can this piece reach the visitor's room? The fit badge's
  partner, built the same way: an icon and a word always, never colour alone,
  on paper even over a photograph.

  With a note, it gives its reason in the visitor's own numbers, names any
  step left unchecked, and says the verdict rests on their measurements.

  @param  \App\Models\ProductVariant|null  $variant
  @param  array|null  $wayIn     the visitor's way in, or null
  @param  bool        $withNote  the reasoning beneath
  @param  bool        $onCard    inside a product card: shown only when there
                                 is something to warn about, and never the
                                 "check the way in" prompt (once a page is enough)
--}}
@props(['variant' => null, 'wayIn' => null, 'withNote' => false, 'onCard' => false])

@php
    use App\Support\Passage;

    $result = Passage::assess($variant, $wayIn);
    $status = $result['status'];
    $where = $result['where'] && $result['where'] !== 'piece' ? __('diwan.passage.where.'.$result['where']) : null;

    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-pill bg-paper '
          . 'font-label text-[0.6875rem] font-medium leading-none';
    $tone = [
        Passage::PASSES => 'text-ok',
        Passage::TIGHT => 'text-warn',
        Passage::HOIST => 'text-warn',
        Passage::NO => 'text-danger',
    ][$status] ?? 'text-muted';
    $note = 'block mt-2 text-xs leading-body text-muted';

    $warn = in_array($status, [Passage::NO, Passage::HOIST, Passage::TIGHT], true);
    $numbers = ['where' => $where, 'need' => $result['need'], 'room' => $result['room'], 'gap' => $result['gap']];
    $carry = $result['carried'] ? $variant->carry_w_cm.'×'.$variant->carry_d_cm.'×'.$variant->carry_h_cm : null;
    $unchecked = collect($result['unchecked'])->map(fn ($step) => __('diwan.passage.step.'.$step))
        ->join(app()->getLocale() === 'ar' ? '، ' : ', ');
@endphp

@if(! $wayIn)
    @unless($onCard)
        <button type="button" data-open-dialog="routeModal"
                class="{{ $pill }} border border-rule-2 text-ink cursor-pointer transition-colors hover:border-ink">
            <x-icon name="door-open" class="size-3.5" />
            <span>{{ __('diwan.passage.ask') }}</span>
        </button>
    @endunless
@elseif(! $onCard || $warn)
    <span {{ $attributes->merge(['class' => $pill.' '.$tone]) }}>
        <x-icon :name="Passage::icon($status)" class="size-3.5" />
        <span>{{ __('diwan.passage.'.$status) }}</span>
    </span>

    @if($withNote)
        <span class="{{ $note }}">
            @if($carry){{ __('diwan.passage.carried', ['size' => $carry]) }} @endif
            @if($result['where'] === 'piece')
                {{ __('diwan.passage.explain.unmeasured') }}
            @elseif($result['note'] === 'may_disassemble' && $status === Passage::UNKNOWN)
                {{ __('diwan.passage.explain.may_disassemble', $numbers) }}
            @elseif($where && $status !== Passage::UNKNOWN)
                {{ __('diwan.passage.explain.'.$status, $numbers) }}
            @endif
            @if($unchecked){{ __('diwan.passage.unchecked', ['steps' => $unchecked]) }} @endif
            <button type="button" data-open-dialog="routeModal"
                    class="inline p-0 border-0 bg-transparent text-xs text-ink underline underline-offset-4 cursor-pointer hover:text-accent-ink">{{ __('diwan.passage.by_yours') }}</button>
        </span>
    @endif
@endif
