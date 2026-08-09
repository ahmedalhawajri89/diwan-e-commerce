@extends('layouts.furnish')

@section('title', 'Inspiration & Lookbook')

@section('content')
<style>
    .lookbook-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 6rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .lookbook-header::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: var(--accent);
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.2;
        top: -100px;
        right: -100px;
        pointer-events: none;
    }

    .look-container {
        position: relative;
        width: 100%;
        height: 70vh;
        min-height: 500px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 5rem;
        box-shadow: var(--shadow-lg);
    }

    .look-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 10s ease;
    }

    .look-container:hover .look-image {
        transform: scale(1.03);
    }

    .hotspot {
        position: absolute;
        width: 30px;
        height: 30px;
        transform: translate(-50%, -50%);
        z-index: 10;
        cursor: pointer;
    }

    .hotspot-dot {
        width: 16px;
        height: 16px;
        background-color: white;
        border-radius: 50%;
        box-shadow: 0 0 0 4px rgba(255,255,255,0.3);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        transition: all 0.3s ease;
    }

    .hotspot:hover .hotspot-dot {
        background-color: var(--accent);
        box-shadow: 0 0 0 6px rgba(255,255,255,0.5);
    }

    .hotspot-pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        background-color: rgba(255,255,255,0.5);
        border-radius: 50%;
        animation: pulse 2s infinite cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }

    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(3); opacity: 0; }
    }

    /* Custom Popover */
    .custom-popover {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 260px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 100;
        overflow: hidden;
        cursor: default;
    }

    .custom-popover.pop-right {
        left: 40px;
        transform: translate(-10px, -50%);
    }

    .custom-popover.pop-left {
        right: 40px;
        transform: translate(10px, -50%);
    }

    .hotspot:hover .custom-popover,
    .hotspot:focus-within .custom-popover {
        opacity: 1;
        visibility: visible;
    }

    .hotspot:hover .custom-popover.pop-right,
    .hotspot:focus-within .custom-popover.pop-right {
        transform: translate(0, -50%);
    }

    .hotspot:hover .custom-popover.pop-left,
    .hotspot:focus-within .custom-popover.pop-left {
        transform: translate(0, -50%);
    }

    .hotspot-card {
        display: flex;
        flex-direction: column;
    }

    .hotspot-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .hotspot-content {
        padding: 1rem;
    }
</style>

<!-- Header -->
<section class="lookbook-header text-center">
    <div class="container position-relative z-1" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">Shop The Look</h1>
        <p class="lead text-light-muted mb-0 mx-auto" style="max-width: 600px;">
            Get inspired by our professionally styled spaces. Find something you love? Click the hotspots to shop it instantly.
        </p>
    </div>
</section>

<!-- Looks Gallery -->
<section class="py-5 py-lg-7 bg-soft">
    <div class="container">
        @if(empty($looks))
            <div class="text-center py-5 text-muted">
                <i class="bi bi-images fs-1 opacity-25 mb-3 d-block"></i>
                <h5>No looks available yet.</h5>
            </div>
        @else
            @foreach($looks as $index => $look)
                <div class="row mb-4 align-items-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-2">{{ $look['title'] }}</h2>
                        <p class="text-muted mb-0">{{ $look['description'] }}</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-tag-fill text-accent me-1"></i> {{ count($look['hotspots']) }} Items to shop
                        </span>
                    </div>
                </div>

                <div class="look-container" data-aos="zoom-in" data-aos-delay="200">
                    <img src="{{ $look['image'] }}" alt="{{ $look['title'] }}" class="look-image">
                    
                    @foreach($look['hotspots'] as $hotspot)
                        @php 
                            $product = $hotspot['product']; 
                            $imgUrl = asset('assets/images/product-img-1.jpg');
                            if($product->images && $product->images->first()) {
                                $img = $product->images->first()->image_url;
                                $imgUrl = str_starts_with($img, 'http') ? $img : (str_starts_with($img, 'assets/') ? asset($img) : asset('storage/' . $img));
                            }
                            $popClass = $hotspot['left'] > 50 ? 'pop-left' : 'pop-right';
                        @endphp
                            
                            <!-- Hotspot Marker -->
                            <div class="hotspot" 
                                 style="top: {{ $hotspot['top'] }}%; left: {{ $hotspot['left'] }}%;"
                                 tabindex="0">
                                <div class="hotspot-pulse"></div>
                                <div class="hotspot-dot"></div>
                                
                                <!-- Custom Popover -->
                                <div class="custom-popover {{ $popClass }}">
                                    <div class="hotspot-card">
                                        <img src="{{$imgUrl}}" class="hotspot-img">
                                        <div class="hotspot-content">
                                            <h6 class="fw-bold text-dark text-truncate mb-1">{{$product->name}}</h6>
                                            <div class="text-primary fw-bold mb-3">${{number_format($product->price, 2)}}</div>
                                            <a href="/product/{{$product->slug}}" class="btn btn-primary-modern btn-modern btn-sm w-100 text-center text-decoration-none" style="background-color: var(--primary); color: white; border: none; padding: 0.5rem 1rem; display: block;">View Product</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-white text-center">
    <div class="container" data-aos="fade-up">
        <h3 class="fw-bold mb-3">Want to build your own look?</h3>
        <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">Explore our full catalog and mix and match pieces to create your perfect space.</p>
        <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern px-5 rounded-pill fw-bold text-decoration-none">Shop All Products</a>
    </div>
</section>


@endsection
