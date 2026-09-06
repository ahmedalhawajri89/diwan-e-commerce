<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
