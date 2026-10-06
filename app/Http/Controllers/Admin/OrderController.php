<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Support\AdminList;
use App\Support\CsvExport;
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

        // The stages an order can be moved to, for the selection's bar.
        $bulkOptions = collect([...Order::STAGES, 'cancelled'])
            ->mapWithKeys(fn ($s) => [$s => __('admin.bulk.to', ['stage' => __('admin.orders.status_values.'.$s)])])
            ->all();

        return view('admin.orders.index', compact('orders', 'status', 'q', 'sort', 'chips', 'sorts', 'bulkOptions'));
    }

    /** The list as on screen, every page of it, as CSV. */
    public function export(Request $request)
    {
        [$orders] = $this->listing($request);

        return CsvExport::download('orders', $orders->with(['user', 'address'])->withCount('items'), [
            __('admin.orders.number') => fn ($o) => $o->number(),
            __('admin.orders.placed') => fn ($o) => $o->created_at->format('Y-m-d H:i'),
            __('admin.orders.customer') => fn ($o) => $o->user?->name,
            __('admin.users.email') => fn ($o) => $o->user?->email,
            __('admin.csv.phone') => fn ($o) => $o->address?->phone,
            __('admin.csv.city') => fn ($o) => $o->address?->city,
            __('admin.csv.items') => fn ($o) => $o->items_count,
            __('admin.orders.total') => fn ($o) => number_format($o->total_price, 2, '.', ''),
            __('admin.orders.status') => fn ($o) => __('admin.orders.status_values.'.$o->status),
            __('admin.orders.payment') => fn ($o) => __('admin.orders.payment_values.'.$o->payment_status),
        ]);
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

        $order->update(['payment_status' => $validated['payment_status']]);
        $this->applyStatus($order, $validated['status']);

        session()->flash('success', __('admin.flash.order_updated'));

        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Move several orders to one stage at once. Each goes through the same
     * applyStatus() as a single order: the same e-mail, the same stock
     * returned on cancelling, and nothing for an order already there.
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|max:200',
            'ids.*' => 'integer',
            'action' => 'required|string|in:'.implode(',', [...Order::STAGES, 'cancelled']),
        ]);

        $moved = 0;
        foreach (Order::whereIn('id', $validated['ids'])->get() as $order) {
            $moved += (int) $this->applyStatus($order, $validated['action']);
        }

        session()->flash('success', trans_choice('admin.bulk.done', $moved, ['count' => $moved]));

        return back();
    }

    /**
     * Set an order's stage, and do what the change means. Returns whether it
     * changed.
     *
     * Asked of the transition, not the value: re-saving an order without
     * touching its stage must not send the customer a second "your order has
     * shipped", and re-cancelling must not put its stock back twice.
     */
    private function applyStatus(Order $order, string $status): bool
    {
        if ($order->status === $status) {
            return false;
        }

        $order->update(['status' => $status]);

        // Cancelling returns the pieces to the shelf. Without this the shop
        // believes stock it still has was sold, and the count drifts down by
        // one order every time somebody changes their mind.
        if ($status === 'cancelled') {
            CheckoutController::releaseStock($order->load('items'));
        }

        $this->notifyCustomer($order);

        return true;
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
