<?php

namespace App\Support;

use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\WishlistItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Every figure on the admin overview, read from the rows that exist.
 *
 * A window is the last N days including today. Each figure is compared with
 * the N days before it; where that earlier window has nothing, there is no
 * change to report and `change()` says so with null, never with a percentage
 * of zero.
 *
 * Cancelled orders are not sales: they count nowhere except the pipeline.
 */
class Overview
{
    /** The windows the overview offers, in days. */
    public const WINDOWS = [7, 30, 90];

    /** A variant with this many or fewer left is running low. The shop's number. */
    public const LOW_STOCK = 3;

    public readonly CarbonImmutable $from;

    public readonly CarbonImmutable $to;

    public readonly CarbonImmutable $previousFrom;

    public function __construct(public readonly int $days = 30, ?CarbonImmutable $now = null)
    {
        $now ??= CarbonImmutable::now();

        $this->to = $now->endOfDay();
        $this->from = $now->subDays($days - 1)->startOfDay();
        $this->previousFrom = $this->from->subDays($days);
    }

    /** A window the overview offers, or the default for anything else. */
    public static function window(mixed $days): int
    {
        return in_array((int) $days, self::WINDOWS, true) ? (int) $days : 30;
    }

    /* ── What is waiting ─────────────────────────────────────────────── */

    /**
     * Work that needs a person, in the order it should be done. Each entry
     * carries where to go to do it; entries at zero are kept so the operator
     * sees that they are clear.
     *
     * @return array<string, array{count: int, route: string, params: array}>
     */
    public function waiting(): array
    {
        $live = fn ($q) => $q->where('status', 'active');

        return [
            'confirm' => [
                'count' => Order::where('status', 'pending')->count(),
                'route' => 'admin.orders.index',
                'params' => ['status' => 'pending'],
            ],
            'low_stock' => [
                'count' => ProductVariant::where('stock', '<=', self::LOW_STOCK)->whereHas('product', $live)->count(),
                'route' => 'admin.products.index',
                'params' => ['need' => 'low_stock'],
            ],
            'unpriced' => [
                'count' => Product::where('status', 'active')->doesntHave('variants')->count(),
                'route' => 'admin.products.index',
                'params' => ['need' => 'unpriced'],
            ],
            'unmeasured' => [
                'count' => Product::where('status', 'active')->whereHas('variants', fn ($q) => $q
                    ->where(fn ($q) => $q->whereNull('width_cm')->orWhereNull('depth_cm')->orWhereNull('height_cm')))->count(),
                'route' => 'admin.products.index',
                'params' => ['need' => 'unmeasured'],
            ],
            'untranslated' => [
                'count' => Product::where('status', 'active')->where(fn ($q) => $q->whereNull('name_ar')->orWhere('name_ar', ''))->count(),
                'route' => 'admin.products.index',
                'params' => ['need' => 'untranslated'],
            ],
            'reviews' => [
                'count' => Review::where('status', 'pending')->count(),
                'route' => 'admin.reviews.index',
                'params' => [],
            ],
            'messages' => [
                'count' => Contact::where('created_at', '>=', CarbonImmutable::now()->subDays(6)->startOfDay())->count(),
                'route' => 'admin.contacts.index',
                'params' => [],
            ],
        ];
    }

    /* ── The window's figures ────────────────────────────────────────── */

    /**
     * Revenue, orders, average order and new customers, each with the same
     * figure for the window before and the change between them.
     *
     * @return array<string, array{now: float|int|null, before: float|int|null, change: int|null}>
     */
    public function figures(): array
    {
        [$revenue, $orders] = $this->sales($this->from, $this->to);
        [$revenueBefore, $ordersBefore] = $this->sales($this->previousFrom, $this->from->subSecond());

        $figures = [
            'revenue' => ['now' => $revenue, 'before' => $revenueBefore],
            'orders' => ['now' => $orders, 'before' => $ordersBefore],
            // No orders, no average: not zero, simply nothing to divide.
            'average' => [
                'now' => $orders > 0 ? round($revenue / $orders, 2) : null,
                'before' => $ordersBefore > 0 ? round($revenueBefore / $ordersBefore, 2) : null,
            ],
            'customers' => [
                'now' => $this->newCustomers($this->from, $this->to),
                'before' => $this->newCustomers($this->previousFrom, $this->from->subSecond()),
            ],
        ];

        return array_map(fn ($f) => $f + ['change' => self::change($f['now'], $f['before'])], $figures);
    }

    /** The change from before to now, in percent; null where before was nothing. */
    public static function change(float|int|null $now, float|int|null $before): ?int
    {
        if (! $before || $now === null) {
            return null;
        }

        return (int) round(($now - $before) / $before * 100);
    }

    /**
     * One entry per day of the window, oldest first, days without an order
     * included at zero so the chart's spacing is the calendar's.
     *
     * @return Collection<int, array{date: CarbonImmutable, revenue: float, orders: int, customers: int}>
     */
    public function daily(): Collection
    {
        $rows = $this->sold($this->from, $this->to)
            ->selectRaw('DATE(created_at) as day, SUM(total_price) as revenue, COUNT(*) as orders')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $joined = User::whereBetween('created_at', [$this->from, $this->to])
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as n')
            ->groupBy('day')
            ->pluck('n', 'day');

        return collect(range(0, $this->days - 1))->map(function ($i) use ($rows, $joined) {
            $date = $this->from->addDays($i);
            $row = $rows->get($date->toDateString());

            return [
                'date' => $date,
                'revenue' => (float) ($row->revenue ?? 0),
                'orders' => (int) ($row->orders ?? 0),
                'customers' => (int) ($joined[$date->toDateString()] ?? 0),
            ];
        });
    }

    /**
     * How many orders stand at each stage right now, cancelled last.
     *
     * @return array<string, int>
     */
    public function pipeline(): array
    {
        $counts = Order::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return collect([...Order::STAGES, 'cancelled'])
            ->mapWithKeys(fn ($stage) => [$stage => (int) ($counts[$stage] ?? 0)])
            ->all();
    }

    /**
     * Whether the way-in check is working, in the customers' own answers:
     * how each delivery went, and how many orders were cancelled because a
     * piece would not get in. All time: the answers come slowly, and a
     * window would mostly show nothing. Every count is a row, none estimated.
     *
     * @return array{easy:int, hard:int, failed:int, cancelled:int}
     */
    public function wayIn(): array
    {
        $said = Order::whereNotNull('delivery_feedback')->selectRaw('delivery_feedback, COUNT(*) as n')
            ->groupBy('delivery_feedback')->pluck('n', 'delivery_feedback');

        return collect(Order::FEEDBACK)->mapWithKeys(fn ($answer) => [$answer => (int) ($said[$answer] ?? 0)])->all()
            + ['cancelled' => Order::where('cancel_reason', 'route')->count()];
    }

    /**
     * Returns asked for, by reason, all time. Read beside the way-in answers:
     * whether returns for size, the ones the fit and passage checks are for,
     * are few beside the rest. Every count is a row.
     *
     * @return array<string, int>
     */
    public function returnReasons(): array
    {
        $counts = ReturnRequest::selectRaw('reason, COUNT(*) as n')->groupBy('reason')->pluck('n', 'reason');

        return collect(ReturnRequest::REASONS)->mapWithKeys(fn ($r) => [$r => (int) ($counts[$r] ?? 0)])->all();
    }

    /**
     * The pieces sold most in the window, by quantity, with what they brought in.
     *
     * @return Collection<int, object>
     */
    public function bestSellers(int $limit = 5): Collection
    {
        return OrderItem::query()
            ->whereIn('order_id', $this->sold($this->from, $this->to)->select('id'))
            ->selectRaw('product_id, MAX(product_name_snapshot) as name, SUM(quantity) as quantity, SUM(quantity * price) as revenue')
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * The pieces most often saved to a list, of all time: a list is kept, not
     * a moment.
     *
     * @return Collection<int, Product>
     */
    public function mostSaved(int $limit = 5): Collection
    {
        $counts = WishlistItem::selectRaw('product_id, COUNT(*) as n')
            ->groupBy('product_id')
            ->orderByDesc('n')
            ->limit($limit)
            ->pluck('n', 'product_id');

        return Product::whereIn('id', $counts->keys())->get()
            ->each(fn ($p) => $p->setAttribute('saves', (int) $counts[$p->id]))
            ->sortByDesc('saves')
            ->values();
    }

    /**
     * Today so far, from midnight: what sold, how many orders, how many
     * calls are still to make (of any day), and who joined.
     *
     * @return array{revenue: float, orders: int, pending: int, customers: int}
     */
    public function today(): array
    {
        $now = CarbonImmutable::now();
        [$revenue, $orders] = $this->sales($now->startOfDay(), $now->endOfDay());

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'pending' => Order::where('status', 'pending')->count(),
            'customers' => $this->newCustomers($now->startOfDay(), $now->endOfDay()),
        ];
    }

    /** Morning, afternoon or evening, by the shop's clock. */
    public static function greeting(?CarbonImmutable $now = null): string
    {
        $hour = ($now ?? CarbonImmutable::now())->hour;

        return match (true) {
            $hour < 12 => 'morning',
            $hour < 18 => 'afternoon',
            default => 'evening',
        };
    }

    /** The order that has waited longest for its confirming call, if any. */
    public function oldestPending(): ?Order
    {
        return Order::with('user')->where('status', 'pending')->oldest()->first();
    }

    /** @return Collection<int, Order> */
    public function recentOrders(int $limit = 6): Collection
    {
        return Order::with('user')->latest()->take($limit)->get();
    }

    /* ── Helpers ─────────────────────────────────────────────────────── */

    private function sold(CarbonImmutable $from, CarbonImmutable $to)
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to]);
    }

    /** @return array{0: float, 1: int} */
    private function sales(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = $this->sold($from, $to)->selectRaw('COALESCE(SUM(total_price), 0) as revenue, COUNT(*) as orders')->first();

        return [(float) $row->revenue, (int) $row->orders];
    }

    private function newCustomers(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return User::whereBetween('created_at', [$from, $to])
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->count();
    }
}
