<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'city'    => ['required', 'string', 'max:100'],
            'street'  => ['required', 'string', 'max:255'],
            'phone'   => ['required', 'string', 'max:32'],
        ], [], [
            'country' => __('diwan.checkout.country'),
            'city'    => __('diwan.checkout.city'),
            'street'  => __('diwan.checkout.street'),
            'phone'   => __('diwan.checkout.phone'),
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
            // The phone now has its own column. It used to be appended to the
            // street — "Rainbow Street 12 (Phone: 0791234567)" — which corrupted
            // the row and would have printed on the delivery label.
            $address = Address::create([
                'user_id' => auth()->id(),
                'country' => $validated['country'],
                'city'    => $validated['city'],
                'street'  => $validated['street'],
                'phone'   => $validated['phone'],
            ]);

            // Nothing has been paid, so nothing claims to have been. An operator
            // marks the order paid from the admin panel when the money actually
            // arrives; that is the only place the truth is known.
            $order = Order::create([
                'user_id'             => auth()->id(),
                'shipping_address_id' => $address->id,
                'total_price'         => $totalPrice,
                'status'              => 'pending',
                'payment_status'      => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'             => $item->product_id,
                    'product_variant_id'     => $item->product_variant_id,
                    // Snapshots, so the confirmation and the order history stay
                    // true if the piece is later renamed or delisted.
                    'product_name_snapshot'  => $item->product?->name ?? __('diwan.common.error'),
                    'product_image_snapshot' => $item->product?->primaryImage()?->image_url,
                    'quantity'               => $item->quantity,
                    'price'                  => $item->price,
                ]);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

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
}
