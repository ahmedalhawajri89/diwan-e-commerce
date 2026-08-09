<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = Cart::with(['items.product'])->where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            sweetalert()->addError('Your cart is empty!');
            return redirect()->route('cart.index');
        }

        return view('furnish.checkout', compact('cart'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:cod,credit_card',
            'card_name' => 'required_if:payment_method,credit_card|nullable|string|max:255',
            'card_number' => 'required_if:payment_method,credit_card|nullable|digits:16',
            'card_expiry' => 'required_if:payment_method,credit_card|nullable|string|size:5',
            'card_cvc' => 'required_if:payment_method,credit_card|nullable|digits_between:3,4',
        ], [
            'card_number.digits' => 'The card number must be exactly 16 digits.',
            'card_expiry.size' => 'The expiry date must be exactly 5 characters (e.g., 12/25).',
        ]);

        $cart = Cart::with(['items.product'])->where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            sweetalert()->addError('Your cart is empty!');
            return redirect()->route('furnish.products');
        }

        $totalPrice = 0;
        foreach ($cart->items as $item) {
            $totalPrice += ($item->price * $item->quantity);
        }

        DB::beginTransaction();

        try {
            // 1. Create Address
            $address = Address::create([
                'user_id' => auth()->id(),
                'country' => $request->country,
                'city' => $request->city,
                'street' => $request->street,
                // store phone in a profile or simply omit since address table doesn't have phone. 
                // Wait, if address table lacks phone, I will just append it to street for now.
            ]);
            
            // Append phone to street just to save it safely without migrations
            $address->street = $request->street . ' (Phone: ' . $request->phone . ')';
            $address->save();

            // 2. Determine Payment Status
            $paymentStatus = ($request->payment_method === 'credit_card') ? 'paid' : 'pending';

            // 3. Create Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_address_id' => $address->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
            ]);

            // 4. Create Order Items
            foreach ($cart->items as $item) {
                
                $img = $item->product ? $item->product->images->first() : null;
                $imgSrc = 'assets/images/product-img-1.jpg';
                if($img) {
                    $imgSrc = $img->image_url;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name_snapshot' => $item->product ? $item->product->name : 'Unknown Product',
                    'product_image_snapshot' => $imgSrc,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Clear Cart
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return redirect()->route('checkout.thankyou', $order->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            sweetalert()->addError('Failed to process order: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function thankyou($orderId)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($orderId);
        return view('furnish.thankyou', compact('order'));
    }
}
