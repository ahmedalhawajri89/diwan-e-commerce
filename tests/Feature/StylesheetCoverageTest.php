<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;

/**
 * Guards the selective Bootstrap import in style.scss.
 *
 * style.scss no longer pulls `bootstrap/scss/bootstrap`. It names the parts the
 * app renders — grid, utilities, four components — which is worth about 100 KB
 * of CSS, and carries one risk: dropping a partial that something quietly
 * needed. A missing rule is invisible in a test that only checks status codes,
 * and often invisible on the page too, until the one state that used it.
 *
 * So this renders real pages and asserts every class in the markup still
 * resolves to a rule in the built stylesheet. Anything genuinely unstyled —
 * a JavaScript hook, an id-like class — is listed below with its reason.
 */

/** Classes that carry behaviour, not appearance, and are expected to have no rule. */
const UNSTYLED_BY_DESIGN = [
    // JavaScript hooks. The wishlist script finds its targets by these.
    'toggle-wishlist',
    'wishlist-counter-badge',
    'wishlist-header-icon',
    'remove-wishlist-btn',
    'move-to-cart-btn',
    'delete-form',
    'js-delete-image',

    // PHP Flasher injects and styles this itself, from its own stylesheet.
    'flasher-js',

    // Not a Bootstrap class and never was — `justify-items` is a grid property
    // Bootstrap does not generate a utility for, so this has never done
    // anything. Left in place rather than silently removed: deleting markup is
    // a separate decision from trimming a stylesheet.
    'justify-items-center',
];

function builtStylesheet(): ?string
{
    $files = glob(public_path('build/assets/style-*.css'));

    return $files ? file_get_contents($files[0]) : null;
}

function seedCatalogue(): void
{
    $category = Category::factory()->create(['name' => 'Living room', 'slug' => 'living-room']);

    // Enough rows to paginate, so the pager's own classes are covered.
    Product::factory()->count(14)->create(['category_id' => $category->id, 'status' => 'active'])
        ->each(fn ($p) => ProductVariant::factory()->create(['product_id' => $p->id, 'price' => 120]));

    // No ReviewFactory in the project, so this is built by hand. An approved
    // review matters here: the proof sections render nothing without one, and
    // their markup would go unchecked.
    Review::create([
        'product_id' => Product::first()->id,
        'user_id' => User::factory()->create()->id,
        'rating' => 5,
        'comment' => 'It fits the alcove exactly as the drawing said it would.',
        'status' => 'approved',
    ]);
}

it('leaves no class in the markup without a rule', function () {
    $css = builtStylesheet();

    if ($css === null) {
        $this->markTestSkipped('No built stylesheet. Run `npm run build`.');
    }

    seedCatalogue();

    $pages = [
        'home' => route('furnish.index'),
        'catalogue' => route('furnish.products'),
        'product' => route('furnish.product.show', Product::first()->slug),
        'lookbook' => route('furnish.lookbook'),
        'about' => route('furnish.about'),
        'contact' => route('furnish.contact'),
        'testimonials' => route('furnish.testimonials'),
        'login' => route('login'),
        'register' => route('register'),
    ];

    $unstyled = [];

    foreach ($pages as $name => $url) {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('/class="([^"]*)"/', $html, $matches);

        foreach ($matches[1] as $attr) {
            foreach (explode(' ', $attr) as $class) {
                $class = trim($class);

                if ($class === '' || ! preg_match('/^[A-Za-z][\w-]*$/', $class)) {
                    continue;
                }

                if (in_array($class, UNSTYLED_BY_DESIGN, true)) {
                    continue;
                }

                // A rule for the class, not merely the substring: `.col` must
                // not be satisfied by `.col-md-6`.
                if (! preg_match('/\.'.preg_quote($class, '/').'(?![\w-])/', $css)) {
                    $unstyled[$class][] = $name;
                }
            }
        }
    }

    $report = [];
    foreach ($unstyled as $class => $pages) {
        $report[] = ".{$class} (on ".implode(', ', array_unique($pages)).')';
    }

    expect($report)->toBe(
        [],
        "These classes are rendered but have no rule in the built stylesheet.\n"
        ."Usually that means style.scss stopped importing the Bootstrap partial\n"
        ."that defined them. Add the partial back, restyle the markup with the\n"
        .'app\'s own classes, or list it in UNSTYLED_BY_DESIGN with a reason.'
    );
});

it('does not import the whole of Bootstrap', function () {
    $scss = file_get_contents(base_path('resources/scss/furnish/style.scss'));

    expect(str_contains($scss, '@import "bootstrap/scss/bootstrap"'))->toBeFalse(
        'The full import pulls in accordions, carousels, toasts, popovers, cards, '
        .'alerts, navbars and the form suite that this project never renders.'
    );
});
