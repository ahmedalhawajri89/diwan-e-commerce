<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// The operator's notices: the page, the feed the bell polls, and reading them.
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

// A colour's stock set by hand; every change lands in the stock log.
Route::post('variants/{variant}/stock', [StockController::class, 'update'])->name('variants.stock');

// Several rows at once, from a list's selection. Registered before the
// resources so `bulk` is never read as an id.
Route::post('orders/bulk', [OrderController::class, 'bulk'])->name('orders.bulk');
Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
Route::post('reviews/bulk', [ReviewController::class, 'bulk'])->name('reviews.bulk');

// A list as CSV, with the same search, filter and sort as on screen.
Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::get('users/export', [UserController::class, 'export'])->name('users.export');

// CRUD Routes for Admin.
//
// Each resource is registered with exactly the actions its controller
// implements. `Route::resource` used to register `users.show`, `users.edit`
// and `users.update` against a controller that has neither, so any request to
// them threw BadMethodCallException; `categories.show` and `products.show`
// pointed at a stub and a missing view respectively. No view links to any of
// them, so narrowing the registration removes the 500s without removing
// anything the admin panel actually offers.
Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('products', ProductController::class);
Route::delete('product-images/{id}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
Route::resource('orders', OrderController::class)->except(['create', 'store', 'destroy']);
Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
Route::resource('newsletters', NewsletterSubscriberController::class)->only(['index', 'destroy']);

// Contact-form messages. These used to sit in routes/web.php under the plain
// `auth` group while carrying admin.* names, so any signed-in customer could
// read every message and delete them. They belong here, behind the admin
// middleware, with the rest of the panel.
Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::delete('contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
