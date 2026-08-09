@extends('layouts.furnish')

@section('title', 'Shopping Cart')

@section('content')
<div class="py-5 py-lg-8" style="background-color: var(--bg-soft); min-height: 80vh;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="display-5 mb-2 fw-bold letter-spacing-tight">Your Cart</h1>
                <p class="text-muted lead">Review your items before proceeding to checkout.</p>
            </div>
        </div>

        @if($cart && $cart->items->count() > 0)
        <div class="row g-5">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card card-modern border-0 mb-4 p-2">
                    <div class="card-body p-0">
                        <div class="table-responsive overflow-visible">
                            <table class="table align-middle mb-0 border-transparent">
                                <thead class="bg-transparent text-muted text-xs text-uppercase letter-spacing-wide fw-bold">
                                    <tr>
                                        <th scope="col" class="ps-4 py-3 border-bottom border-soft">Product Details</th>
                                        <th scope="col" class="py-3 border-bottom border-soft text-center">Quantity</th>
                                        <th scope="col" class="py-3 border-bottom border-soft text-center">Price</th>
                                        <th scope="col" class="py-3 border-bottom border-soft text-center">Total</th>
                                        <th scope="col" class="pe-4 py-3 border-bottom border-soft text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach($cart->items as $item)
                                        @php $subtotal += ($item->price * $item->quantity); @endphp
                                        <tr>
                                            <td class="ps-4 py-4 border-bottom-0">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $img = $item->product ? $item->product->images->first() : null;
                                                        $imgSrc = asset('assets/images/product-img-1.jpg');
                                                        if($img) {
                                                            if (str_starts_with($img->image_url, 'http')) {
                                                                $imgSrc = $img->image_url;
                                                            } elseif (str_starts_with($img->image_url, 'assets/')) {
                                                                $imgSrc = asset($img->image_url);
                                                            } else {
                                                                $imgSrc = asset('storage/' . $img->image_url);
                                                            }
                                                        }
                                                    @endphp
                                                    <img src="{{ $imgSrc }}" alt="{{ $item->product ? $item->product->name : 'Product' }}" class="img-fluid rounded-3 shadow-sm me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark">
                                                            <a href="{{ $item->product ? route('furnish.product.show', $item->product->slug) : '#' }}" class="text-decoration-none text-dark">
                                                                {{ $item->product ? $item->product->name : 'Product Unavailable' }}
                                                            </a>
                                                        </h6>
                                                        <span class="text-muted small d-block mb-1">Variant ID: {{ $item->product_variant_id }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="py-4 border-bottom-0 text-center">
                                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex justify-content-center align-items-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group input-group-sm w-auto shadow-sm rounded-pill overflow-hidden">
                                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control text-center border-0 bg-light" style="width: 60px;">
                                                        <button type="submit" class="btn btn-primary px-3 text-white" title="Update Quantity">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            
                                            <td class="py-4 border-bottom-0 text-center text-muted">
                                                ${{ number_format($item->price, 2) }}
                                            </td>
                                            
                                            <td class="py-4 border-bottom-0 text-center fw-bold text-dark">
                                                ${{ number_format($item->price * $item->quantity, 2) }}
                                            </td>
                                            
                                            <td class="pe-4 py-4 border-bottom-0 text-end">
                                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 35px; height: 35px;" title="Remove Item">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('furnish.products') }}" class="text-decoration-none text-muted fw-bold">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card card-modern border-0 position-sticky" style="top: 100px;">
                    <div class="card-body p-4 p-lg-5">
                        <h4 class="mb-4 fw-bold">Order Summary</h4>
                        
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Subtotal</span>
                            <span class="fw-semibold text-dark">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Shipping Estimate</span>
                            <span class="fw-semibold text-dark">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-muted">
                            <span>Tax Estimate</span>
                            <span class="fw-semibold text-dark">$0.00</span>
                        </div>
                        
                        <hr class="text-muted opacity-25 mb-4">
                        
                        <div class="d-flex justify-content-between mb-5">
                            <span class="h5 mb-0 fw-bold">Total Order</span>
                            <span class="h4 mb-0 fw-bold text-dark">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        <a href="{{ route('checkout') }}" class="btn btn-primary-modern btn-modern btn-lg w-100 d-flex justify-content-center align-items-center gap-2">
                            Proceed to Checkout <i class="bi bi-arrow-right"></i>
                        </a>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted text-uppercase letter-spacing-wide d-block mb-3" style="font-size: 0.7rem;">Secure Payment</small>
                            <div class="d-flex justify-content-center gap-3 text-muted fs-5 opacity-75">
                                <i class="bi bi-credit-card-fill"></i>
                                <i class="bi bi-paypal"></i>
                                <i class="bi bi-stripe"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Empty Cart State -->
        <div class="row justify-content-center">
            <div class="col-md-6 text-center py-5">
                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 120px; height: 120px;">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                </div>
                <h3 class="mb-3 fw-bold text-dark letter-spacing-tight">Your Cart is Empty</h3>
                <p class="text-muted mb-4 lead">Looks like you haven't added any products to your cart yet.</p>
                <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern btn-lg px-5">
                    Start Shopping
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
