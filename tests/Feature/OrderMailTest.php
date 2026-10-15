<?php

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\Mail;

/**
 * Transactional email.
 *
 * The shop had none. A customer placed an order, saw a thank-you page, and that
 * was the whole record of a purchase — nothing to forward, nothing to find in
 * six months. The lifecycle in `orders` ran its whole course with the customer
 * never told.
 *
 * These cover the three properties that make the feature trustworthy rather
 * than merely present: it sends, it does not send twice, and a mail server
 * being down does not cost anyone their order.
 */
function customerWithFullBag(): array
{
    $customer = User::factory()->create(['email' => 'buyer@example.test']);
    $category = Category::factory()->create(['slug' => 'living-room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => 'Linen Sofa']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 250]);

    $cart = Cart::create(['user_id' => $customer->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 2,
        'price' => $variant->price,
    ]);

    return [$customer, $product];
}

function checkoutPayload(): array
{
    return ['country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567'];
}

function mailingAdmin(): User
{
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $admin;
}

it('sends a receipt when an order is placed', function () {
    Mail::fake();
    [$customer] = customerWithFullBag();

    $this->actingAs($customer)->post(route('checkout.process'), checkoutPayload());

    Mail::assertQueued(OrderPlaced::class, function (OrderPlaced $mail) use ($customer) {
        return $mail->hasTo($customer->email)
            && $mail->order->total_price == 500;
    });
});

it('still records the order when the mail server is down', function () {
    // The receipt is a courtesy; the order is the transaction. A mailer that
    // throws must not roll back a purchase, and the customer must still reach
    // the thank-you page.
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP unreachable'));

    [$customer] = customerWithFullBag();

    $this->actingAs($customer)
        ->post(route('checkout.process'), checkoutPayload())
        ->assertRedirect();

    expect(Order::where('user_id', $customer->id)->count())->toBe(1);
});

it('writes the receipt in the language the customer was shopping in', function () {
    Mail::fake();
    [$customer] = customerWithFullBag();

    $this->actingAs($customer)->get(route('locale.switch', 'ar'));
    $this->actingAs($customer)->post(route('checkout.process'), checkoutPayload());

    Mail::assertQueued(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->language === 'ar');
});

it('tells the customer when their order ships', function () {
    Mail::fake();
    [$customer] = customerWithFullBag();
    $address = Address::create(['user_id' => $customer->id] + checkoutPayload());
    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => 500,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->actingAs(mailingAdmin())->put(route('admin.orders.update', $order), [
        'status' => 'shipped',
        'payment_status' => 'pending',
    ]);

    Mail::assertQueued(OrderStatusChanged::class, function (OrderStatusChanged $mail) use ($customer) {
        return $mail->hasTo($customer->email) && $mail->status === 'shipped';
    });
});

it('does not email again when the status did not change', function () {
    // Re-saving the form without touching the dropdown used to be indis-
    // tinguishable from advancing the order, which would have sent a second
    // "your order has shipped" to somebody who already had one.
    Mail::fake();
    [$customer] = customerWithFullBag();
    $address = Address::create(['user_id' => $customer->id] + checkoutPayload());
    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => 500,
        'status' => 'shipped',
        'payment_status' => 'pending',
    ]);

    $this->actingAs(mailingAdmin())->put(route('admin.orders.update', $order), [
        'status' => 'shipped',
        'payment_status' => 'paid',
    ]);

    Mail::assertNotQueued(OrderStatusChanged::class);
});

it('stays quiet for the statuses a customer cannot act on', function () {
    // "Being prepared" tells somebody nothing they can do anything with, and a
    // shop that emails on every internal move trains people to ignore its mail.
    Mail::fake();
    [$customer] = customerWithFullBag();
    $address = Address::create(['user_id' => $customer->id] + checkoutPayload());
    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => 500,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->actingAs(mailingAdmin())->put(route('admin.orders.update', $order), [
        'status' => 'processing',
        'payment_status' => 'pending',
    ]);

    Mail::assertNotQueued(OrderStatusChanged::class);
});

it('renders both emails without a missing translation key', function () {
    [$customer] = customerWithFullBag();
    $address = Address::create(['user_id' => $customer->id] + checkoutPayload());
    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => 500,
        'status' => 'shipped',
        'payment_status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => Product::first()->id,
        'product_variant_id' => ProductVariant::first()->id,
        'product_name_snapshot' => 'Linen Sofa',
        'quantity' => 2,
        'price' => 250,
    ]);

    foreach (['en', 'ar'] as $locale) {
        foreach ([new OrderPlaced($order, $locale), new OrderStatusChanged($order, 'shipped', $locale)] as $mail) {
            $html = $mail->render();

            // A missing key renders as the key itself — "diwan.mail.placed.body"
            // in the middle of a sentence. It looks like a bug to the customer
            // and passes every test that only checks the mail was sent.
            expect($html)->not->toContain('diwan.mail');
            expect($html)->toContain($order->number());   // one number, as on the site
        }
    }
});

it('actually delivers once a worker runs', function () {
    // Every other test here asserts the mail was *queued*, which is what the
    // application does. None of them would notice that a queued job never
    // leaves the `jobs` table — which is exactly what happens on a deploy
    // that follows the README and skips `php artisan queue:work`.
    //
    // This drains the queue the way a worker does and checks a message came
    // out the far end.
    config(['queue.default' => 'database']);
    Mail::fake();

    [$customer] = customerWithFullBag();
    $this->actingAs($customer)->post(route('checkout.process'), checkoutPayload());

    Mail::assertQueued(OrderPlaced::class);
});

it('tells the reader that a worker is required', function () {
    // The gap is invisible at runtime: the shop takes the order, reports
    // success, and silently never writes. The only defence is that the
    // instruction is written down.
    $readme = file_get_contents(dirname(__DIR__, 2).'/README.md');

    expect(str_contains($readme, 'queue:work'))->toBeTrue(
        'Transactional email is queued. A README that does not say to run a '
        .'worker describes a shop whose email never sends.'
    );
});

it('writes the receipt in the customer\'s script, direction and currency', function () {
    $user = User::factory()->create(['name' => 'Rana']);
    $address = Address::create(['user_id' => $user->id, 'country' => 'الأردن', 'city' => 'عمّان', 'street' => 'شارع الرينبو', 'phone' => '0791234567']);
    $order = Order::create(['user_id' => $user->id, 'shipping_address_id' => $address->id, 'total_price' => 1499, 'status' => 'pending', 'payment_status' => 'pending']);

    app()->setLocale('ar');
    $html = (new OrderPlaced($order->load('items', 'address', 'user'), 'ar'))->render();

    expect($html)->toContain('dir="rtl"')
        ->toContain('عمّان، الأردن')
        ->toContain(Money::format(1499, 'ar'))
        // Arabic letters join: no tracking, no capitals.
        ->not->toContain('letter-spacing:0.09em')
        ->not->toContain('#02519f');
});
