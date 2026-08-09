@extends('layouts.furnish')

@section('title', '404 - Page Not Found')

@section('content')

@php
    // Fetch some suggested products to keep the user engaged
    $suggestedProducts = \App\Models\Product::with(['images', 'variants', 'category'])
                            ->where('status', 'active')
                            ->inRandomOrder()
                            ->limit(4)
                            ->get();
@endphp

<!-- Inline styles for unique 404 animations -->
<style>
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    .text-404-gradient {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .product-img-zoom {
        transition: transform 0.5s ease;
    }
    .card-modern:hover .product-img-zoom {
        transform: scale(1.1);
    }
    .product-overlay {
        opacity: 0;
        transition: opacity 0.3s ease;
        background-color: rgba(0, 0, 0, 0.1);
    }
    .card-modern:hover .product-overlay {
        opacity: 1;
    }
    .glassmorphism {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
</style>

<div class="py-5" style="background-color: var(--bg-soft); min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <!-- Visual Section -->
                <div class="mb-4 animate-float">
                    <h1 class="display-1 fw-bold text-404-gradient" style="font-size: 8rem; line-height: 1;">404</h1>
                    <div class="position-relative d-inline-block mt-3">
                        <i class="bi bi-search" style="font-size: 3rem; color: var(--accent); position: absolute; top: -20px; right: -40px; transform: rotate(15deg); opacity: 0.5;"></i>
                        <i class="bi bi-box-seam" style="font-size: 4rem; color: var(--primary); opacity: 0.8;"></i>
                    </div>
                </div>

                <!-- Message Section -->
                <h2 class="fw-bold text-main mb-3">Oops! Page not found</h2>
                <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 500px;">
                    We can't seem to find the page you're looking for. It might have been removed, had its name changed, or is temporarily unavailable.
                </p>

                <!-- Call To Actions -->
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-5">
                    <a href="{{ route('furnish.index') }}" class="btn-modern btn-primary-modern text-decoration-none">
                        <i class="bi bi-house-door me-2"></i> Back to Home
                    </a>
                    <a href="{{ route('furnish.products') }}" class="btn-modern btn-outline-modern text-decoration-none bg-white">
                        <i class="bi bi-shop me-2"></i> Browse Shop
                    </a>
                </div>

                <!-- Search Feature -->
                <div class="card-modern p-4 mx-auto mb-5 glassmorphism" style="max-width: 600px;">
                    <h5 class="fw-bold text-main mb-3">Let's find what you need</h5>
                    <form action="{{ route('furnish.products') }}" method="GET" class="d-flex gap-2">
                        <div class="position-relative flex-grow-1">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="q" class="form-control-modern ps-5" placeholder="Search for furniture, lighting, decor..." required style="height: 100%;">
                        </div>
                        <button type="submit" class="btn-modern btn-primary-modern" style="border-radius: var(--radius-md);">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Suggested Content -->
        @if($suggestedProducts->count() > 0)
        <div class="mt-5 border-top pt-5" style="border-color: var(--border-soft) !important;">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-main">You might like these</h3>
                <p class="text-muted">Explore some of our popular products</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                @foreach ($suggestedProducts as $product)
                    @php
                        $variant = $product->variants->first();
                        $primaryImage = $product->images->where('is_primary', true)->first();
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card-modern h-100 position-relative">
                            <!-- Image -->
                            <div class="position-relative overflow-hidden rounded-top" style="aspect-ratio: 1/1;">
                                @if($primaryImage)
                                    <img src="{{ str_starts_with($primaryImage->image_url, 'http') ? $primaryImage->image_url : asset('storage/' . $primaryImage->image_url) }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-100 h-100 product-img-zoom" style="object-fit: cover;">
                                @else
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image text-muted fs-1"></i>
                                    </div>
                                @endif
                                
                                <!-- Quick View overlay -->
                                <div class="position-absolute top-0 start-0 w-100 h-100 product-overlay d-flex align-items-center justify-content-center gap-2">
                                    <a href="{{ route('furnish.product.show', $product->slug) }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-3">
                                <div class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">{{ $product->category ? $product->category->name : 'General' }}</div>
                                <h6 class="fw-bold text-main mb-1 text-truncate">
                                    <a href="{{ route('furnish.product.show', $product->slug) }}" class="text-decoration-none text-main">{{ $product->name }}</a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fw-bold text-accent">${{ $variant ? number_format($variant->price, 2) : '0.00' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
