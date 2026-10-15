<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Support\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Returns: what customers asked to send back and why, the shop's answer,
 * and the moment the pieces are back, which returns them to stock. Each
 * step goes on the order's history.
 */
class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ReturnRequest::STATUSES, true) ? $request->query('status') : null;

        $returns = ReturnRequest::with(['order.items', 'user'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $counts = ReturnRequest::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.returns.index', compact('returns', 'status', 'counts'));
    }

    public function approve(ReturnRequest $returnRequest)
    {
        return $this->decide($returnRequest, 'approved');
    }

    public function reject(ReturnRequest $returnRequest)
    {
        return $this->decide($returnRequest, 'rejected');
    }

    /** The pieces are back: into stock, once. */
    public function receive(ReturnRequest $returnRequest)
    {
        abort_unless($returnRequest->status === 'approved', 409);

        DB::transaction(function () use ($returnRequest) {
            $order = $returnRequest->order->load('items');
            foreach ($order->items as $item) {
                // Under a lock, as a sale takes it: the shelf gains the piece,
                // then the log says so (Stock::record only writes the line).
                $variant = $item->product_variant_id
                    ? ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first()
                    : null;
                if ($variant) {
                    $variant->increment('stock', (int) $item->quantity);
                    Stock::record($variant, (int) $item->quantity, 'return', $order->id);
                }
            }
            $returnRequest->update(['status' => 'received', 'received_at' => now()]);
            $order->log('return', 'approved', 'received');
        });

        return back()->with('success', __('admin.returns.received'));
    }

    private function decide(ReturnRequest $returnRequest, string $status)
    {
        abort_unless($returnRequest->status === 'requested', 409);

        $returnRequest->update(['status' => $status, 'decided_at' => now()]);
        $returnRequest->order->log('return', 'requested', $status);

        return back()->with('success', __('admin.returns.'.$status));
    }
}
