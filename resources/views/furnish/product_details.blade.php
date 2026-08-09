@extends('layouts.furnish')

@section('title', $product->name)

@section('content')
<section class="py-5 py-lg-8" style="background-color: var(--bg-soft);">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-5">
            <ol class="breadcrumb bg-transparent p-0 m-0 text-sm">
                <li class="breadcrumb-item"><a href="{{ route('furnish.index') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('furnish.products') }}" class="text-decoration-none text-muted">Products</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row gx-lg-8">
            <!-- Product Images -->
            <div class="col-lg-6 mb-8 mb-lg-0">
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
                <div class="card card-modern border-0 mb-4 bg-white" style="border-radius: var(--radius-lg);">
                    <div class="p-4 p-lg-5 text-center">
                        <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="img-fluid object-fit-contain transition-transform" style="max-height: 500px;">
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-6">
                <div class="ps-lg-5">
                    <span class="badge bg-white text-primary border px-3 py-2 rounded-pill mb-4 shadow-sm letter-spacing-wide">
                        {{ $product->category ? $product->category->name : 'General' }}
                    </span>
                    <h1 class="display-4 fw-bold mb-3 lh-sm">{{ $product->name }}</h1>
                    
                    <div class="display-6 fw-bold mb-4" style="color: var(--primary);">
                        ${{ number_format($product->price ?? 99.99, 2) }}
                    </div>

                    <p class="lead text-muted mb-5" style="line-height: 1.8;">
                        {{ $product->description ?? 'Discover the perfect blend of style and comfort with this premium product. Designed to elevate your space with timeless elegance and modern functionality.' }}
                    </p>

                    <!-- Add to Cart Form -->
                    <form action="{{ route('cart.add') }}" method="POST" class="mt-4 bg-white p-4 rounded-4 shadow-sm border border-soft">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        <div class="row align-items-center g-3">
                            <div class="col-sm-4">
                                <label for="quantity" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-1">Quantity</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control-modern form-control w-100 text-center fw-bold fs-5">
                            </div>

                            <div class="col-sm-8">
                                <label class="form-label d-block text-muted small text-uppercase letter-spacing-wide fw-bold mb-1 opacity-0">Action</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary-modern btn-modern flex-grow-1 fs-5 d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-light btn-modern border shadow-sm px-4 d-flex align-items-center justify-content-center toggle-wishlist transition-all" data-product-id="{{ $product->id }}" onclick="toggleWishlist({{ $product->id }}, this); event.preventDefault();" style="border-radius: var(--radius-pill);">
                                        <i class="bi bi-heart fs-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Features -->
                    <div class="mt-8 pt-5 border-top">
                        <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex align-items-center gap-3 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Premium Quality Materials
                            </li>
                            <li class="d-flex align-items-center gap-3 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Free Shipping over $100
                            </li>
                            <li class="d-flex align-items-center gap-3 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                30-Day Easy Returns
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section class="py-5 py-lg-8 bg-white border-top border-soft">
    <div class="container">
        <div class="row gx-lg-8">
            <div class="col-lg-10 mx-auto">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <h3 class="fw-bold text-main mb-0">Customer Reviews</h3>
                </div>
                
                @php
                    $approvedReviews = $product->reviews()->where('status', 'approved')->latest()->get();
                    $totalReviews = $approvedReviews->count();
                    $avgRating = $totalReviews > 0 ? $approvedReviews->avg('rating') : 0;
                    
                    // Calculate star distribution
                    $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                    if ($totalReviews > 0) {
                        foreach ($approvedReviews as $r) {
                            $distribution[$r->rating]++;
                        }
                    }
                @endphp

                <div class="row mb-5 g-4">
                    <!-- Summary Card -->
                    <div class="col-md-5 col-lg-4">
                        <div class="card-modern p-4 h-100 bg-soft border-0 d-flex flex-column align-items-center justify-content-center text-center rounded-4">
                            <h1 class="display-3 fw-bold text-main mb-0">{{ number_format($avgRating, 1) }}</h1>
                            <div class="text-accent fs-4 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($avgRating))
                                        <i class="bi bi-star-fill"></i>
                                    @elseif($i - 0.5 == round($avgRating, 1) || ($i == ceil($avgRating) && $avgRating - floor($avgRating) >= 0.5))
                                        <i class="bi bi-star-half"></i>
                                    @else
                                        <i class="bi bi-star text-muted opacity-25"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-muted mb-0 fw-medium">Based on {{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
                        </div>
                    </div>
                    
                    <!-- Progress Bars -->
                    <div class="col-md-7 col-lg-8">
                        <div class="d-flex flex-column gap-3 h-100 justify-content-center px-lg-4">
                            @for($i = 5; $i >= 1; $i--)
                                @php
                                    $percentage = $totalReviews > 0 ? ($distribution[$i] / $totalReviews) * 100 : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-muted fw-bold d-flex align-items-center gap-1" style="width: 40px;">
                                        {{ $i }} <i class="bi bi-star-fill text-accent" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="progress flex-grow-1 bg-soft border" style="height: 10px; border-radius: 10px;">
                                        <div class="progress-bar bg-accent" role="progressbar" style="width: {{ $percentage }}%; border-radius: 10px;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="text-muted small fw-bold text-end" style="width: 40px;">{{ $distribution[$i] }}</div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="row gx-lg-5">
                    <!-- Reviews List -->
                    <div class="col-lg-7 order-2 order-lg-1">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-main mb-0">Reviews ({{ $totalReviews }})</h5>
                            @if($totalReviews > 0)
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle fw-bold text-muted px-3 rounded-pill shadow-sm" type="button" data-bs-toggle="dropdown">
                                    Sort By: <span id="currentSort" class="text-dark">Newest</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                    <li><a class="dropdown-item sort-btn active fw-bold" href="#" data-sort="newest">Newest First</a></li>
                                    <li><a class="dropdown-item sort-btn" href="#" data-sort="highest">Highest Rating</a></li>
                                    <li><a class="dropdown-item sort-btn" href="#" data-sort="lowest">Lowest Rating</a></li>
                                </ul>
                            </div>
                            @endif
                        </div>

                        <div id="reviewsContainer" class="d-flex flex-column gap-4">
                            @forelse($approvedReviews as $review)
                                <div class="review-card card-modern p-4 transition-all hover-lift rounded-4 border-soft" data-rating="{{ $review->rating }}" data-timestamp="{{ $review->created_at->timestamp }}">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm">
                                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-main d-flex align-items-center gap-2">
                                                    {{ $review->user->name }}
                                                    <i class="bi bi-patch-check-fill text-success" style="font-size: 0.9rem;" title="Verified Customer"></i>
                                                </h6>
                                                <small class="text-muted fw-medium">{{ $review->created_at->format('F d, Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-accent mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="bi bi-star-fill"></i>
                                            @else
                                                <i class="bi bi-star text-muted opacity-25"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-muted mb-3" style="line-height: 1.7; font-size: 0.95rem;">{{ $review->comment }}</p>
                                    
                                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-soft">
                                        <small class="text-muted fw-bold">Was this helpful?</small>
                                        <button class="btn btn-sm btn-light border-0 rounded-pill px-3 text-muted hover-primary transition-colors">
                                            <i class="bi bi-hand-thumbs-up me-1"></i> Yes
                                        </button>
                                        <button class="btn btn-sm btn-light border-0 rounded-pill px-3 text-muted hover-primary transition-colors">
                                            <i class="bi bi-hand-thumbs-down me-1"></i> No
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 bg-soft rounded-4 border border-dashed border-soft">
                                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                                        <i class="bi bi-star text-muted opacity-50 fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold text-main">No reviews yet</h5>
                                    <p class="text-muted mb-0">Be the first to share your thoughts on this product!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Add Review Form -->
                    <div class="col-lg-5 order-1 order-lg-2 mb-5 mb-lg-0">
                        <div class="position-sticky" style="top: 100px;">
                            <div class="card-modern p-4 p-lg-5 bg-white border border-soft shadow-sm rounded-4">
                                <h4 class="fw-bold text-main mb-2">Write a Review</h4>
                                <p class="text-muted small mb-4">Share your experience with other customers.</p>
                                
                                @auth
                                    <form action="{{ route('reviews.store', $product->id) }}" method="POST" id="reviewForm">
                                        @csrf
                                        
                                        <!-- Interactive Star Selector -->
                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Overall Rating *</label>
                                            <div class="star-rating-input d-flex flex-row-reverse justify-content-end gap-1">
                                                <input type="radio" id="star5" name="rating" value="5" required />
                                                <label for="star5" title="5 stars"><i class="bi bi-star-fill"></i></label>
                                                
                                                <input type="radio" id="star4" name="rating" value="4" />
                                                <label for="star4" title="4 stars"><i class="bi bi-star-fill"></i></label>
                                                
                                                <input type="radio" id="star3" name="rating" value="3" />
                                                <label for="star3" title="3 stars"><i class="bi bi-star-fill"></i></label>
                                                
                                                <input type="radio" id="star2" name="rating" value="2" />
                                                <label for="star2" title="2 stars"><i class="bi bi-star-fill"></i></label>
                                                
                                                <input type="radio" id="star1" name="rating" value="1" />
                                                <label for="star1" title="1 star"><i class="bi bi-star-fill"></i></label>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="comment" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Your Review *</label>
                                            <textarea name="comment" id="comment" rows="4" class="form-control-modern form-control bg-soft border-0" placeholder="What did you like or dislike? What should other shoppers know?" required style="resize: none;"></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn-modern btn-primary-modern w-100 d-flex justify-content-center align-items-center gap-2" id="submitReviewBtn">
                                            <span>Submit Review</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </form>
                                @else
                                    <div class="text-center py-5 bg-soft rounded-4 border border-soft">
                                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 50px; height: 50px;">
                                            <i class="bi bi-lock text-primary fs-4"></i>
                                        </div>
                                        <p class="text-dark fw-bold mb-3">Sign in to review</p>
                                        <a href="{{ route('login') }}" class="btn-modern btn-outline-modern text-decoration-none px-4 rounded-pill">Log In</a>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Star Rating Input Interactivity */
    .star-rating-input {
        font-size: 2rem;
    }
    .star-rating-input input {
        display: none;
    }
    .star-rating-input label {
        color: #e2e8f0; /* text-muted / soft color */
        cursor: pointer;
        transition: color 0.2s ease-in-out, transform 0.1s;
    }
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label,
    .star-rating-input input:checked ~ label {
        color: #fbbf24; /* Accent color / yellow */
    }
    .star-rating-input label:active {
        transform: scale(0.9);
    }
    
    /* Avatar Circle */
    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
    }
    
    .hover-primary:hover {
        color: var(--primary) !important;
        background-color: var(--bg-soft) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Review Sorting Logic
    const sortBtns = document.querySelectorAll('.sort-btn');
    const reviewsContainer = document.getElementById('reviewsContainer');
    const currentSortLabel = document.getElementById('currentSort');
    
    if(reviewsContainer && sortBtns.length > 0) {
        sortBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active state
                sortBtns.forEach(b => b.classList.remove('active', 'fw-bold'));
                this.classList.add('active', 'fw-bold');
                currentSortLabel.textContent = this.textContent;
                
                const sortType = this.getAttribute('data-sort');
                const cards = Array.from(reviewsContainer.querySelectorAll('.review-card'));
                
                cards.sort((a, b) => {
                    if (sortType === 'newest') {
                        return parseInt(b.getAttribute('data-timestamp')) - parseInt(a.getAttribute('data-timestamp'));
                    } else if (sortType === 'highest') {
                        return parseInt(b.getAttribute('data-rating')) - parseInt(a.getAttribute('data-rating'));
                    } else if (sortType === 'lowest') {
                        return parseInt(a.getAttribute('data-rating')) - parseInt(b.getAttribute('data-rating'));
                    }
                });
                
                // Add smooth fade out/in effect
                reviewsContainer.style.opacity = '0';
                setTimeout(() => {
                    reviewsContainer.innerHTML = '';
                    cards.forEach(card => reviewsContainer.appendChild(card));
                    reviewsContainer.style.opacity = '1';
                }, 200);
            });
        });
        
        // CSS transition for smooth sorting
        reviewsContainer.style.transition = 'opacity 0.2s ease-in-out';
    }
    
    // Form submission loading state
    const reviewForm = document.getElementById('reviewForm');
    if(reviewForm) {
        reviewForm.addEventListener('submit', function() {
            const btn = document.getElementById('submitReviewBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';
            btn.disabled = true;
        });
    }
});
</script>

<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection
