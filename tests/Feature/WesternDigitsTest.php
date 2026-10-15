<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

/**
 * An Arabic keyboard types ٣٥٠ for 350. The integer rule refused it, and a
 * phone number was stored in a form a dialler may not take (D20).
 */
it('reads an Arabic-typed room as numbers', function () {
    $this->post(route('room.store'), ['width' => '٣٥٠', 'length' => '٤٨٠'])
        ->assertSessionHasNoErrors();

    expect(session('diwan.room'))->toMatchArray(['width' => 350, 'length' => 480]);
});

it('stores a phone typed in Arabic digits in the digits a courier dials', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10, 'stock' => 5]);
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 10]);

    $this->actingAs($user)->post(route('checkout.process'), [
        'country' => 'الأردن', 'city' => 'عمّان', 'street' => 'شارع ٥', 'phone' => '٠٧٩١٢٣٤٥٦٧',
    ]);

    expect($user->addresses()->sole()->phone)->toBe('0791234567');
});

it('leaves a password exactly as typed', function () {
    $user = User::factory()->create(['password' => bcrypt('سر١٢٣')]);

    // Converted, '١٢٣' would become '123' and this sign-in would fail.
    $this->post(route('login'), ['email' => $user->email, 'password' => 'سر١٢٣'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});
