<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;

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
Route::resource('products', ProductController::class)->except(['show']);
Route::delete('product-images/{id}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
Route::resource('orders', OrderController::class)->except(['create', 'store', 'destroy']);
Route::resource('users', UserController::class)->only(['index', 'destroy']);
Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
Route::resource('newsletters', NewsletterSubscriberController::class)->only(['index', 'destroy']);
