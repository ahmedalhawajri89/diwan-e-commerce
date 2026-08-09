<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FurnishController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

//Furnish Routes 
Route::get('/', [FurnishController::class, 'index'])->name('furnish.index');
Route::get('/about', [FurnishController::class, 'about'])->name('furnish.about');
Route::get('/products', [FurnishController::class, 'products'])->name('furnish.products');
Route::get('/lookbook', [FurnishController::class, 'lookbook'])->name('furnish.lookbook');
Route::get('/api/search', [FurnishController::class, 'search'])->name('api.search');
Route::get('/product/{slug}', [FurnishController::class, 'show'])->name('furnish.product.show');
Route::get('/testimonials', [FurnishController::class, 'testimonials'])->name('furnish.testimonials');
Route::get('/contact', [FurnishController::class, 'contact'])->name('furnish.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Public Wishlist Route to fetch items securely
Route::get('/wishlist/dropdown', [WishlistController::class, 'getDropdownItems'])->name('wishlist.dropdown');
Route::post('/wishlist/guest-dropdown', [WishlistController::class, 'getGuestDropdownItems'])->name('wishlist.guest.dropdown');

//Auth Routes 
Route::middleware('auth')->group(function () {
    // Reviews
    Route::post('/product/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    //Profile Routes
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

    // Admin Contact Messages Routes
    Route::get('/contacts', [AdminContactController::class, 'index'])->name('admin.contacts.index');
    Route::delete('/contacts/{id}', [AdminContactController::class, 'destroy'])->name('admin.contacts.destroy');

    // Wishlist Routes (Auth required for actions)
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->name('wishlist.sync');
});


require __DIR__ . '/auth.php';
