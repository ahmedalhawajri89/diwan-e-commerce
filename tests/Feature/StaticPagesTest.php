<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The category landing page and the three policy pages.
 *
 * The catalogue filter already narrowed by category, but `?categories[]=3` is
 * not a link anybody shares and not a URL a search engine keeps.
 */

it('serves a category at its own address', function () {
    $category = Category::factory()->create(['name' => 'Living room', 'slug' => 'living-room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 200]);

    $this->get(route('furnish.category', 'living-room'))
        ->assertOk()
        ->assertSee('Living room')
        ->assertSee($product->name);
});

it('404s an unknown category rather than showing an empty grid', function () {
    $this->get(route('furnish.category', 'no-such-category'))->assertNotFound();
});

it('shows only active products in a category', function () {
    $category = Category::factory()->create(['slug' => 'office']);
    $live = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => 'Live desk']);
    $draft = Product::factory()->create(['category_id' => $category->id, 'status' => 'draft', 'name' => 'Draft desk']);
    ProductVariant::factory()->create(['product_id' => $live->id, 'price' => 100]);
    ProductVariant::factory()->create(['product_id' => $draft->id, 'price' => 100]);

    $this->get(route('furnish.category', 'office'))
        ->assertSee('Live desk')
        ->assertDontSee('Draft desk');
});

it('serves the three policy pages', function () {
    foreach (['shipping', 'privacy', 'terms'] as $page) {
        $this->get(route('furnish.page', $page))
            ->assertOk()
            ->assertSee(__("diwan.pages.{$page}.title"));
    }
});

it('does not let the policy route swallow every url', function () {
    // The route is /{page}, so it must be constrained or it shadows the whole
    // site. Anything not in the whitelist has to fall through to a 404.
    $this->get('/anything-else')->assertNotFound();
    $this->get('/admin')->assertNotFound();
});

it('says plainly which clauses are still awaiting a real figure', function () {
    // A policy with an invented returns window is worse than one that admits
    // the number is not settled.
    $this->get(route('furnish.page', 'shipping'))
        ->assertSee(__('diwan.pages.awaiting'));
});

it('states that no card details are handled', function () {
    // The site takes cash on delivery because no gateway exists. The privacy
    // page has to say the true thing, not the usual boilerplate.
    $this->get(route('furnish.page', 'privacy'))
        ->assertSee(__('diwan.pages.privacy.s4_body'));
});
