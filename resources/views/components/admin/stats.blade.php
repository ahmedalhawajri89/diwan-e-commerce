{{--
  A row of real figures. Hairlines between cells, not cards: the numbers are
  the content. Holds <x-admin.stat> children.
--}}
<dl {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-px m-0 mb-10 bg-rule border border-rule']) }}>
    {{ $slot }}
</dl>
