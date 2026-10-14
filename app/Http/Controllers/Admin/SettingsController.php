<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use Illuminate\Http\Request;

/**
 * The shop's facts and policies, in one form. Each is optional: leaving one
 * empty keeps the storefront saying "agreed on contact" for it.
 */
class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['settings' => ShopSetting::current()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'legal_name' => 'nullable|string|max:160',
            // A Saudi VAT registration number: fifteen digits, first and last a 3.
            'vat_number' => ['nullable', 'regex:/^3\d{13}3$/'],
            'return_days' => 'nullable|integer|min:1|max:365',
            'delivery_lead_days' => 'nullable|integer|min:0|max:60',
            'delivery_weekdays' => 'nullable|array',
            'delivery_weekdays.*' => 'integer|in:'.implode(',', ShopSetting::WEEKDAYS),
        ], [], [
            'legal_name' => __('admin.settings.legal_name'),
            'vat_number' => __('admin.settings.vat_number'),
            'return_days' => __('admin.settings.return_days'),
            'delivery_lead_days' => __('admin.settings.lead_days'),
        ]);

        $weekdays = array_values(array_unique(array_map('intval', $validated['delivery_weekdays'] ?? [])));
        sort($weekdays);

        $settings = ShopSetting::query()->first() ?? new ShopSetting;
        $settings->fill([
            'legal_name' => $validated['legal_name'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'return_days' => $validated['return_days'] ?? null,
            'delivery_lead_days' => $validated['delivery_lead_days'] ?? null,
            'delivery_weekdays' => $weekdays ?: null,
        ])->save();

        return redirect()->route('admin.settings')->with('success', __('admin.settings.saved'));
    }
}
