<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user', 'product'])->latest()->paginate(15);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->update(['status' => $validated['status']]);

        flash()->success('Review status updated successfully.');

        return back();
    }

    public function destroy(Review $review)
    {
        $review->delete();

        flash()->success('Review deleted successfully.');

        return back();
    }
}
