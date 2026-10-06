{{--
  A data table: hairline rows, quiet headings, scrolling sideways on a narrow
  screen rather than squeezing its columns. The cells are styled from here so
  each page writes plain <th> and <td>.

  The box is relative because an sr-only label inside is absolutely
  positioned, and must be clipped here rather than widen the whole page.
--}}
<div class="relative overflow-x-auto border border-rule">
    <table {{ $attributes->merge(['class' => 'w-full border-collapse text-sm '
        . '[&_th]:px-4 [&_th]:py-3 [&_th]:text-start [&_th]:font-label [&_th]:text-xs [&_th]:font-medium [&_th]:uppercase [&_th]:tracking-label [&_th]:text-muted [&_th]:whitespace-nowrap [&_th]:bg-paper-2 '
        . '[&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_td]:border-0 [&_td]:border-t [&_td]:border-rule '
        . '[&_tbody_tr]:transition-colors [&_tbody_tr:hover]:bg-paper-2']) }}>
        {{ $slot }}
    </table>
</div>
