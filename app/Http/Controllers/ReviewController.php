<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        // Back to the form itself, not the top of a long page: the errors and
        // what was typed are shown beside the fields they belong to.
        $form = url()->previous().'#reviews';

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->to($form)->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $product->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending', // Default to pending for moderation
        ]);

        session()->flash('success', __('diwan.flash.review_pending'));

        return redirect()->to($form);
    }
}
