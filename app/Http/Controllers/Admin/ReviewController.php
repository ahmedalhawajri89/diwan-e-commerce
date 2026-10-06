<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\AdminList;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const SORTS = ['newest', 'oldest', 'rating_low', 'rating_high'];

    public function index(Request $request)
    {
        $status = AdminList::choice($request, 'status', self::STATUSES);
        $q = AdminList::term($request);
        $sort = AdminList::choice($request, 'sort', self::SORTS, self::SORTS[0]);

        $base = AdminList::search(Review::query(), $q, ['comment', 'user.name', 'product.name', 'product.name_ar']);
        $chips = AdminList::chips($base, 'status', self::STATUSES, $status, 'admin.reviews.values');

        $reviews = (clone $base)->with(['user', 'product'])
            ->when($status, fn ($w) => $w->where('status', $status))
            ->when($sort === 'newest', fn ($w) => $w->latest()->orderByDesc('id'))
            ->when($sort === 'oldest', fn ($w) => $w->oldest()->orderBy('id'))
            ->when($sort === 'rating_low', fn ($w) => $w->orderBy('rating'))
            ->when($sort === 'rating_high', fn ($w) => $w->orderByDesc('rating'))
            ->paginate(15)
            ->withQueryString();

        $sorts = AdminList::sorts(self::SORTS, $sort);

        return view('admin.reviews.index', compact('reviews', 'status', 'q', 'sort', 'chips', 'sorts'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->update(['status' => $validated['status']]);

        session()->flash('success', __('admin.flash.review_updated'));

        return back();
    }

    /** Approve, reject or delete several reviews at once. */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|max:200',
            'ids.*' => 'integer',
            'action' => 'required|string|in:approved,rejected,delete',
        ]);

        $reviews = Review::whereIn('id', $validated['ids']);

        $done = $validated['action'] === 'delete'
            ? $reviews->get()->each->delete()->count()
            : $reviews->where('status', '!=', $validated['action'])->get()
                ->each(fn ($review) => $review->update(['status' => $validated['action']]))
                ->count();

        session()->flash('success', trans_choice('admin.bulk.done', $done, ['count' => $done]));

        return back();
    }

    public function destroy(Review $review)
    {
        $review->delete();

        session()->flash('success', __('admin.flash.review_deleted'));

        return back();
    }
}
