<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;

/**
 * Every class in the rendered markup must have a rule in the built stylesheet.
 *
 * Tailwind writes only the utilities it finds in the source. A class assembled
 * from pieces in PHP or JavaScript ("text-" . $tone) is invisible to that scan,
 * and the element it was meant to style simply renders plain — in a test that
 * checks status codes, and often on the page too, until the one state that
 * used it. So this renders real pages in both languages and checks each class
 * against app-*.css.
 */

/** Classes that carry behaviour, not appearance, and are expected to have no rule. */
const UNSTYLED_BY_DESIGN = [
    // JavaScript hooks. The wishlist and admin scripts find their targets by these.
    'toggle-wishlist',
    'wishlist-counter-badge',
    'wishlist-header-icon',
    'remove-wishlist-btn',
    'move-to-cart-btn',
    'delete-form',
    'js-delete-image',
];

function builtStylesheet(): ?string
{
    $files = glob(public_path('build/assets/app-*.css'));

    return $files ? file_get_contents($files[0]) : null;
}

/** A class name as it appears in a selector: `w-[50%]` → `w-\[50\%\]`. */
function cssEscape(string $class): string
{
    return preg_replace('/[^A-Za-z0-9_-]/', '\\\\$0', $class);
}

function seedCatalogue(): void
{
    $category = Category::factory()->create(['name' => 'Living room', 'slug' => 'living-room']);

    // Enough rows to paginate, so the pager's own classes are covered.
    Product::factory()->count(14)->create(['category_id' => $category->id, 'status' => 'active'])
        ->each(fn ($p) => ProductVariant::factory()->create(['product_id' => $p->id, 'price' => 120]));

    // An approved review matters: the quote and the review list render
    // nothing without one, and their markup would go unchecked.
    Review::create([
        'product_id' => Product::first()->id,
        'user_id' => User::factory()->create()->id,
        'rating' => 5,
        'comment' => 'It fits the alcove exactly as the drawing said it would.',
        'status' => 'approved',
    ]);
}

it('builds a rule for every class in the markup', function () {
    $css = builtStylesheet();

    if ($css === null) {
        $this->markTestSkipped('No built stylesheet. Run `npm run build`.');
    }

    seedCatalogue();

    $pages = [
        'home' => route('furnish.index'),
        'catalogue' => route('furnish.products'),
        'category' => route('furnish.category', 'living-room'),
        'product' => route('furnish.product.show', Product::first()->slug),
        'lookbook' => route('furnish.lookbook'),
        'about' => route('furnish.about'),
        'contact' => route('furnish.contact'),
        'testimonials' => route('furnish.testimonials'),
        'policy' => route('furnish.page', 'shipping'),
        'login' => route('login'),
        'register' => route('register'),
        'missing' => '/no-such-address',
    ];

    $missing = [];

    foreach (['en', 'ar'] as $locale) {
        foreach ($pages as $name => $url) {
            $html = $this->withSession(['locale' => $locale])->get($url)->getContent();

            // Not `:class="…"` — that is an Alpine binding, and its value is a
            // property name, not a class.
            preg_match_all('/(?<![:\w-])class="([^"]*)"/', $html, $matches);

            foreach ($matches[1] as $attr) {
                // `[&[hidden]]:hidden` arrives as `&amp;` in the markup.
                foreach (preg_split('/\s+/', trim(html_entity_decode($attr))) as $class) {
                    if ($class === '' || in_array($class, UNSTYLED_BY_DESIGN, true)) {
                        continue;
                    }

                    if (! preg_match('/\.'.preg_quote(cssEscape($class), '/').'(?![\w-])/', $css)) {
                        $missing[$class][] = "{$name} ({$locale})";
                    }
                }
            }
        }
    }

    $report = [];
    foreach ($missing as $class => $where) {
        $report[] = ".{$class} (on ".implode(', ', array_unique($where)).')';
    }

    expect($report)->toBe(
        [],
        "These classes are rendered but have no rule in the built stylesheet.\n"
        ."Usually the class was assembled from pieces, which Tailwind cannot see —\n"
        ."write it out in full — or it is a JavaScript hook that belongs in\n"
        .'UNSTYLED_BY_DESIGN with a reason.'
    );
});
