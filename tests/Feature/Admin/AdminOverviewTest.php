<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\Overview;
use Carbon\CarbonImmutable;

/**
 * The admin overview: every figure from the rows that exist, compared only
 * with something real, and nothing where there is nothing.
 */
function overviewAdmin(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function placeOrder(float $total, string $status = 'pending', int $daysAgo = 0): Order
{
    $customer = User::factory()->create();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12']);

    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => $total,
        'status' => $status,
        'payment_status' => 'pending',
    ]);
    $order->forceFill(['created_at' => now()->subDays($daysAgo)])->save();

    return $order;
}

function livePiece(array $variant = [], array $product = []): Product
{
    $p = Product::factory()->create(array_merge(['category_id' => Category::factory()->create()->id, 'status' => 'active', 'name_ar' => 'قطعة'], $product));
    if ($variant !== []) {
        // Measured unless the test says otherwise.
        ProductVariant::factory()->create(array_merge(['product_id' => $p->id, 'width_cm' => 50, 'depth_cm' => 50, 'height_cm' => 50], $variant));
    }

    return $p;
}

it('counts sales in the window, leaving cancelled orders out', function () {
    placeOrder(100, 'pending', 1);
    placeOrder(300, 'delivered', 5);
    placeOrder(999, 'cancelled', 2);
    placeOrder(50, 'delivered', 40);   // before the 30-day window

    $f = (new Overview(30))->figures();

    expect($f['revenue']['now'])->toBe(400.0)
        ->and($f['orders']['now'])->toBe(2)
        ->and($f['average']['now'])->toBe(200.0)
        ->and($f['revenue']['before'])->toBe(50.0)
        ->and($f['revenue']['change'])->toBe(700);
});

it('reports no change, not a zero, when the period before had nothing', function () {
    placeOrder(100, 'pending', 1);

    $f = (new Overview(7))->figures();

    expect($f['revenue']['change'])->toBeNull()
        ->and(Overview::change(5, 0))->toBeNull()
        ->and(Overview::change(5, 5))->toBe(0);
});

it('has no average order when there are no orders', function () {
    expect((new Overview(30))->figures()['average']['now'])->toBeNull();
});

it('gives every day of the window a bar, sold or not', function () {
    placeOrder(80, 'pending', 2);
    placeOrder(20, 'pending', 2);

    $days = (new Overview(7))->daily();

    expect($days)->toHaveCount(7)
        ->and($days[4]['revenue'])->toBe(100.0)
        ->and($days[4]['orders'])->toBe(2)
        ->and($days->sum('orders'))->toBe(2);
});

it('lists what is waiting, from live pieces only', function () {
    placeOrder(100, 'pending');
    livePiece(['stock' => 2]);                                    // low
    livePiece(['stock' => 3]);                                    // low, at the line
    livePiece(['stock' => 4]);                                    // fine
    livePiece(['stock' => 1], ['status' => 'draft']);             // not live
    livePiece();                                                   // no variant: no price
    livePiece(['stock' => 9, 'width_cm' => null]);                // unmeasured
    livePiece(['stock' => 9], ['name_ar' => null]);               // untranslated

    $w = (new Overview)->waiting();

    expect($w['confirm']['count'])->toBe(1)
        ->and($w['low_stock']['count'])->toBe(2)
        ->and($w['unpriced']['count'])->toBe(1)
        ->and($w['unmeasured']['count'])->toBe(1)
        ->and($w['untranslated']['count'])->toBe(1);
});

it('stands every order at its stage, cancelled last', function () {
    placeOrder(1, 'pending');
    placeOrder(1, 'shipped');
    placeOrder(1, 'shipped');
    placeOrder(1, 'cancelled');

    expect((new Overview)->pipeline())
        ->toBe(['pending' => 1, 'processing' => 0, 'shipped' => 2, 'delivered' => 0, 'cancelled' => 1]);
});

it('offers only its own periods', function () {
    expect(Overview::window('7'))->toBe(7)
        ->and(Overview::window('90'))->toBe(90)
        ->and(Overview::window('999'))->toBe(30)
        ->and(Overview::window(null))->toBe(30);

    $o = new Overview(7, CarbonImmutable::parse('2026-10-06 15:00'));
    expect($o->from->toDateTimeString())->toBe('2026-09-30 00:00:00')
        ->and($o->previousFrom->toDateTimeString())->toBe('2026-09-23 00:00:00');
});

it('draws the overview in both languages, with nothing invented on an empty shop', function () {
    $admin = overviewAdmin();

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();
    expect($html)->toContain(__('admin.dashboard.chart_empty'))
        ->toContain(__('admin.dashboard.no_change'))
        ->not->toMatch('/\d+%/');

    placeOrder(250, 'pending', 1);

    $ar = $this->actingAs($admin)->withSession(['locale' => 'ar'])->get(route('admin.dashboard', ['days' => 7]))->assertOk()->getContent();
    $text = strip_tags(preg_replace('/<script\b.*?<\/script>/s', '', $ar));

    expect($ar)->toContain('<svg viewBox="0 0 70 100"')
        ->toContain(__('admin.dashboard.waiting.confirm', [], 'ar'))
        ->and($text)->not->toMatch('/\badmin\.[a-z_]+\.[a-z_.]+/');
});

it('opens the orders and pieces the overview points at', function () {
    $admin = overviewAdmin();
    $waiting = placeOrder(10, 'pending');
    $sent = placeOrder(10, 'shipped');
    $low = livePiece(['stock' => 1], ['name' => 'Nearly gone']);
    livePiece(['stock' => 20], ['name' => 'Plenty left']);

    $orders = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'pending']))->viewData('orders');
    expect($orders->pluck('id')->all())->toBe([$waiting->id]);

    // A status the column does not hold is no filter.
    expect($this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'lost']))->viewData('orders')->total())->toBe(2);

    $this->actingAs($admin)->get(route('admin.products.index', ['need' => 'low_stock']))
        ->assertSee('Nearly gone')
        ->assertDontSee('Plenty left')
        ->assertSee(__('admin.filter.clear'));
});

it('draws the rail folded on the first paint when the operator left it folded', function () {
    $admin = overviewAdmin();

    $open = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
    $folded = $this->actingAs($admin)->withUnencryptedCookie('admin_rail', 'folded')->get(route('admin.dashboard'))->getContent();

    expect($open)->toContain('x-data="adminShell"')->not->toMatch('/x-data="adminShell"[^>]*\sdata-folded[\s>]/')
        ->and($folded)->toMatch('/x-data="adminShell"[^>]*\sdata-folded[\s>]/');
});

it('keeps the account in the top bar, not in the rail', function () {
    $admin = overviewAdmin();
    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();

    preg_match('/<nav id="rail".*?<\/nav>/s', $html, $rail);
    preg_match('/<header class="sticky.*?<\/header>/s', $html, $bar);

    expect($rail[0] ?? '')->not->toContain(route('logout'))->not->toContain($admin->email)
        ->and($bar[0] ?? '')->toContain(route('logout'))->toContain(e($admin->email));
});
