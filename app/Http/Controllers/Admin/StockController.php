<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Support\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Setting a colour's stock by hand, from the product list's editor or the
 * product's own page. Answers JSON for the editor (which saves without a
 * reload) and redirects back for a plain form.
 */
class StockController extends Controller
{
    public function update(Request $request, ProductVariant $variant): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0|max:100000',
            'note' => 'nullable|string|max:255',
        ]);

        $variant = Stock::set($variant, (int) $validated['stock'], 'manual', $validated['note'] ?? null);
        $total = (int) ProductVariant::where('product_id', $variant->product_id)->sum('stock');

        if ($request->wantsJson()) {
            return response()->json([
                'stock' => $variant->stock,
                'state' => Stock::state($variant->stock),
                'total' => $total,
                'totalState' => Stock::state($total),
            ]);
        }

        return back()->with('success', __('admin.stock.saved'));
    }
}
