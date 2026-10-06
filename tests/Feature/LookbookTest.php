<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Each room lists pieces from its own category, and nothing is pinned onto
 * the photographs — they are not of these pieces.
 */
function lookbookCatalogue(): array
{
    $made = [];

    foreach (['living-room' => 'Living Room', 'office' => 'Office', 'dining-room' => 'Dining Room'] as $slug => $name) {
        $category = Category::factory()->create(['slug' => $slug, 'name' => $name]);
        $made[$slug] = Product::factory()->count(2)->create(['category_id' => $category->id, 'status' => 'active'])
            ->each(fn ($p) => ProductVariant::factory()->create(['product_id' => $p->id]));
    }

    return $made;
}

it('lists each room with pieces from its own category only', function () {
    $made = lookbookCatalogue();

    $looks = $this->get(route('furnish.lookbook'))->assertOk()->viewData('looks');

    expect($looks)->toHaveCount(3);

    foreach ($looks as $look) {
        $slug = $look['category']->slug;

        expect($look['pieces']->pluck('id')->sort()->values()->all())
            ->toBe($made[$slug]->pluck('id')->sort()->values()->all());
    }
});

it('pins nothing onto the photographs', function () {
    lookbookCatalogue();

    $html = $this->get(route('furnish.lookbook'))->getContent();

    expect($html)->not->toMatch('/inset-inline-start:\s*\d+%/');
});

it('leaves out a room whose category has nothing for sale', function () {
    Category::factory()->create(['slug' => 'living-room', 'name' => 'Living Room']);

    $this->get(route('furnish.lookbook'))
        ->assertOk()
        ->assertViewHas('looks', []);
});
