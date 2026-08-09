@extends('layouts.furnish')

@section('title', 'Products')

@section('content')
<!-- Modern Header -->
<section class="py-5 bg-white border-bottom border-soft">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">Our Collection</h1>
        <p class="text-muted lead max-w-md mx-auto" style="max-width: 600px;">
            Discover pieces that blend form with function. Elevate your space with our premium, handcrafted furniture.
        </p>
    </div>
</section>

<!-- Page Content -->
<section class="py-5 py-lg-8" style="background-color: var(--bg-soft);">
    <div class="container">
        <form method="GET" action="{{ route('furnish.products') }}" id="filterForm">
            <div class="row g-5">
                
                <!-- Modern Sidebar Filters -->
                <aside class="col-lg-3" data-aos="fade-right">
                    <div class="card card-modern border-0 mb-4 position-sticky" style="top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-funnel me-2"></i> Filters</h5>
                            
                            <!-- Search -->
                            <div class="mb-4">
                                <div class="position-relative">
                                    <input type="text" name="q" class="form-control-modern form-control pe-5" placeholder="Search products..." value="{{ request('q') }}" onchange="document.getElementById('filterForm').submit()">
                                    <button type="submit" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-primary">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 small text-uppercase text-muted letter-spacing-wide">Category</h6>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($categories as $category)
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat{{ $category->id }}" {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                            <label class="form-check-label text-dark" for="cat{{ $category->id }}">{{ $category->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <hr class="text-muted opacity-25 my-4">

                            <!-- Price -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 small text-uppercase text-muted letter-spacing-wide">Price Range</h6>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check custom-checkbox">
                                        <input class="form-check-input" type="checkbox" name="prices[]" value="under_100" id="price1" {{ in_array('under_100', request('prices', [])) ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                        <label class="form-check-label text-dark" for="price1">Under $100</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input class="form-check-input" type="checkbox" name="prices[]" value="100_300" id="price2" {{ in_array('100_300', request('prices', [])) ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                        <label class="form-check-label text-dark" for="price2">$100 - $300</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input class="form-check-input" type="checkbox" name="prices[]" value="above_300" id="price3" {{ in_array('above_300', request('prices', [])) ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                        <label class="form-check-label text-dark" for="price3">Above $300</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25 my-4">

                            <!-- Colors -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 small text-uppercase text-muted letter-spacing-wide">Colors</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($availableColors as $color)
                                        <div class="color-swatch-container">
                                            <input type="checkbox" class="btn-check color-check" name="colors[]" value="{{ $color }}" id="col{{ $loop->index }}" {{ in_array($color, request('colors', [])) ? 'checked' : '' }} autocomplete="off" onchange="document.getElementById('filterForm').submit()">
                                            <label class="color-swatch rounded-circle shadow-sm" for="col{{ $loop->index }}" style="background-color: {{ strtolower(str_replace(' ', '', $color)) }};" title="{{ ucfirst($color) }}">
                                                <i class="bi bi-check swatch-check-icon {{ in_array($color, request('colors', [])) ? 'd-block' : 'd-none' }}" style="color: {{ in_array(strtolower($color), ['white', '#ffffff', '#fff']) ? '#000' : '#fff' }}; font-size: 1.2rem; line-height: 1;"></i>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if(request()->has('categories') || request()->has('prices') || request()->has('colors') || request()->has('q'))
                                <div class="mt-4">
                                    <a href="{{ route('furnish.products') }}" class="btn btn-light btn-sm w-100 fw-bold rounded-pill shadow-sm">Clear All Filters</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>

                <!-- Product Grid -->
                <div class="col-lg-9" data-aos="fade-left">
                    
                    <!-- Top Bar -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                        <div class="text-muted mb-3 mb-md-0 ms-2">
                            Showing <span class="fw-bold text-dark">{{ $products->count() }}</span> products
                        </div>
                        <div style="min-width: 200px;">
                            <select class="form-select border-0 bg-light rounded-pill px-4 fw-medium shadow-none" name="sort" onchange="document.getElementById('filterForm').submit()">
                                <option value="" {{ request('sort') == '' ? 'selected' : '' }}>Sort by: Featured</option>
                                <option value="alphabetical_az" {{ request('sort') == 'alphabetical_az' ? 'selected' : '' }}>A - Z</option>
                                <option value="alphabetical_za" {{ request('sort') == 'alphabetical_za' ? 'selected' : '' }}>Z - A</option>
                                <option value="date_new" {{ request('sort') == 'date_new' ? 'selected' : '' }}>Newest Arrivals</option>
                                <option value="date_old" {{ request('sort') == 'date_old' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="row g-4">
                        @forelse($products as $index => $product)
                            <div class="col-sm-6 col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
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
                                    $price = $product->variants->first() ? $product->variants->first()->price : 50.00;
                                @endphp
                                
                                <div class="card card-modern h-100 border-0">
                                    <div class="position-relative overflow-hidden bg-light rounded-top-lg group" style="aspect-ratio: 4/4;">
                                        <!-- Image -->
                                        <a href="{{ route('furnish.product.show', $product->slug) }}" class="d-block h-100 w-100">
                                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover transition-transform" style="transition: transform 0.5s ease;">
                                        </a>
                                        
                                        <!-- Wishlist Button -->
                                        <button class="btn btn-wishlist-card position-absolute top-0 end-0 m-3 rounded-circle p-0 d-flex align-items-center justify-content-center border-0 shadow-sm toggle-wishlist" style="width: 40px; height: 40px; z-index: 10;" data-product-id="{{ $product->id }}" onclick="toggleWishlist({{ $product->id }}, this); event.preventDefault();">
                                            <i class="bi bi-heart fs-5"></i>
                                        </button>
                                        
                                        <!-- Quick Add Button (Hover) -->
                                        <div class="position-absolute bottom-0 start-50 translate-middle-x w-100 p-3 opacity-0 transition-opacity quick-add" style="transition: opacity 0.3s ease;">
                                            <form action="{{ route('cart.add') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm fw-bold">
                                                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4 text-center">
                                        <p class="text-muted text-uppercase small letter-spacing-wide mb-1">{{ $product->category ? $product->category->name : 'Home' }}</p>
                                        <h5 class="mb-2 fw-bold">
                                            <a href="{{ route('furnish.product.show', $product->slug) }}" class="text-dark text-decoration-none stretched-link-disabled">{{ $product->name }}</a>
                                        </h5>
                                        <div class="fw-bold fs-5" style="color: var(--primary);">
                                            ${{ number_format($price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 100px; height: 100px;">
                                    <i class="bi bi-search fs-1 text-muted"></i>
                                </div>
                                <h3 class="fw-bold">No results found</h3>
                                <p class="text-muted">Try adjusting your filters or search terms to find what you're looking for.</p>
                                <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern mt-3">Clear Filters</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-5">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </form>
    </div>
</section>

<style>
    /* Color Swatches */
    .color-swatch-container {
        display: inline-block;
    }
    .color-swatch {
        width: 32px;
        height: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        position: relative;
    }
    .color-swatch::after {
        content: '';
        position: absolute;
        top: -4px; left: -4px; right: -4px; bottom: -4px;
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    .color-check:checked + .color-swatch::after {
        border-color: var(--primary);
        border-width: 2px;
    }
    .color-check:checked + .color-swatch {
        transform: scale(0.9);
    }
    
    /* Card Hover & Quick Add Effects */
    .card-modern:hover .transition-transform {
        transform: scale(1.05);
    }
    .quick-add {
        opacity: 0;
        visibility: hidden;
    }
    .card-modern:hover .quick-add {
        opacity: 1;
        visibility: visible;
    }
    .custom-checkbox .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .btn-check:checked + .btn-outline-secondary {
        background-color: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
    }
</style>
@endsection
