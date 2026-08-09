@extends('layouts.furnish')

@section('title', 'Order Confirmed')

@section('content')
<div class="py-5 py-lg-8" style="background-color: var(--bg-soft); min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            
            <div class="col-lg-8 text-center mb-5 anim-fade-up">
                <!-- Success Animation -->
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-4 position-relative" style="width: 120px; height: 120px;">
                    <i class="bi bi-check-lg text-success" style="font-size: 4rem; z-index: 2;"></i>
                    <div class="position-absolute w-100 h-100 rounded-circle border border-success border-2 pulse-ring"></div>
                </div>
                
                <h1 class="display-5 fw-bold mb-3 letter-spacing-tight text-dark">Your Order is Confirmed! 🎉</h1>
                <p class="text-muted lead mx-auto" style="max-width: 600px;">
                    Thank you for shopping with Furnish. Your order <strong>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong> has been placed and is currently being processed.
                </p>
            </div>

            <div class="col-lg-10">
                <div class="row g-4 mb-5">
                    
                    <!-- Order Summary Card -->
                    <div class="col-md-6 anim-fade-up" style="animation-delay: 0.1s;">
                        <div class="card card-modern border-0 h-100 p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="bi bi-receipt fs-5"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Order Summary</h5>
                            </div>
                            
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent px-0 py-3 border-soft d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold">Order ID</span>
                                    <span class="fw-bold text-dark">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-3 border-soft d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold">Date</span>
                                    <span class="fw-bold text-dark">{{ $order->created_at->format('M d, Y, h:i A') }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-3 border-soft d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold">Payment Method</span>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($order->payment_method === 'credit_card')
                                            <i class="bi bi-credit-card text-primary"></i> <span class="fw-bold">Card</span>
                                        @else
                                            <i class="bi bi-cash-stack text-success"></i> <span class="fw-bold">COD</span>
                                        @endif
                                    </div>
                                </li>
                                <li class="list-group-item bg-transparent px-0 pt-3 pb-0 border-soft d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold">Total Paid</span>
                                    <span class="fw-bold text-primary fs-4">${{ number_format($order->total_price, 2) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Delivery Info Card -->
                    <div class="col-md-6 anim-fade-up" style="animation-delay: 0.2s;">
                        <div class="card card-modern border-0 h-100 p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="bi bi-truck fs-5"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Delivery Details</h5>
                            </div>
                            
                            <!-- Delivery Status Tracker -->
                            <div class="mb-4 pb-4 border-bottom border-soft">
                                <div class="d-flex justify-content-between position-relative mb-2">
                                    <div class="position-absolute top-50 start-0 w-100 translate-middle-y bg-soft" style="height: 3px; z-index: 0;"></div>
                                    <div class="position-absolute top-50 start-0 bg-success translate-middle-y" style="height: 3px; width: 33%; z-index: 1;"></div>
                                    
                                    <div class="bg-success rounded-circle position-relative z-2 border border-3 border-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                        <i class="bi bi-check text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="bg-soft rounded-circle position-relative z-2 border border-3 border-white" style="width: 24px; height: 24px;"></div>
                                    <div class="bg-soft rounded-circle position-relative z-2 border border-3 border-white" style="width: 24px; height: 24px;"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted fw-semibold">
                                    <span class="text-success">Processed</span>
                                    <span>Shipped</span>
                                    <span>Delivered</span>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold text-dark text-xs text-uppercase letter-spacing-wide mb-2">Shipping To</h6>
                            @if($order->shippingAddress)
                                <p class="text-muted mb-0 lh-lg">
                                    {{ auth()->check() ? auth()->user()->name : 'Guest' }}<br>
                                    {{ $order->shippingAddress->street_address }}<br>
                                    {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->country }}<br>
                                    {{ $order->shippingAddress->phone_number }}
                                </p>
                            @else
                                <p class="text-muted mb-0">Standard Shipping Address</p>
                            @endif
                            <div class="mt-3 bg-light rounded-3 p-3 d-flex align-items-center gap-3">
                                <i class="bi bi-calendar-check text-success fs-4"></i>
                                <div>
                                    <span class="d-block fw-bold text-dark small">Estimated Delivery</span>
                                    <span class="text-muted small">{{ now()->addDays(3)->format('M d') }} - {{ now()->addDays(7)->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-5 pb-5 border-bottom border-soft anim-fade-up" style="animation-delay: 0.3s;">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary-modern btn-modern btn-lg px-5">
                        <i class="bi bi-box-seam me-2"></i> Track My Order
                    </a>
                    <a href="{{ route('furnish.products') }}" class="btn btn-light btn-modern border btn-lg px-5 shadow-sm">
                        Continue Shopping <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>

                <!-- Recommended Products -->
                <div class="anim-fade-up" style="animation-delay: 0.4s;">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold">You Might Also Like</h4>
                        <p class="text-muted">Handpicked items that match your style.</p>
                    </div>
                    
                    <div class="row g-4">
                        @php
                            $recommended = \App\Models\Product::inRandomOrder()->take(3)->get();
                        @endphp
                        @foreach($recommended as $product)
                            @php
                                $img = $product->images->first();
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
                            <div class="col-md-4">
                                <div class="card card-modern h-100 border-0 p-2">
                                    <div class="position-relative overflow-hidden bg-light rounded-top-lg mb-3" style="aspect-ratio: 4/3;">
                                        <a href="{{ route('furnish.product.show', $product->slug) }}" class="d-block h-100 w-100">
                                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover transition-transform" style="transition: transform 0.5s ease;">
                                        </a>
                                    </div>
                                    <div class="card-body p-2 text-center">
                                        <h6 class="fw-bold mb-1"><a href="{{ route('furnish.product.show', $product->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $product->name }}</a></h6>
                                        <div class="fw-bold text-primary">${{ number_format($product->price ?? 99.99, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Support Section -->
                <div class="mt-5 text-center anim-fade-up" style="animation-delay: 0.5s;">
                    <div class="d-inline-flex flex-column align-items-center bg-white p-4 rounded-4 shadow-sm border border-soft w-100" style="max-width: 500px;">
                        <i class="bi bi-question-circle text-primary fs-2 mb-2"></i>
                        <h6 class="fw-bold mb-1">Need Help With Your Order?</h6>
                        <p class="text-muted small mb-3">Our support team is available 24/7 to assist you.</p>
                        <a href="{{ route('furnish.contact') }}" class="btn btn-outline-dark btn-modern btn-sm px-4">Contact Support</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Success Pulse Animation */
    @keyframes pulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .pulse-ring {
        animation: pulse 2s infinite cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    
    /* Fade Up Animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-fade-up {
        animation: fadeUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
    }

    .card-modern:hover .transition-transform {
        transform: scale(1.05);
    }
</style>
@endsection
