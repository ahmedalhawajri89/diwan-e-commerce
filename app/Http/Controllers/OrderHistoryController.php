<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
}
