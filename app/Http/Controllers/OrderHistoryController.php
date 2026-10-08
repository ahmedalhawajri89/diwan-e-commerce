<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The customer's own order history.
 *
 * Until now the only way to see an order was the thank-you page immediately
 * after placing it: leave that screen and the purchase was gone. The account
 * page listed five as plain text with nothing to open.
 *
 * Every figure here reads the order's own snapshots, so a piece renamed or
 * delisted later cannot rewrite what somebody bought.
 */
class OrderHistoryController extends Controller
{
    public function index(): View
    {
        $orders = Order::withCount('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('furnish.orders', compact('orders'));
    }

    public function show(Order $order): View
    {
        // Ownership before anything else: an order id is a guessable integer.
        abort_unless($order->user_id === auth()->id(), 404);

        $order->load(['items.product.images', 'address']);

        return view('furnish.order', compact('order'));
    }

    /**
     * After delivery, one answer: did it get in easily, with difficulty, or
     * not at all. Once per order, by its owner, and only once it has arrived.
     * It is how the shop learns whether its way-in check is right.
     */
    public function feedback(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 404);
        abort_unless($order->status === 'delivered' && ! $order->delivery_feedback, 409);

        $answer = $request->validate(['answer' => 'required|in:'.implode(',', Order::FEEDBACK)])['answer'];

        $order->update(['delivery_feedback' => $answer]);
        $order->log('feedback', to: $answer);

        return redirect()->route('orders.show', $order)->with('success', __('diwan.orders.feedback.thanks'));
    }
}
