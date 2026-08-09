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

// CRUD Routes for Admin
Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
Route::delete('product-images/{id}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
Route::resource('orders', OrderController::class)->except(['create', 'store', 'destroy']);
Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
Route::resource('newsletters', NewsletterSubscriberController::class)->only(['index', 'destroy']);
