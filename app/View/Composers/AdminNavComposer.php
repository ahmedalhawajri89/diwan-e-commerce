<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

/**
 * The live counts beside each row of the admin side-rail.
 *
 * Seven queries used to run from a `@php` block inside layouts/app.blade.php,
 * on every admin page.
 *
 * They are deliberately NOT cached. design.md forbids invented metrics, and a
 * stale count is an invented metric with extra steps: an operator who has just
 * deleted a product and still sees the old total has been told something that
 * is not true, about the exact thing they were just doing. The admin panel is
 * a handful of authenticated sessions, not the storefront — correctness is
 * worth more here than seven queries.
 *
 * Moving them out of the view is the win: they are now named, mockable, and
 * gone from the template.
 */
class AdminNavComposer
{
    public function compose(View $view): void
    {
        $view->with('counts', [
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::where('status', 'pending')->count(),
            'reviews' => Review::where('status', 'pending')->count(),
            'users' => User::count(),
            'messages' => Contact::count(),
            'newsletter' => NewsletterSubscriber::count(),
        ]);

        // The top bar's account menu: who, and the initials on its disc
        // (the first letter of the first two words of the name).
        $who = auth()->user();
        $view->with('who', $who);
        $view->with('initials', collect(preg_split('/\s+/u', trim($who?->name ?? '')))
            ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''));

        // The bell's count on first paint; the bell keeps it current after.
        $view->with('unreadAlerts', $who?->alerts()->whereNull('read_at')->count() ?? 0);

        // Folded to icons, as the operator last left it.
        $view->with('railFolded', request()->cookie('admin_rail') === 'folded');
    }
}
