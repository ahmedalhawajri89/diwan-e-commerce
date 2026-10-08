<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Ai\DescriptionDrafter;
use App\Support\Ai\DraftFailed;
use App\Support\Ai\ProductFacts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A first draft of a piece's descriptions, for the product form. Built from
 * the saved piece when there is one (so its photograph and every colour are
 * used), or from what has been typed so far on a new one. Returns the draft;
 * saving it is the operator's decision, in the form.
 */
class ProductDraftController extends Controller
{
    public function __invoke(Request $request, DescriptionDrafter $drafter): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'name' => 'required_without:product_id|nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'color' => 'nullable|string|max:50',
            'width_cm' => 'nullable|integer|min:1|max:2000',
            'depth_cm' => 'nullable|integer|min:1|max:2000',
            'height_cm' => 'nullable|integer|min:1|max:2000',
        ]);

        $facts = isset($validated['product_id'])
            ? ProductFacts::fromProduct(Product::findOrFail($validated['product_id']))
            : ProductFacts::fromInput($validated);

        // What is typed in the form wins over what is saved: the operator may
        // have just corrected the name.
        foreach (['name', 'name_ar'] as $field) {
            if (filled($validated[$field] ?? null)) {
                $facts[$field] = trim($validated[$field]);
            }
        }

        try {
            return response()->json($drafter->draft($facts));
        } catch (DraftFailed $e) {
            return response()->json(
                ['message' => __('admin.draft.'.$e->reason)],
                $e->reason === 'unavailable' ? 503 : 502,
            );
        }
    }
}
