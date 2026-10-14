<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

/**
 * The cities the shop delivers to and their fees. One page: the list, and a
 * line to add a city; each city edits on its own page. A fee left empty is
 * "agreed on contact", never zero.
 */
class DeliveryZoneController extends Controller
{
    public function index()
    {
        return view('admin.delivery-zones.index', ['zones' => DeliveryZone::orderBy('position')->orderBy('name')->withCount('orders')->get()]);
    }

    public function store(Request $request)
    {
        DeliveryZone::create($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')->with('success', __('admin.zones.saved'));
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        return view('admin.delivery-zones.edit', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $deliveryZone->update($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')->with('success', __('admin.zones.saved'));
    }

    /** Orders keep their city's name and fee as they were; only the link goes. */
    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')->with('success', __('admin.zones.deleted'));
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:100',
            'name_ar' => 'nullable|string|max:100',
            'fee' => 'nullable|numeric|min:0|max:100000',
            'position' => 'nullable|integer|min:0|max:999',
        ], [], [
            'name' => __('admin.zones.name'), 'name_ar' => __('admin.zones.name_ar'), 'fee' => __('admin.zones.fee'),
        ]);

        return [
            'name' => $v['name'],
            'name_ar' => $v['name_ar'] ?? null,
            'fee' => isset($v['fee']) && $v['fee'] !== '' ? $v['fee'] : null,
            'active' => $request->boolean('active'),
            'position' => (int) ($v['position'] ?? 0),
        ];
    }
}
