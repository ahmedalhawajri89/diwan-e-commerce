<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Support\AdminList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /** The ways the list can be ordered, first is the default. */
    public const SORTS = ['newest', 'oldest', 'total_high', 'total_low'];

    public function index(Request $request)
    {
        [$orders, $status, $q, $sort, $chips] = $this->listing($request);

        $orders = $orders->paginate(15)->withQueryString();
        $sorts = AdminList::sorts(self::SORTS, $sort);

        return view('admin.orders.index', compact('orders', 'status', 'q', 'sort', 'chips', 'sorts'));
    }

    /**
     * The list as the address asks for it: searched, filtered to one stage
     * (anything not a status the column holds is no filter), and sorted. The
     * export reads the same, so a download is always what was on screen.
     */
    private function listing(Request $request): array
    {
        $status = AdminList::choice($request, 'status', [...Order::STAGES, 'cancelled']);
        $q = AdminList::term($request);
        $sort = AdminList::choice($request, 'sort', self::SORTS, self::SORTS[0]);

        $base = Order::query();

        if ($q !== null) {
            // "#000042", "000042" and "42" all mean order 42.
            $number = ltrim(ltrim($q, '#'), '0');
            $base->where(function ($w) use ($q, $number) {
                if ($number !== '' && ctype_digit($number)) {
                    $w->orWhere('id', (int) $number);
                }
                $w->orWhereHas('user', fn ($u) => AdminList::search($u, $q, ['name', 'email']))
                    ->orWhereHas('address', fn ($a) => AdminList::search($a, $q, ['phone', 'city']));
            });
        }

        $chips = AdminList::chips($base, 'status', [...Order::STAGES, 'cancelled'], $status, 'admin.orders.status_values');

        $orders = (clone $base)->with('user')
            ->when($status, fn ($w) => $w->where('status', $status))
            ->when($sort === 'newest', fn ($w) => $w->latest()->orderByDesc('id'))
            ->when($sort === 'oldest', fn ($w) => $w->oldest()->orderBy('id'))
            ->when($sort === 'total_high', fn ($w) => $w->orderByDesc('total_price'))
            ->when($sort === 'total_low', fn ($w) => $w->orderBy('total_price'));

        return [$orders, $status, $q, $sort, $chips];
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'address', 'payment', 'shippingMethod']);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
        ]);

        // Captured before the update: "did this change?" cannot be asked
        // afterwards, and re-saving a form without touching the dropdown would
        // otherwise send the customer a second "your order has shipped".
        $wasStatus = $order->status;

        $order->update($validated);

        if ($order->status !== $wasStatus) {
            // Cancelling returns the pieces to the shelf. Without this the
            // shop believes stock it still has was sold, and the count drifts
            // down by one order every time somebody changes their mind.
            //
            // Guarded on the *transition* rather than the current status: an
            // operator re-saving an already-cancelled order must not credit
            // the stock a second time.
            if ($order->status === 'cancelled') {
                CheckoutController::releaseStock($order->load('items'));
            }

            $this->notifyCustomer($order);
        }

        session()->flash('success', __('admin.flash.order_updated'));

        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Tell the customer their order moved, when the move is one they can act on.
     *
     * The lifecycle has always been in the database and the panel has always
     * been able to advance it. Nobody told the customer: an operator marked an
     * order shipped and the only person who could see that was the operator.
     *
     * Sent in the language the customer shopped in, recorded on the order at
     * checkout (`orders.locale`). Never the operator's: an operator who
     * switched the panel to Arabic to read a note must not thereby send an
     * Arabic email to an English-speaking customer. Orders placed before the
     * column existed fall back to the application's default.
     *
     * Failure is logged rather than raised: the status change is the real work
     * and it is already saved. An operator who sees no mail can resend; an
     * operator who sees a 500 does not know whether the status stuck.
     */
    private function notifyCustomer(Order $order): void
    {
        if (! in_array($order->status, OrderStatusChanged::NOTIFIABLE, true)) {
            return;
        }

        $customer = $order->user;

        if (! $customer?->email) {
            return;
        }

        try {
            Mail::to($customer->email)->send(new OrderStatusChanged(
                $order->load('user'),
                $order->status,
                $order->locale ?: config('app.locale'),
            ));
        } catch (\Throwable $e) {
            Log::error('Order status email failed', [
                'order_id' => $order->id,
                'status' => $order->status,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
