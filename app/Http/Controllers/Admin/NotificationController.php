<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The operator's notices: the full list, the feed the bell polls, and
 * marking them read. Every query is scoped to the signed-in administrator's
 * own rows; one never sees, or reads, another's.
 */
class NotificationController extends Controller
{
    /** The bell's dropdown holds this many. */
    public const DROPDOWN = 8;

    public function index(Request $request): View
    {
        $unreadOnly = $request->query('show') === 'unread';

        $notifications = $request->user()->alerts()
            ->when($unreadOnly, fn ($q) => $q->whereNull('read_at'))
            ->paginate(25)
            ->withQueryString();

        // Grouped by day for the page: today, yesterday, then dates.
        $days = $notifications->getCollection()->groupBy(fn ($n) => $n->created_at->toDateString());

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'days' => $days,
            'unreadOnly' => $unreadOnly,
            'unread' => $request->user()->alerts()->whereNull('read_at')->count(),
        ]);
    }

    /**
     * What the bell needs, as JSON. With `after`, only what arrived since
     * that id (the poll); without it, the latest few (the first open). The
     * unread count and the orders waiting are always current, so the badge
     * and the rail never drift from the truth.
     */
    public function feed(Request $request): JsonResponse
    {
        $after = $request->integer('after');
        $alerts = $request->user()->alerts();

        $items = (clone $alerts)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->take(self::DROPDOWN)
            ->get();

        return response()->json([
            'unread' => (clone $alerts)->whereNull('read_at')->count(),
            'latest' => (int) ((clone $alerts)->max('id') ?? 0),
            'items' => $items->map->toFeed()->values(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
        ]);
    }

    /** Read one, then go to what it is about. */
    public function read(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        abort_unless($this->owns($request, $notification), 404);

        $notification->forceFill(['read_at' => $notification->read_at ?? now()])->save();

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : redirect()->to($notification->url());
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->alerts()->whereNull('read_at')->update(['read_at' => now()]);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'unread' => 0])
            : back()->with('success', __('admin.alerts_page.all_read'));
    }

    private function owns(Request $request, Notification $notification): bool
    {
        return $notification->notifiable_type === $request->user()::class
            && (int) $notification->notifiable_id === (int) $request->user()->getKey();
    }
}
