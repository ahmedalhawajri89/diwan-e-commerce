@extends('layouts.furnish')

@section('title', 'Home')

@section('content')

<!-- Modern Animated Hero Slider -->
<section class="position-relative overflow-hidden" style="background: var(--bg-soft);">
    <!-- Swiper Container -->
    <div class="swiper hero-swiper" style="height: 85vh; min-height: 600px;">
        <div class="swiper-wrapper">
            
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="h-100 w-100 d-flex align-items-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                    <div class="container position-relative z-1">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0 pe-lg-5 hero-content">
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide anim-delay-1">NEW COLLECTION 2025</span>
                                <h1 class="display-3 fw-bold mb-4 lh-sm text-dark anim-delay-2">
                                    Redefine Your<br>
                                    <span style="color: var(--accent);">Living Space.</span>
                                </h1>
                                <p class="lead text-muted mb-5 pe-lg-4 anim-delay-3" style="font-weight: 400; line-height: 1.8;">
                                    Experience the perfect blend of minimalist design and maximum comfort. Handcrafted furniture for the modern home.
                                </p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start anim-delay-4">
                                    <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern btn-lg px-5">Shop Collection</a>
                                    <a href="{{ route('furnish.about') }}" class="btn btn-light btn-modern btn-lg px-5 border shadow-sm">Our Story</a>
                                </div>
                            </div>
                            <div class="col-lg-6 position-relative hero-image-wrapper">
                                <!-- Decorative Elements -->
                                <div class="position-absolute bg-primary opacity-10 rounded-circle blur-3xl anim-blob" style="width: 400px; height: 400px; top: -10%; right: -10%; filter: blur(60px);"></div>
                                <img src="{{ asset('assets/images/slider/slider-img-1.png') }}" alt="Modern Sofa" class="img-fluid position-relative z-1 hero-image" style="filter: drop-shadow(0 30px 40px rgba(0,0,0,0.1));">
                                <!-- Floating Info Card -->
                                <div class="position-absolute bottom-0 start-0 bg-white p-3 rounded-4 shadow-lg z-2 d-none d-md-flex align-items-center gap-3 hero-card">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-star-fill text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">4.9/5 Rating</div>
                                        <small class="text-muted">From 2,000+ Customers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide">
                <div class="h-100 w-100 d-flex align-items-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%);">
                    <div class="container position-relative z-1">
                        <div class="row align-items-center flex-row-reverse">
                            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0 ps-lg-5 hero-content">
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide anim-delay-1">EXCLUSIVE DESIGN</span>
                                <h1 class="display-3 fw-bold mb-4 lh-sm text-dark anim-delay-2">
                                    Crafted For<br>
                                    <span style="color: var(--accent);">Elegance.</span>
                                </h1>
                                <p class="lead text-muted mb-5 pe-lg-4 anim-delay-3" style="font-weight: 400; line-height: 1.8;">
                                    Transform your workspace or reading corner with our award-winning ergonomic chairs. Built to last a lifetime.
                                </p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start anim-delay-4">
                                    <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern btn-lg px-5">Explore Chairs</a>
                                </div>
                            </div>
                            <div class="col-lg-6 position-relative hero-image-wrapper text-center">
                                <div class="position-absolute bg-info opacity-10 rounded-circle blur-3xl anim-blob" style="width: 400px; height: 400px; top: 10%; left: -10%; filter: blur(60px);"></div>
                                <img src="{{ asset('assets/images/slider/slider-img-2.png') }}" alt="Modern Chair" class="img-fluid position-relative z-1 hero-image" style="filter: drop-shadow(0 30px 40px rgba(0,0,0,0.1)); max-height: 500px;">
                                <div class="position-absolute bottom-0 end-0 bg-white p-3 rounded-4 shadow-lg z-2 d-none d-md-flex align-items-center gap-3 hero-card">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-shield-check text-success fs-5"></i>
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-bold text-dark">Lifetime Warranty</div>
                                        <small class="text-muted">On all premium seating</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide">
                <div class="h-100 w-100 d-flex align-items-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="container position-relative z-1">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0 pe-lg-5 hero-content">
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide anim-delay-1">LIMITED EDITION</span>
                                <h1 class="display-3 fw-bold mb-4 lh-sm text-dark anim-delay-2">
                                    Minimalist<br>
                                    <span style="color: var(--accent);">Perfection.</span>
                                </h1>
                                <p class="lead text-muted mb-5 pe-lg-4 anim-delay-3" style="font-weight: 400; line-height: 1.8;">
                                    Discover the beauty of simplicity with our new Scandinavian-inspired collection. Pure, functional, and stunning.
                                </p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start anim-delay-4">
                                    <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern btn-lg px-5">Discover Now</a>
                                </div>
                            </div>
                            <div class="col-lg-6 position-relative hero-image-wrapper">
                                <div class="position-absolute bg-warning opacity-10 rounded-circle blur-3xl anim-blob" style="width: 400px; height: 400px; top: -20%; right: 10%; filter: blur(60px);"></div>
                                <img src="{{ asset('assets/images/slider/slider-img-3.png') }}" alt="Minimalist Furniture" class="img-fluid position-relative z-1 hero-image" style="filter: drop-shadow(0 30px 40px rgba(0,0,0,0.1));">
                                <div class="position-absolute top-50 start-0 translate-middle-y bg-white p-3 rounded-4 shadow-lg z-2 d-none d-md-flex flex-column align-items-center justify-content-center gap-2 hero-card" style="width: 80px; height: 100px; left: -20px !important;">
                                    <span class="fs-4 fw-bold text-dark">-25%</span>
                                    <small class="text-muted text-uppercase" style="font-size: 0.6rem;">Discount</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Custom Navigation -->
        <div class="swiper-button-next custom-swiper-nav shadow-sm d-none d-md-flex"></div>
        <div class="swiper-button-prev custom-swiper-nav shadow-sm d-none d-md-flex"></div>
        
        <!-- Custom Pagination -->
        <div class="swiper-pagination custom-swiper-pagination mb-3"></div>
    </div>
</section>

<!-- Shop by Category -->
<section class="py-5 py-lg-8" style="background: linear-gradient(180deg, #ffffff 0%, var(--bg-soft) 100%);">
    <div class="container" data-aos="fade-up">
        <div class="text-center mb-5">
            <h6 class="text-primary text-uppercase fw-bold letter-spacing-wide mb-2">Explore</h6>
            <h2 class="display-6 fw-bold">Shop by Category</h2>
        </div>
        
        <div class="row g-4 justify-content-center">
            @php
                $categories = \App\Models\Category::take(4)->get();
                $catIcons = ['bi-lamp', 'bi-house', 'bi-grid', 'bi-box'];
            @endphp
            @forelse($categories as $index => $category)
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <a href="{{ route('furnish.products', ['categories[]' => $category->id]) }}" class="text-decoration-none">
                        <div class="card card-modern border-0 text-center p-4 h-100 bg-white">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 text-primary transition-transform" style="width: 80px; height: 80px;">
                                <i class="bi {{ $catIcons[$index % 4] }} fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $category->name }}</h5>
                            <span class="text-muted small">Explore <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
            @empty
            @endforelse
        </div>
    </div>
</section>

<!-- Featured Products (Best Sellers) -->
<section class="py-5 py-lg-8 bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-5" data-aos="fade-up">
            <div>
                <h6 class="text-muted text-uppercase fw-bold letter-spacing-wide mb-2">Curated Picks</h6>
                <h2 class="display-5 fw-bold mb-0">Trending Now</h2>
            </div>
            <a href="{{ route('furnish.products') }}" class="btn btn-outline-dark btn-modern mt-4 mt-md-0 d-none d-md-inline-block">View All Products <i class="bi bi-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            @forelse($featuredProducts->take(6) as $index => $product)
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
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
                    <div class="card card-modern h-100 border-0">
                        <div class="position-relative overflow-hidden bg-light rounded-top-lg" style="aspect-ratio: 4/3;">
                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 p-3 w-100 d-flex justify-content-between" style="z-index: 10;">
                                <div class="d-flex flex-column gap-2 align-items-start">
                                    @if($loop->first)
                                        <span class="badge bg-dark rounded-pill px-3 py-2 shadow-sm">#1 Trending</span>
                                    @elseif($loop->iteration == 2)
                                        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">New</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Image -->
                            <a href="{{ route('furnish.product.show', $product->slug) }}" class="d-block h-100 w-100">
                                <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover transition-transform" style="transition: transform 0.5s ease;">
                            </a>

                            <!-- Hover Actions Overlay -->
                            <div class="product-actions-overlay">
                                <button class="btn-action-icon text-dark" title="Quick View" onclick="window.location.href='{{ route('furnish.product.show', $product->slug) }}'">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-action-icon bg-primary text-white" title="Add to Cart" onclick="window.location.href='{{ route('furnish.product.show', $product->slug) }}'">
                                    <i class="bi bi-bag-plus"></i>
                                </button>
                                <button class="btn-action-icon text-danger" title="Wishlist" onclick="toggleWishlist({{ $product->id }}, this); event.preventDefault();">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 text-center">
                            <h5 class="mb-1 fw-bold">
                                <a href="{{ route('furnish.product.show', $product->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $product->name }}</a>
                            </h5>
                            <p class="text-muted small mb-3 text-truncate">{{ $product->description }}</p>
                            <div class="fw-bold fs-4" style="color: var(--primary);">
                                ${{ number_format($product->price ?? 99.99, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-seam fs-1 text-muted"></i>
                    </div>
                    <h4>No products found</h4>
                    <p class="text-muted">Check back later for new arrivals.</p>
                </div>
            @endforelse
        </div>
        
        <div class="text-center mt-5 d-block d-md-none" data-aos="fade-up">
            <a href="{{ route('furnish.products') }}" class="btn btn-outline-dark btn-modern w-100">View All Products</a>
        </div>
    </div>
</section>

<!-- Brand Highlights -->
<section class="py-5 py-lg-8 text-white" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="0">
                <i class="bi bi-truck fs-1 text-accent mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">Free Delivery</h5>
                <p class="text-light-muted small mb-0">On all orders over $500</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <i class="bi bi-shield-check fs-1 text-accent mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">10-Year Warranty</h5>
                <p class="text-light-muted small mb-0">Guaranteed build quality</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <i class="bi bi-arrow-repeat fs-1 text-accent mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">30 Days Return</h5>
                <p class="text-light-muted small mb-0">No questions asked</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <i class="bi bi-headset fs-1 text-accent mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">24/7 Support</h5>
                <p class="text-light-muted small mb-0">Dedicated assistance</p>
            </div>
        </div>
    </div>
</section>

<!-- Modern Testimonial -->
<section class="py-5 py-lg-8 position-relative overflow-hidden" style="background-color: var(--bg-soft);">
    <div class="position-absolute bg-white rounded-circle opacity-50" style="width: 400px; height: 400px; top: -100px; right: -100px; filter: blur(40px);"></div>
    <div class="container position-relative z-1">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <i class="bi bi-quote fs-1 text-primary opacity-25 mb-3 d-block"></i>
                <h3 class="lh-base fw-light mb-5" style="font-size: 1.75rem;">
                    "The quality and craftsmanship of Furnish is unmatched. My living room has never looked so elegant and felt so comfortable. Highly recommended for modern homes."
                </h3>
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=Sarah+Jenks&background=0f172a&color=fff" alt="Sarah Jenks" class="rounded-circle shadow-sm" width="50" height="50">
                    <div class="text-start">
                        <div class="fw-bold">Sarah Jenks</div>
                        <small class="text-muted">Interior Designer</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Clean Newsletter -->
<section class="py-5 py-lg-8 bg-white border-top border-soft">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center" data-aos="fade-up">
                <h2 class="fw-bold mb-3">Join the Club</h2>
                <p class="text-muted mb-4">Subscribe to our newsletter for exclusive offers, new arrivals, and design inspiration.</p>
                
                <form class="d-flex flex-column flex-sm-row gap-2 max-w-md mx-auto">
                    <input type="email" class="form-control-modern flex-grow-1 shadow-sm" placeholder="Email address" required>
                    <button type="submit" class="btn btn-primary-modern btn-modern px-4 shadow-sm">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    /* Card hover zoom effect */
    .card-modern:hover .transition-transform {
        transform: scale(1.05);
    }

    /* Hero Slider Animations */
    
    /* Base hidden state for animations */
    .hero-content .anim-delay-1,
    .hero-content .anim-delay-2,
    .hero-content .anim-delay-3,
    .hero-content .anim-delay-4 {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .hero-image-wrapper .hero-image {
        opacity: 0;
        transform: scale(0.9) translateX(50px);
        transition: all 1.2s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .hero-image-wrapper .hero-card {
        opacity: 0;
        transform: translateY(50px);
        transition: all 1s cubic-bezier(0.34, 1.56, 0.64, 1); /* Bounce effect */
    }

    /* Active state (triggered by Swiper) */
    .swiper-slide-active .hero-content .anim-delay-1 { opacity: 1; transform: translateY(0); transition-delay: 0.1s; }
    .swiper-slide-active .hero-content .anim-delay-2 { opacity: 1; transform: translateY(0); transition-delay: 0.3s; }
    .swiper-slide-active .hero-content .anim-delay-3 { opacity: 1; transform: translateY(0); transition-delay: 0.5s; }
    .swiper-slide-active .hero-content .anim-delay-4 { opacity: 1; transform: translateY(0); transition-delay: 0.7s; }
    
    .swiper-slide-active .hero-image-wrapper .hero-image {
        opacity: 1;
        transform: scale(1) translateX(0);
        transition-delay: 0.2s;
    }
    .swiper-slide-active .hero-image-wrapper .hero-card {
        opacity: 1;
        transform: translateY(20px); /* Final resting state */
        transition-delay: 0.8s;
    }

    /* Hover effect on the floating card */
    .hero-card:hover {
        transform: translateY(10px) !important;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    /* Continuous floating animation for blobs */
    @keyframes floatBlob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .anim-blob {
        animation: floatBlob 15s infinite ease-in-out;
    }

    /* Continuous subtle float for the main image */
    @keyframes floatImage {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    .swiper-slide-active .hero-image {
        animation: floatImage 6s ease-in-out infinite;
        animation-delay: 1.4s; /* Start after entry animation */
    }

    /* Custom Swiper Navigation */
    .custom-swiper-nav {
        background-color: white;
        width: 50px !important;
        height: 50px !important;
        border-radius: 50%;
        color: var(--primary) !important;
        transition: all 0.3s ease;
    }
    .custom-swiper-nav:hover {
        background-color: var(--primary);
        color: white !important;
        transform: scale(1.1);
    }
    .custom-swiper-nav::after {
        font-size: 1.2rem !important;
        font-weight: bold;
    }
    
    /* Custom Swiper Pagination */
    .custom-swiper-pagination .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: var(--primary);
        opacity: 0.3;
        transition: all 0.3s ease;
    }
    .custom-swiper-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        width: 30px;
        border-radius: 5px;
    }
</style>

<!-- Initialize Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const heroSwiper = new Swiper('.hero-swiper', {
            loop: true,
            effect: 'fade', // Smooth crossfade transition
            fadeEffect: {
                crossFade: true
            },
            speed: 1000,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    });
</script>
@endsection
