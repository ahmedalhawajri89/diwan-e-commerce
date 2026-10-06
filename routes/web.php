<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FurnishController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomBoardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Localization
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// "My room" — the two numbers the fit system is computed against. Session
// backed, so a visitor can measure their room without registering first.
Route::post('/room', [RoomController::class, 'store'])->name('room.store');
Route::delete('/room', [RoomController::class, 'destroy'])->name('room.destroy');

// Furnish Routes
Route::get('/', [FurnishController::class, 'index'])->name('furnish.index');
Route::get('/about', [FurnishController::class, 'about'])->name('furnish.about');
Route::get('/products', [FurnishController::class, 'products'])->name('furnish.products');
Route::get('/lookbook', [FurnishController::class, 'lookbook'])->name('furnish.lookbook');
Route::get('/api/search', [FurnishController::class, 'search'])->name('api.search');
Route::get('/category/{slug}', [FurnishController::class, 'category'])->name('furnish.category');
Route::get('/product/{slug}', [FurnishController::class, 'show'])->name('furnish.product.show');
Route::get('/testimonials', [FurnishController::class, 'testimonials'])->name('furnish.testimonials');
// Registered before the `/{page}` catch-all, which would otherwise swallow
// both and answer a crawler with a 404 policy page.
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/{page}', [FurnishController::class, 'page'])
    ->whereIn('page', ['shipping', 'privacy', 'terms'])
    ->name('furnish.page');
Route::get('/contact', [FurnishController::class, 'contact'])->name('furnish.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Public Wishlist Route to fetch items securely
Route::get('/wishlist/dropdown', [WishlistController::class, 'getDropdownItems'])->name('wishlist.dropdown');
Route::post('/wishlist/guest-dropdown', [WishlistController::class, 'getGuestDropdownItems'])->name('wishlist.guest.dropdown');

// Auth Routes
Route::middleware('auth')->group(function () {
    // Reviews
    Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/thankyou/{order}', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

    // The customer's own order history. Until this existed, an order was
    // visible only on the thank-you page immediately after placing it.
    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderHistoryController::class, 'show'])->name('orders.show');

    // Saved pieces, grouped into named rooms — layer 4 of the fit system.
    Route::get('/wishlist', [RoomBoardController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomBoardController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomBoardController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomBoardController::class, 'destroy'])->name('rooms.destroy');
    Route::put('/saved/{item}/room', [RoomBoardController::class, 'assign'])->name('rooms.assign');

    // Wishlist Routes (Auth required for actions)
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->name('wishlist.sync');
});

require __DIR__.'/auth.php';

// An address no route claims. Without this the 404 is rendered outside the
// web middleware group — no session, so no locale and no signed-in visitor —
// and an Arabic reader who mistypes a link lands on an English page that has
// signed them out. The fallback runs inside the group.
Route::fallback(fn () => abort(404));
