{{--
  A delivery city's fields. The fee is optional: empty means the fee is
  agreed when the shop calls, and the checkout says exactly that.

  @param  \App\Models\DeliveryZone|null  $zone
--}}
@php $zone ??= null; @endphp

<div class="grid gap-6 sm:grid-cols-2">
    <x-ui.field name="name" :label="__('admin.zones.name')" :value="old('name', $zone?->raw('name'))" required maxlength="100" dir="ltr" />
    <x-ui.field name="name_ar" :label="__('admin.zones.name_ar')" :value="old('name_ar', $zone?->raw('name_ar'))" maxlength="100" dir="rtl" lang="ar" />
    <x-ui.field name="fee" type="number" :label="__('admin.zones.fee').' ('.App\Support\Money::symbol().')'" :value="old('fee', $zone?->fee)"
                :hint="__('admin.zones.fee_hint')" min="0" step="0.01" inputmode="decimal" dir="ltr" />
    <x-ui.field name="position" type="number" :label="__('admin.zones.position')" :value="old('position', $zone?->position ?? 0)" min="0" max="999" dir="ltr" />
</div>
<label class="inline-flex items-center gap-2 mt-5 text-sm cursor-pointer">
    <input type="checkbox" name="active" value="1" @checked(old('active', $zone?->active ?? true)) class="size-4 accent-ink">
    {{ __('admin.zones.active') }}
</label>
