<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Furnish - @yield('title', 'Home')</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/scss/furnish/style.scss', 'resources/js/furnish/main.js'])
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Fix for notifications appearing behind the header */
        .fl-wrapper,
        #toast-container,
        .swal2-container {
            z-index: 99999 !important;
        }

        /* Footer Enhancements */
        .hover-white:hover {
            color: #ffffff !important;
        }

        .footer-link {
            position: relative;
            display: inline-block;
            padding-bottom: 2px;
            color: rgba(255, 255, 255, 0.7) !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: #ffffff !important;
            padding-left: 4px;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: 0;
            left: 0;
            background-color: #fff;
            opacity: 0.5;
            transition: width 0.3s ease;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        .social-icon {
            transform: translateY(0);
            display: inline-block;
        }

        .social-icon:hover {
            transform: translateY(-3px) scale(1.1);
            color: #fff !important;
        }

        :root {
            /* Premium Modern Palette (High Contrast) */
            --primary: #0f172a;
            --primary-light: #334155;
            --accent: #b45309; /* Deep amber for better contrast */
            --bg-soft: #f1f5f9; /* Better separation from white cards */
            --surface: #ffffff;
            --text-main: #020617; /* Near black */
            --text-muted: #475569; /* Darker muted text for readability */
            --border-soft: #cbd5e1; /* More visible borders */

            /* Shadows & Effects */
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-pill: 999px;

            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .text-light-muted { color: rgba(255, 255, 255, 0.8) !important; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-soft);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Glassmorphism Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1050;
            padding: 1rem 2rem !important;
            transition: var(--transition);
        }

        .navbar-brand span {
            font-size: 1.5rem !important;
            letter-spacing: 0 !important;
            font-weight: 800 !important;
            color: var(--primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-muted) !important;
            transition: var(--transition);
            padding: 0.5rem 1rem !important;
            border-radius: var(--radius-pill);
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
            background-color: rgba(15, 23, 42, 0.05);
        }

        /* Modern Buttons */
        .btn-modern {
            border-radius: var(--radius-pill);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            border: none;
        }

        .btn-primary-modern {
            background-color: var(--primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-modern:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Premium Cards */
        .card-modern {
            background: var(--surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
        }

        .card-modern:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
            border-color: transparent;
        }

        /* Product Card Overlay Actions */
        .product-actions-overlay {
            position: absolute;
            bottom: -60px;
            left: 0;
            width: 100%;
            padding: 1rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 20;
            background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 100%);
        }
        .card-modern:hover .product-actions-overlay {
            bottom: 0;
            opacity: 1;
        }
        .btn-action-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        .btn-action-icon:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        /* Forms */
        .form-control-modern {
            background-color: var(--bg-soft);
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control-modern:focus {
            background-color: var(--surface);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.1);
        }

        /* Custom Active Link */
        .nav-link.active {
            color: var(--primary) !important;
            font-weight: 700;
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* Footer */
        .footer-dark {
            background-color: var(--primary);
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-dark h5 {
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .footer-dark .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-dark .footer-link:hover {
            color: #ffffff;
            padding-left: 5px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: var(--transition);
        }

        .social-icon:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white navbar-custom">
        <div class="container py-1">
            <!-- Left: Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('furnish.index') }}">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-house-door-fill fs-5"></i>
                </div>
                <span class="fs-4 fw-bold letter-spacing-tight text-dark"
                    style="letter-spacing: -0.05em !important;">FURNISH.</span>
            </a>

            <!-- Center: Navigation Links -->
            <div class="mx-auto d-none d-lg-block">
                <ul class="navbar-nav gap-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.index') ? 'active' : '' }}"
                            href="{{ route('furnish.index') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.products') ? 'active' : '' }}"
                            href="{{ route('furnish.products') }}">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.lookbook') ? 'active' : '' }} d-flex align-items-center"
                            href="{{ route('furnish.lookbook') }}">
                            Inspiration 
                            <span class="badge ms-1 rounded-pill" style="background-color: var(--accent); color: white; font-size: 0.55rem; padding: 0.25em 0.5em; transform: translateY(-2px);">NEW</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.about') ? 'active' : '' }}"
                            href="{{ route('furnish.about') }}">Our Story</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.contact') ? 'active' : '' }}"
                            href="{{ route('furnish.contact') }}">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Right: Icons & Actions -->
            <div class="d-flex align-items-center gap-3 gap-lg-4">

                <!-- Search -->
                <a href="#" data-bs-toggle="modal" data-bs-target="#searchModal"
                    class="text-dark opacity-75 text-decoration-none hover-primary transition-all d-none d-sm-block">
                    <i class="bi bi-search fs-5"></i>
                </a>

                @php
                    $cartCount = 0;
                    if (auth()->check()) {
                        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
                        if ($cart) {
                            $cartCount = $cart->items()->sum('quantity');
                        }
                    }
                @endphp

                <!-- Wishlist -->
                <div class="dropdown d-none d-sm-block" id="wishlistDropdownContainer">
                    <a href="#"
                        class="position-relative text-dark opacity-75 text-decoration-none hover-primary transition-all"
                        id="wishlistDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false"
                        data-bs-auto-close="outside">
                        <i class="bi bi-heart fs-5 wishlist-header-icon transition-transform"></i>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-white d-none wishlist-counter-badge"
                            style="font-size: 0.65rem; padding: 0.35em 0.5em; transform-origin: center;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-3 p-0"
                        style="width: 320px; overflow: hidden; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);"
                        aria-labelledby="wishlistDropdownBtn">
                        <div
                            class="p-3 border-bottom border-soft d-flex justify-content-between align-items-center bg-light">
                            <h6 class="mb-0 fw-bold d-flex align-items-center"><i
                                    class="bi bi-heart-fill text-primary me-2"></i> My Wishlist</h6>
                            <span class="badge bg-primary rounded-pill wishlist-counter-badge">0</span>
                        </div>
                        <div id="wishlistDropdownContent" class="p-2" style="max-height: 350px; overflow-y: auto;">
                            <div class="text-center p-4 text-muted small">
                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                <div>Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart -->
                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}"
                    class="position-relative text-dark opacity-75 text-decoration-none hover-primary transition-all">
                    <i class="bi bi-bag fs-5"></i>
                    @if($cartCount > 0)
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-white"
                            style="font-size: 0.65rem; padding: 0.35em 0.5em;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                <!-- User Profile / Auth -->
                @auth
                    <div class="dropdown d-none d-md-block">
                        <button
                            class="btn btn-light rounded-pill px-3 py-2 border-soft d-flex align-items-center gap-2 text-dark shadow-sm dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=e2e8f0&color=0f172a"
                                alt="User" class="rounded-circle" width="24" height="24">
                            <span class="fw-semibold small">{{ strtok(auth()->user()->name, " ") }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                            @if(auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists())
                                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.categories.index') }}"><i
                                            class="bi bi-grid-1x2 me-2 text-muted"></i> Dashboard</a></li>
                                <li>
                                    <hr class="dropdown-divider opacity-25">
                                </li>
                            @endif
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('profile.edit') }}"><i
                                        class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('profile.edit') }}"><i
                                        class="bi bi-box-seam me-2 text-muted"></i> My Orders</a></li>
                            <li>
                                <hr class="dropdown-divider opacity-25">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 py-2 text-danger"><i
                                            class="bi bi-box-arrow-right me-2"></i> Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="btn btn-primary-modern btn-modern btn-sm px-4 d-none d-md-block">Sign In</a>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button class="btn border-0 p-0 text-dark d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center m-3 shadow-sm border-0 rounded-4"
                role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show text-center m-3 shadow-sm border-0 rounded-4"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer-premium pt-5 pt-lg-7 pb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #020617 100%); color: rgba(255,255,255,0.7);">
        <!-- Decorative background glow -->
        <div class="position-absolute bg-primary rounded-circle opacity-20" style="width: 600px; height: 600px; top: -300px; right: -200px; filter: blur(80px); pointer-events: none;"></div>

        <div class="container position-relative z-1">
            
            <!-- Top: Trust & Value Indicators -->
            <div class="row g-4 border-bottom border-white border-opacity-10 pb-5 mb-5 text-center text-md-start">
                <div class="col-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start gap-3">
                        <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-accent transition-transform hover-scale" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1 fw-bold">Secure Payment</h6>
                            <small class="text-light-muted">100% safe transactions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 position-relative">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
                        <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-accent transition-transform hover-scale" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1 fw-bold">Fast Delivery</h6>
                            <small class="text-light-muted">On orders over $500</small>
                        </div>
                    </div>
                    <!-- Separators -->
                    <div class="d-none d-md-block position-absolute top-50 start-0 translate-middle-y border-start border-white border-opacity-10" style="height: 40px;"></div>
                    <div class="d-none d-md-block position-absolute top-50 end-0 translate-middle-y border-end border-white border-opacity-10" style="height: 40px;"></div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-end gap-3">
                        <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-accent transition-transform hover-scale" style="width: 50px; height: 50px; min-width: 50px;">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                        <div class="text-md-start">
                            <h6 class="text-white mb-1 fw-bold">Quality Guarantee</h6>
                            <small class="text-light-muted">10-year warranty</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle: Main Content -->
            <div class="row g-5 mb-5">
                <!-- Brand -->
                <div class="col-lg-3">
                    <a class="text-decoration-none d-flex align-items-center gap-2 mb-4" href="{{ route('furnish.index') }}">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-house-door-fill fs-5"></i>
                        </div>
                        <span class="fs-4 fw-bold letter-spacing-tight text-white">FURNISH.</span>
                    </a>
                    <p class="mb-4 text-light-muted lh-lg pe-lg-2" style="font-size: 0.95rem;">
                        Elevate your space with premium, meticulously crafted furniture. We design not just for the eyes, but for the soul.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-pinterest"></i></a>
                    </div>
                </div>

                <!-- Links -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-4 text-uppercase letter-spacing-wide small">Shop</h6>
                    <ul class="list-unstyled footer-links d-flex flex-column gap-3">
                        <li><a href="{{ route('furnish.products') }}" class="footer-link">All Products</a></li>
                        <li><a href="#" class="footer-link">Living Room</a></li>
                        <li><a href="#" class="footer-link">Bedroom</a></li>
                        <li><a href="#" class="footer-link">Workspace</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-4 text-uppercase letter-spacing-wide small">Support</h6>
                    <ul class="list-unstyled footer-links d-flex flex-column gap-3">
                        <li><a href="{{ route('furnish.contact') }}" class="footer-link">Contact Us</a></li>
                        <li><a href="#" class="footer-link">Shipping Policy</a></li>
                        <li><a href="#" class="footer-link">Returns & Refunds</a></li>
                        <li><a href="#" class="footer-link">FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter Highlight Card -->
                <div class="col-lg-5 col-md-4">
                    <div class="card bg-white bg-opacity-10 border-0 rounded-4 overflow-hidden h-100 shadow-sm" style="backdrop-filter: blur(10px);">
                        <div class="card-body p-4 p-lg-5 position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-envelope fs-1 text-white"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-2 position-relative z-1">Get Exclusive Offers</h5>
                            <p class="text-light-muted small mb-4 position-relative z-1">Join our inner circle for design tips, new arrivals, and special discounts.</p>
                            
                            <form class="position-relative z-1 newsletter-form" onsubmit="event.preventDefault(); handleNewsletter(this);">
                                <div class="input-group input-group-lg rounded-pill p-1 bg-white bg-opacity-10 border border-white border-opacity-10 transition-all focus-ring-group">
                                    <input type="email" class="form-control bg-transparent border-0 text-white shadow-none ps-4 placeholder-white-50" placeholder="Your email address" required style="font-size: 0.95rem;">
                                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" type="submit" style="background-color: var(--accent); border: none;">Subscribe</button>
                                </div>
                                <!-- Success Feedback -->
                                <div class="newsletter-success text-success mt-3 small fw-bold d-none">
                                    <i class="bi bi-check-circle-fill me-1"></i> Thank you! You've been subscribed.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-white border-opacity-10 mb-4">

            <!-- Bottom Bar -->
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-light-muted small">&copy; {{ date('Y') }} Furnish. All rights reserved.</p>
                </div>
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <div class="d-flex gap-3 justify-content-center">
                        <i class="bi bi-credit-card fs-4 text-light-muted hover-white transition-all cursor-pointer"></i>
                        <i class="bi bi-paypal fs-4 text-light-muted hover-white transition-all cursor-pointer"></i>
                        <i class="bi bi-stripe fs-4 text-light-muted hover-white transition-all cursor-pointer"></i>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn btn-sm bg-white bg-opacity-10 text-white rounded-pill px-4 transition-all border-0 shadow-sm btn-back-top" style="backdrop-filter: blur(5px);">
                        Back to top <i class="bi bi-arrow-up ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .hover-scale:hover { transform: scale(1.1); }
        .hover-white:hover { color: #ffffff !important; }
        .btn-back-top:hover { background-color: rgba(255,255,255,0.2) !important; color: #fff; }
        .focus-ring-group:focus-within { border-color: rgba(255,255,255,0.3) !important; box-shadow: 0 0 0 4px rgba(255,255,255,0.05); }
        .placeholder-white-50::placeholder { color: rgba(255,255,255,0.5) !important; }
    </style>

    <script>
        function handleNewsletter(form) {
            const btn = form.querySelector('button');
            const successMsg = form.querySelector('.newsletter-success');
            const inputGrp = form.querySelector('.input-group');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;
            
            setTimeout(() => {
                inputGrp.classList.add('d-none');
                successMsg.classList.remove('d-none');
                successMsg.classList.add('anim-slide-down');
            }, 1000);
        }
    </script>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header px-4">
            <a class="navbar-brand" href="{{ route('furnish.index') }}">
                <span class="d-flex flex-column text-uppercase text-xs fw-bold lh-sm">
                    <span class="" style="letter-spacing: .12rem;">Furnish</span>
                    <span>Template</span>
                </span>
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="navbar-custom">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.index') ? 'active' : '' }}"
                            aria-current="page" href="{{ route('furnish.index') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.about') ? 'active' : '' }}"
                            href="{{ route('furnish.about') }}">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->requestUri == '/products' ? 'active' : '' }}"
                            href="{{ route('furnish.products') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.lookbook') ? 'active' : '' }}"
                            href="{{ route('furnish.lookbook') }}">Inspiration <span class="badge" style="background-color: var(--accent);">NEW</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.testimonials') ? 'active' : '' }}"
                            href="{{ route('furnish.testimonials') }}">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('furnish.contact') ? 'active' : '' }}"
                            href="{{ route('furnish.contact') }}">Contact</a>
                    </li>
                    @auth
                        @if(auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists())
                            <li class="nav-item mt-3">
                                <a class="nav-link text-primary fw-bold" href="{{ route('admin.categories.index') }}"><i
                                        class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                            </li>
                        @endif
                        <li
                            class="nav-item {{ !auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists() ? 'mt-3' : '' }}">
                            <a class="nav-link text-dark fw-bold" href="{{ route('profile.edit') }}"><i
                                    class="bi bi-person me-2"></i> Profile</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="nav-link border-0 bg-transparent text-danger fw-bold text-start p-0"><i
                                        class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item mt-3">
                            <a class="nav-link text-primary fw-bold" href="{{ route('login') }}">Log in</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Success!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonColor: "#3085d6",
                    timer: 3000
                });
            });
        </script>
    @endif

    <!-- Search Modal (Glassmorphism) -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content"
                style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none;">
                <div class="modal-header border-0 pb-0 pe-4 pt-4">
                    <button type="button" class="btn-close fs-4 ms-auto" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column align-items-center pt-2">
                    <div class="w-100" style="max-width: 800px;">
                        <!-- Search Input -->
                        <div class="position-relative mb-5 anim-slide-down">
                            <i
                                class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 fs-3 text-muted"></i>
                            <input type="text" id="liveSearchInput"
                                class="form-control form-control-lg border-0 shadow-sm rounded-pill px-5 py-4 fs-4"
                                placeholder="Search for products, categories..." autocomplete="off"
                                style="padding-left: 4rem !important;">
                            <i class="bi bi-x-circle-fill position-absolute top-50 end-0 translate-middle-y me-4 fs-4 text-muted cursor-pointer d-none"
                                id="clearSearchBtn" style="cursor: pointer;"></i>
                        </div>

                        <!-- Search Results -->
                        <div id="searchResults" class="row g-3 w-100 m-0">
                            <!-- Initial Content (Suggestions) -->
                            <div class="col-12 text-center text-muted" id="searchSuggestions">
                                <h6 class="text-uppercase letter-spacing-wide mb-4 fw-bold">Trending Searches</h6>
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <span class="badge bg-white text-dark border px-4 py-2 rounded-pill shadow-sm"
                                        style="cursor: pointer;"
                                        onclick="document.getElementById('liveSearchInput').value='Sofa'; document.getElementById('liveSearchInput').dispatchEvent(new Event('input'));">Sofa</span>
                                    <span class="badge bg-white text-dark border px-4 py-2 rounded-pill shadow-sm"
                                        style="cursor: pointer;"
                                        onclick="document.getElementById('liveSearchInput').value='Chair'; document.getElementById('liveSearchInput').dispatchEvent(new Event('input'));">Chair</span>
                                    <span class="badge bg-white text-dark border px-4 py-2 rounded-pill shadow-sm"
                                        style="cursor: pointer;"
                                        onclick="document.getElementById('liveSearchInput').value='Table'; document.getElementById('liveSearchInput').dispatchEvent(new Event('input'));">Table</span>
                                    <span class="badge bg-white text-dark border px-4 py-2 rounded-pill shadow-sm"
                                        style="cursor: pointer;"
                                        onclick="document.getElementById('liveSearchInput').value='Lamp'; document.getElementById('liveSearchInput').dispatchEvent(new Event('input'));">Lamp</span>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Spinner -->
                        <div id="searchSpinner" class="text-center d-none mt-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Search Modal Animations */
        .anim-slide-down {
            animation: slideDown 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-result-card {
            transition: var(--transition);
            animation: fadeUp 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
        }

        .search-result-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
            background-color: var(--bg-soft) !important;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            const searchResults = document.getElementById('searchResults');
            const searchSuggestions = document.getElementById('searchSuggestions');
            const searchSpinner = document.getElementById('searchSpinner');
            const searchModal = document.getElementById('searchModal');

            if (!searchModal) return;

            let debounceTimer;

            // Auto focus when modal opens
            searchModal.addEventListener('shown.bs.modal', function () {
                searchInput.focus();
            });

            // Clear input when modal closes
            searchModal.addEventListener('hidden.bs.modal', function () {
                searchInput.value = '';
                clearBtn.classList.add('d-none');
                searchResults.innerHTML = '';
                searchResults.appendChild(searchSuggestions);
                searchSuggestions.style.display = 'block';
            });

            // Clear button action
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearBtn.classList.add('d-none');
                searchInput.focus();
                searchResults.innerHTML = '';
                searchResults.appendChild(searchSuggestions);
                searchSuggestions.style.display = 'block';
            });

            searchInput.addEventListener('input', function () {
                const query = this.value.trim();

                if (query.length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                    searchResults.innerHTML = '';
                    searchResults.appendChild(searchSuggestions);
                    searchSuggestions.style.display = 'block';
                    return;
                }

                clearTimeout(debounceTimer);

                searchSpinner.classList.remove('d-none');
                if (searchSuggestions && searchSuggestions.parentNode) {
                    searchSuggestions.style.display = 'none';
                }

                debounceTimer = setTimeout(() => {
                    fetch('/api/search?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            searchSpinner.classList.add('d-none');
                            renderSearchResults(data, query);
                        })
                        .catch(error => {
                            console.error('Error fetching search results:', error);
                            searchSpinner.classList.add('d-none');
                        });
                }, 300); // 300ms debounce
            });

            function renderSearchResults(products, query) {
                searchResults.innerHTML = '';

                if (products.length === 0) {
                    searchResults.innerHTML = \`
                        <div class="col-12 text-center text-muted mt-5 anim-slide-down">
                            <i class="bi bi-search fs-1 mb-3 opacity-25"></i>
                            <h5>No results found for "\${query}"</h5>
                            <p>Try checking your spelling or use more general terms.</p>
                        </div>
                    \`;
                    return;
                }

                products.forEach((product, index) => {
                    const delay = index * 0.05; // Staggered animation
                    
                    // Highlight matching text
                    const regex = new RegExp(\`(\${query})\`, "gi");
                    const highlightedName = product.name.replace(regex, "<span class='text-primary fw-bold'>$1</span>");

                    const cardHtml = \`
                        <div class="col-12">
                            <a href="/product/\${product.slug}" class="text-decoration-none">
                                <div class="card border border-soft rounded-4 p-3 d-flex flex-row align-items-center gap-4 bg-white search-result-card" style="animation-delay: \${delay}s">
                                    <img src="\${product.image}" alt="\${product.name}" class="rounded-3 object-fit-cover shadow-sm" style="width: 70px; height: 70px;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-dark fw-bold">\${highlightedName}</h6>
                                        <span class="badge bg-light text-muted fw-normal mb-0">\${product.category}</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-primary fs-5">$\${product.price}</span>
                                        <div class="text-muted small mt-1 d-none d-sm-block">View Product <i class="bi bi-arrow-right"></i></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    \`;
                    searchResults.insertAdjacentHTML('beforeend', cardHtml);
                });
                
                // Add a "View All Results" link if we hit the limit
                if (products.length === 5) {
                    searchResults.insertAdjacentHTML('beforeend', \`
                        <div class="col-12 text-center mt-3">
                            <a href="/products" class="btn btn-light rounded-pill px-4 text-dark fw-bold">
                                Browse full catalog <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    \`);
                }
            }
        });
    </script>

    <script>
        // Wishlist Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const wishlistDropdownBtn = document.getElementById('wishlistDropdownBtn');
            const wishlistDropdownContent = document.getElementById('wishlistDropdownContent');
            const wishlistBadges = document.querySelectorAll('.wishlist-counter-badge');
            const wishlistHeaderIcon = document.querySelector('.wishlist-header-icon');

            // Initial fetch of wishlist items when authenticated
            const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

            // Get guest wishlist from localStorage
            function getGuestWishlist() {
                try {
                    return JSON.parse(localStorage.getItem('furnish_wishlist_ids')) || [];
                } catch(e) { return []; }
            }
            function setGuestWishlist(ids) {
                localStorage.setItem('furnish_wishlist_ids', JSON.stringify(ids));
            }

            if(isAuthenticated) {
                const guestIds = getGuestWishlist();
                if (guestIds.length > 0) {
                    // Sync guest wishlist to DB
                    fetch('{{ route('wishlist.sync') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: guestIds })
                    }).then(() => {
                        localStorage.removeItem('furnish_wishlist_ids');
                        fetchWishlistDropdown();
                    }).catch(err => {
                        console.error('Error syncing wishlist:', err);
                        fetchWishlistDropdown();
                    });
                } else {
                    fetchWishlistDropdown();
                }
            } else {
                fetchGuestWishlistDropdown();
            }

            function fetchGuestWishlistDropdown() {
                const guestIds = getGuestWishlist();
                if (guestIds.length === 0) {
                    updateWishlistUI([], 0);
                    return;
                }

                fetch('{{ route('wishlist.guest.dropdown') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: guestIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateWishlistUI(data.items, data.count);
                    }
                })
                .catch(err => console.error('Error fetching guest wishlist:', err));
            }

            // Fetch wishlist items
            function fetchWishlistDropdown() {
                fetch('{{ route('wishlist.dropdown') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateWishlistUI(data.items, data.count);
                    }
                })
                .catch(err => console.error('Error fetching wishlist:', err));
            }

            // Update UI based on data
            function updateWishlistUI(items, count) {
                // Update badges
                wishlistBadges.forEach(badge => {
                    badge.textContent = count;
                    if(count > 0) {
                        badge.classList.remove('d-none');
                        // Optional pop animation
                        badge.style.transform = 'translate(-50%, -50%) scale(1.2)';
                        setTimeout(() => badge.style.transform = 'translate(-50%, -50%) scale(1)', 200);
                    } else {
                        if (badge.closest('a')) {
                            badge.classList.add('d-none');
                        }
                    }
                });

                if(count > 0 && wishlistHeaderIcon) {
                    wishlistHeaderIcon.classList.remove('bi-heart');
                    wishlistHeaderIcon.classList.add('bi-heart-fill', 'text-primary');
                } else if (wishlistHeaderIcon) {
                    wishlistHeaderIcon.classList.remove('bi-heart-fill', 'text-primary');
                    wishlistHeaderIcon.classList.add('bi-heart');
                }

                // Render items in dropdown
                if(wishlistDropdownContent) {
                    wishlistDropdownContent.innerHTML = '';
                    if(items.length === 0) {
                        wishlistDropdownContent.innerHTML = `
                            <div class="text-center p-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-heart fs-3 text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Your wishlist is empty</h6>
                                <p class="small text-muted mb-3">Save items you love to revisit them later.</p>
                                <a href="{{ route('furnish.products') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100">Explore Products</a>
                            </div>
                        `;
                    } else {
                        items.forEach(item => {
                            const html = `
                                <div class="d-flex gap-3 align-items-center p-2 border-bottom border-soft position-relative dropdown-item-hover rounded-3 transition-all" id="wishlist-item-${item.id}">
                                    <a href="/product/${item.slug}" class="flex-shrink-0">
                                        <img src="${item.image}" alt="${item.name}" class="rounded-3 object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                    </a>
                                    <div class="flex-grow-1 min-w-0">
                                        <a href="/product/${item.slug}" class="text-dark text-decoration-none d-block text-truncate fw-bold small mb-1">${item.name}</a>
                                        <div class="text-primary fw-bold small">$${item.price}</div>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <button class="btn btn-sm btn-light text-primary rounded-circle border-0 move-to-cart-btn shadow-sm" data-product-id="${item.product_id}" title="Move to Cart">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-danger rounded-circle border-0 remove-wishlist-btn shadow-sm" data-product-id="${item.product_id}" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            wishlistDropdownContent.insertAdjacentHTML('beforeend', html);
                        });

                        // Attach listeners to dynamically created buttons
                        attachWishlistDropdownListeners();
                    }
                }

                // Update product cards globally if they are on the page
                updateProductCardHearts(items);
            }

            // Sync product cards on the page with current wishlist state
            function updateProductCardHearts(items) {
                const productIdsInWishlist = items.map(i => i.product_id.toString());
                document.querySelectorAll('.toggle-wishlist').forEach(btn => {
                    const pid = btn.getAttribute('data-product-id');
                    const icon = btn.querySelector('i');
                    if (productIdsInWishlist.includes(pid)) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill', 'text-primary');
                        icon.style.animation = 'none'; // reset
                    } else {
                        icon.classList.remove('bi-heart-fill', 'text-primary');
                        icon.classList.add('bi-heart');
                    }
                });
            }

            function attachWishlistDropdownListeners() {
                document.querySelectorAll('.remove-wishlist-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleWishlist(this.getAttribute('data-product-id'));
                    });
                });

                document.querySelectorAll('.move-to-cart-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        moveToCart(this.getAttribute('data-product-id'));
                    });
                });
            }

            function showToast(message) {
                if (typeof flasher !== 'undefined') {
                    flasher.success(message);
                } else if (typeof window.flasher !== 'undefined') {
                    window.flasher.success(message);
                } else {
                    // Fallback just in case
                    const Toast = Swal.mixin({
                        toast: true,
                        position: "bottom-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: "success",
                        title: message
                    });
                }
            }

            // Global toggle function
            window.toggleWishlist = function(productId, buttonElement = null) {
                // If buttonElement exists, play quick animation (Optimistic UI)
                if (buttonElement) {
                    const icon = buttonElement.querySelector('i');
                    icon.style.transform = 'scale(0.8)';
                    setTimeout(() => icon.style.transform = 'scale(1.2)', 100);
                    setTimeout(() => icon.style.transform = 'scale(1)', 200);
                }

                if(!isAuthenticated) {
                    let guestIds = getGuestWishlist();
                    let message = '';
                    if (guestIds.includes(productId.toString())) {
                        guestIds = guestIds.filter(id => id !== productId.toString());
                        message = 'Removed from Wishlist';
                    } else {
                        guestIds.push(productId.toString());
                        message = 'Added to Wishlist ❤️';
                    }
                    setGuestWishlist(guestIds);
                    fetchGuestWishlistDropdown();
                    showToast(message);
                    return;
                }

                fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchWishlistDropdown();
                        showToast(data.message);
                    }
                })
                .catch(err => console.error('Error toggling wishlist:', err));
            };

            function moveToCart(productId) {
                if(!isAuthenticated) {
                    Swal.fire({
                        title: "Sign in required",
                        text: "Please sign in to add items to your cart.",
                        icon: "info",
                        showCancelButton: true,
                        confirmButtonText: "Sign In",
                        cancelButtonText: "Cancel",
                        confirmButtonColor: "var(--primary)",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('login') }}";
                        }
                    });
                    return;
                }

                fetch('{{ route('wishlist.moveToCart') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchWishlistDropdown(); // refresh wishlist

                        // Update cart count
                        const cartBagIcon = document.querySelector('.bi-bag');
                        if (cartBagIcon) {
                            let cartBadge = cartBagIcon.nextElementSibling;
                            if(cartBadge && cartBadge.classList.contains('badge')) {
                                cartBadge.textContent = data.cart_count;
                            } else if(data.cart_count > 0) {
                                let newBadge = document.createElement('span');
                                newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-white';
                                newBadge.style.cssText = 'font-size: 0.65rem; padding: 0.35em 0.5em;';
                                newBadge.textContent = data.cart_count;
                                cartBagIcon.parentElement.appendChild(newBadge);
                            }
                        }

                        Swal.fire({
                            toast: true,
                            position: "bottom-end",
                            showConfirmButton: false,
                            timer: 3000,
                            icon: "success",
                            title: "Moved to cart!"
                        });
                    }
                })
                .catch(err => console.error('Error moving to cart:', err));
            }
        });
    </script>

    <style>
        .wishlist-header-icon {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .wishlist-header-icon:hover {
            transform: scale(1.15);
            color: var(--accent) !important;
        }

        .dropdown-item-hover {
            background-color: transparent;
        }

        .dropdown-item-hover:hover {
            background-color: var(--bg-soft);
        }

        .wishlist-counter-badge {
            transition: transform 0.2s ease;
        }

        /* Product Card Wishlist Button */
        .btn-wishlist-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            color: var(--text-muted);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 10;
        }

        .btn-wishlist-card:hover {
            background: var(--surface);
            color: var(--primary);
            transform: scale(1.1);
            box-shadow: var(--shadow-md);
        }

        .btn-wishlist-card i {
            transition: transform 0.2s ease;
            display: inline-block;
        }
    </style>
    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-body p-0">
                    <div class="card card-modern border-0 overflow-hidden shadow-lg">
                        <div class="p-4 p-md-5 bg-white position-relative">
                            <!-- Close Button -->
                            <button type="button" class="btn-close position-absolute top-0 end-0 mt-4 me-4"
                                data-bs-dismiss="modal" aria-label="Close"></button>

                            <h4 class="fw-bold mb-4">What are you looking for?</h4>

                            <div class="position-relative mb-4">
                                <i
                                    class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 text-muted fs-5"></i>
                                <input type="text" id="globalSearchInput"
                                    class="form-control form-control-lg bg-light border-0 rounded-pill py-3 ps-5 pe-4 fs-5"
                                    placeholder="Search for products, categories..." autocomplete="off">
                                <div id="searchSpinner"
                                    class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-4 d-none"
                                    role="status"></div>
                            </div>

                            <!-- Search Results Container -->
                            <div id="searchResultsContainer" class="d-none">
                                <h6 class="text-muted text-uppercase small fw-bold letter-spacing-wide mb-3">Products
                                </h6>
                                <div id="searchResultsList" class="d-flex flex-column gap-2">
                                    <!-- Results injected here -->
                                </div>
                                <div class="text-center mt-4 d-none" id="viewAllResultsBtn">
                                    <a href="#" class="text-primary fw-bold text-decoration-none">View all results <i
                                            class="bi bi-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('globalSearchInput');
            const searchSpinner = document.getElementById('searchSpinner');
            const searchResultsContainer = document.getElementById('searchResultsContainer');
            const searchResultsList = document.getElementById('searchResultsList');
            const viewAllResultsBtn = document.getElementById('viewAllResultsBtn');
            const searchModalEl = document.getElementById('searchModal');
            let searchTimeout;

            // Focus input when modal opens
            if (searchModalEl) {
                searchModalEl.addEventListener('shown.bs.modal', function () {
                    searchInput.focus();
                });

                // Clear input when modal closes
                searchModalEl.addEventListener('hidden.bs.modal', function () {
                    searchInput.value = '';
                    searchResultsContainer.classList.add('d-none');
                    searchResultsList.innerHTML = '';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();

                    clearTimeout(searchTimeout);

                    if (query.length < 2) {
                        searchResultsContainer.classList.add('d-none');
                        searchResultsList.innerHTML = '';
                        searchSpinner.classList.add('d-none');
                        return;
                    }

                    searchSpinner.classList.remove('d-none');

                    searchTimeout = setTimeout(() => {
                        fetch(`/api/search?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                searchSpinner.classList.add('d-none');
                                searchResultsList.innerHTML = '';
                                
                                if (data.length === 0) {
                                    searchResultsList.innerHTML = `
                                        <div class="text-center py-4 text-muted">
                                            <i class="bi bi-search fs-2 mb-2 opacity-50"></i>
                                            <p class="mb-0">No products found for "${query}"</p>
                                        </div>
                                    `;
                                    viewAllResultsBtn.classList.add('d-none');
                                } else {
                                    data.forEach(item => {
                                        const html = `
                                            <a href="/product/${item.slug}" class="d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none transition-all dropdown-item-hover">
                                                <div class="rounded-3 overflow-hidden" style="width: 60px; height: 60px; background-color: var(--bg-soft);">
                                                    <img src="${item.image}" alt="${item.name}" class="w-100 h-100 object-fit-cover">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-dark fw-bold">${item.name}</h6>
                                                    <small class="text-muted">${item.category}</small>
                                                </div>
                                                <div class="fw-bold text-primary">
                                                    $${item.price}
                                                </div>
                                            </a>
                                        `;
                                        searchResultsList.insertAdjacentHTML('beforeend', html);
                                    });
                                    
                                    viewAllResultsBtn.classList.remove('d-none');
                                    const viewAllLink = viewAllResultsBtn.querySelector('a');
                                    viewAllLink.href = `/products?q=${encodeURIComponent(query)}`;
                                }
                                
                                searchResultsContainer.classList.remove('d-none');
                            })
                            .catch(err => {
                                searchSpinner.classList.add('d-none');
                                console.error('Search error:', err);
                            });
                    }, 300); // 300ms debounce
                });
            }
        });
    </script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
                easing: 'ease-out-cubic',
            });
        });
    </script>
</body>

</html>