<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = Cart::with(['items.product.images', 'items.variant'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            sweetalert()->addError(__('diwan.bag.empty'));

            return redirect()->route('cart.index');
        }

        return view('furnish.checkout', compact('cart'));
    }

    public function process(Request $request)
    {
        // Card fields used to be collected and validated here — sixteen digits,
        // an expiry, a CVC — and then thrown away: never stored, never sent
        // anywhere, no gateway behind them. That is worse than not asking,
        // because it trains people to type card numbers into a form that does
        // not protect them. Until a real payment provider is wired up, the shop
        // takes cash on delivery and says so.
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
        ], [], [
            'country' => __('diwan.checkout.country'),
            'city' => __('diwan.checkout.city'),
            'street' => __('diwan.checkout.street'),
            'phone' => __('diwan.checkout.phone'),
        ]);

        $cart = Cart::with(['items.product.images', 'items.variant'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            sweetalert()->addError(__('diwan.bag.empty'));

            return redirect()->route('furnish.products');
        }

        $totalPrice = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

        DB::beginTransaction();

        try {
            // Stock, before anything else in the transaction.
            //
            // It was never decremented at all: a variant said "69 in stock"
            // after its thousandth sale, and the number was only ever changed
            // by hand in the admin panel. The product page printed it, and the
            // structured data published it to search engines — an invented
            // metric with a longer reach than the page it came from.
            //
            // `lockForUpdate` is the point of doing this here rather than after
            // the order is written. Two people buying the last piece at the
            // same moment both read stock = 1 without it, and both succeed.
            // The lock makes the second wait for the first to commit, so it
            // reads 0 and is turned away.
            $shortfall = $this->reserveStock($cart);

            if ($shortfall !== null) {
                DB::rollBack();

                sweetalert()->addError(__('diwan.checkout.sold_out', ['name' => $shortfall]));

                return redirect()->route('cart.index');
            }

            // The phone now has its own column. It used to be appended to the
            // street — "Rainbow Street 12 (Phone: 0791234567)" — which corrupted
            // the row and would have printed on the delivery label.
            $address = Address::create([
                'user_id' => auth()->id(),
                'country' => $validated['country'],
                'city' => $validated['city'],
                'street' => $validated['street'],
                'phone' => $validated['phone'],
            ]);

            // Nothing has been paid, so nothing claims to have been. An operator
            // marks the order paid from the admin panel when the money actually
            // arrives; that is the only place the truth is known.
            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_address_id' => $address->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    // Snapshots, so the confirmation and the order history stay
                    // true if the piece is later renamed or delisted.
                    'product_name_snapshot' => $item->product?->name ?? __('diwan.common.error'),
                    'product_image_snapshot' => $item->product?->primaryImage()?->image_url,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // After the commit, and inside its own try: a mail server that is
            // down must not roll back an order that is already paid for in
            // trust. The customer sees the thank-you page either way, and the
            // failure goes to the log where an operator can resend.
            //
            // The locale is captured here rather than read in the job, because
            // a queued mail runs in a worker that knows nothing about the
            // request — and a customer who shopped in Arabic should not get an
            // English receipt because the queue happened to boot in English.
            try {
                Mail::to($order->user->email)->send(
                    new OrderPlaced($order->load('items', 'address', 'user'), app()->getLocale())
                );
            } catch (\Throwable $e) {
                Log::error('Order confirmation email failed', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route('checkout.thankyou', $order->id);
        } catch (\Throwable $e) {
            DB::rollBack();

            // The customer gets a plain apology; the detail goes to the log
            // rather than onto the screen, where it would leak internals.
            Log::error('Checkout failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            sweetalert()->addError(__('diwan.common.error'));

            return redirect()->back()->withInput();
        }
    }

    public function thankyou($orderId)
    {
        // The confirmation reads its lines from the order's own snapshots, so
        // they stay correct even if the product is renamed or delisted later.
        $order = Order::with(['items', 'address'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        return view('furnish.thankyou', compact('order'));
    }

    /**
     * Take the ordered quantities out of stock, or report the first piece that
     * cannot supply them.
     *
     * Rows are locked in a stable order — by variant id — so two carts holding
     * the same two pieces cannot each hold one lock and wait for the other.
     * Deadlock is not a theoretical risk here; it is the ordinary outcome of
     * locking a set of rows in whatever order a cart happens to list them.
     *
     * @return string|null the name of the piece that fell short, or null on success
     */
    private function reserveStock(Cart $cart): ?string
    {
        $items = $cart->items->sortBy('product_variant_id');

        foreach ($items as $item) {
            // A cart item with no variant is a product that has none — the
            // catalogue allows it, and such a piece carries no stock to track.
            // Nothing to reserve, so nothing to refuse. Caught by the existing
            // CheckoutTest, which buys exactly that; the first version of this
            // method read "no variant row" as "sold out" and turned the order
            // away.
            if ($item->product_variant_id === null) {
                continue;
            }

            $variant = ProductVariant::whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();

            // An id that points at nothing is different: the row was deleted
            // while the piece sat in somebody's bag, and the shop no longer
            // knows what it would be selling.
            if (! $variant) {
                return $item->product?->name ?? __('diwan.common.error');
            }

            if ($variant->stock < $item->quantity) {
                return $item->product?->name ?? __('diwan.common.error');
            }

            $variant->decrement('stock', $item->quantity);
        }

        return null;
    }

    /**
     * Put stock back when an order will not be fulfilled.
     *
     * Called from the admin panel on cancellation. Without it, cancelling an
     * order quietly destroys inventory: the pieces are back on the shelf and
     * the shop believes they are gone.
     */
    public static function releaseStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                ProductVariant::whereKey($item->product_variant_id)
                    ->increment('stock', $item->quantity);
            }
        }
    }
}
