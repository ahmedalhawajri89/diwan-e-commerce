@extends('layouts.furnish')

@section('title', 'About Us')

@section('content')
    <!-- Hero Section -->
    <section class="position-relative py-5 py-lg-10 overflow-hidden" style="background: linear-gradient(135deg, var(--bg-soft) 0%, #ffffff 100%); min-height: 50vh; display: flex; align-items: center;">
        <div class="position-absolute bg-primary opacity-10 rounded-circle blur-3xl anim-blob" style="width: 500px; height: 500px; top: -20%; right: -10%; filter: blur(80px);"></div>
        <div class="container position-relative z-1 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8 anim-fade-up">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide">OUR STORY</span>
                    <h1 class="display-3 fw-bold mb-4 letter-spacing-tight text-dark">
                        We Design Spaces <br>
                        <span style="color: var(--accent);">That Inspire.</span>
                    </h1>
                    <p class="lead text-muted mb-0" style="font-weight: 400; line-height: 1.8;">
                        Furnish isn't just about selling furniture. It's about bringing life, comfort, and timeless elegance into the places you call home.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Story Section -->
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 anim-fade-up" style="animation-delay: 0.1s;">
                    <div class="position-relative">
                        <div class="position-absolute bg-soft rounded-4" style="width: 100%; height: 100%; top: 20px; left: -20px; z-index: 0;"></div>
                        <img src="{{ asset('assets/images/about.jpg') }}" alt="Our Design Philosophy" class="img-fluid rounded-4 shadow-lg position-relative z-1" style="object-fit: cover; aspect-ratio: 4/5;">
                        <!-- Floating Badge -->
                        <div class="position-absolute bottom-0 end-0 bg-white p-4 rounded-4 shadow-lg z-2 mb-4 me-n4 d-none d-md-block" style="transform: translateX(20px);">
                            <h3 class="fw-bold text-primary mb-0">10+</h3>
                            <p class="text-muted small mb-0 fw-bold text-uppercase letter-spacing-wide">Years of Excellence</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 anim-fade-up" style="animation-delay: 0.2s;">
                    <div class="ps-lg-5">
                        <h6 class="text-primary fw-bold text-uppercase letter-spacing-wide mb-2">The Beginning</h6>
                        <h2 class="display-6 fw-bold mb-4">Born from a passion for <br> modern craftsmanship.</h2>
                        <p class="text-muted mb-4 lh-lg">
                            It started with a simple belief: that everyone deserves to live in a space that feels entirely their own. We noticed a gap in the market for furniture that was both exquisitely designed and genuinely accessible. 
                        </p>
                        <p class="text-muted mb-5 lh-lg">
                            We don't mass-produce. We curate, we design, and we craft. Every piece in our collection is a testament to our dedication to quality, sustainability, and aesthetic perfection. Welcome to a new standard of living.
                        </p>
                        <div class="d-flex align-items-center gap-4">
                            <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="Founder" class="rounded-circle shadow-sm" width="60" height="60">
                            <div>
                                <h6 class="fw-bold mb-0">Anna Smith</h6>
                                <span class="text-muted small">Founder & Lead Designer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats / Achievements Section -->
    <section class="py-5 bg-dark text-white">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.1s;">
                    <h2 class="display-4 fw-bold mb-2 counter" data-target="25000">0</h2>
                    <p class="text-light-muted text-uppercase letter-spacing-wide small fw-bold">Happy Customers</p>
                </div>
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.2s;">
                    <h2 class="display-4 fw-bold mb-2 counter" data-target="150">0</h2>
                    <p class="text-light-muted text-uppercase letter-spacing-wide small fw-bold">Exclusive Designs</p>
                </div>
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.3s;">
                    <h2 class="display-4 fw-bold mb-2"><span class="counter" data-target="99">0</span>%</h2>
                    <p class="text-light-muted text-uppercase letter-spacing-wide small fw-bold">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="py-5 py-lg-8 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 anim-fade-up" style="animation-delay: 0.1s;">
                    <div class="card card-modern border-0 h-100 p-5 text-center text-md-start">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4 mx-auto mx-md-0" style="width: 70px; height: 70px;">
                            <i class="bi bi-bullseye fs-2"></i>
                        </div>
                        <h3 class="fw-bold mb-3">Our Mission</h3>
                        <p class="text-muted lh-lg mb-0">
                            To democratize high-end interior design by offering premium, handcrafted furniture that transforms houses into extraordinary homes without the luxury markup.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 anim-fade-up" style="animation-delay: 0.2s;">
                    <div class="card card-modern border-0 h-100 p-5 text-center text-md-start">
                        <div class="bg-accent bg-opacity-10 text-accent rounded-circle d-inline-flex align-items-center justify-content-center mb-4 mx-auto mx-md-0" style="width: 70px; height: 70px;">
                            <i class="bi bi-eye fs-2" style="color: var(--accent);"></i>
                        </div>
                        <h3 class="fw-bold mb-3">Our Vision</h3>
                        <p class="text-muted lh-lg mb-0">
                            To become the world's most trusted destination for sustainable, beautifully designed living spaces that inspire creativity and foster deep comfort.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="text-center mb-5 anim-fade-up">
                <h6 class="text-primary fw-bold text-uppercase letter-spacing-wide mb-2">Our Foundation</h6>
                <h2 class="display-6 fw-bold">The Values We Live By</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.1s;">
                    <div class="card card-modern border-0 h-100 p-4 text-center">
                        <div class="mb-4">
                            <i class="bi bi-tree text-success fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Sustainability</h4>
                        <p class="text-muted mb-0">We source eco-friendly materials and partner with responsible manufacturers to ensure a greener planet for tomorrow.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.2s;">
                    <div class="card card-modern border-0 h-100 p-4 text-center">
                        <div class="mb-4">
                            <i class="bi bi-award text-warning fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Uncompromising Quality</h4>
                        <p class="text-muted mb-0">Every curve, every stitch, and every joint is rigorously inspected. If it's not perfect, it doesn't leave our workshop.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up" style="animation-delay: 0.3s;">
                    <div class="card card-modern border-0 h-100 p-4 text-center">
                        <div class="mb-4">
                            <i class="bi bi-heart text-danger fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Customer First</h4>
                        <p class="text-muted mb-0">Your home is your sanctuary. We are dedicated to providing a seamless, stress-free experience from browsing to delivery.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 py-lg-8 bg-light">
        <div class="container text-center">
            <div class="mb-5 anim-fade-up">
                <h6 class="text-primary fw-bold text-uppercase letter-spacing-wide mb-2">The Minds Behind Furnish</h6>
                <h2 class="display-6 fw-bold">Meet Our Creative Team</h2>
            </div>
            <div class="row justify-content-center g-4">
                <!-- Team Member 1 -->
                <div class="col-sm-6 col-lg-3 anim-fade-up" style="animation-delay: 0.1s;">
                    <div class="card border-0 bg-transparent">
                        <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" class="rounded-4 mb-3 shadow-sm object-fit-cover mx-auto" alt="Anna Smith" style="width: 200px; height: 200px;">
                        <h5 class="fw-bold mb-1">Anna Smith</h5>
                        <p class="text-muted small fw-bold text-uppercase letter-spacing-wide mb-0">Creative Director</p>
                    </div>
                </div>
                <!-- Team Member 2 -->
                <div class="col-sm-6 col-lg-3 anim-fade-up" style="animation-delay: 0.2s;">
                    <div class="card border-0 bg-transparent">
                        <img src="{{ asset('assets/images/avatar/avatar-2.jpg') }}" class="rounded-4 mb-3 shadow-sm object-fit-cover mx-auto" alt="Michael Brown" style="width: 200px; height: 200px;">
                        <h5 class="fw-bold mb-1">Michael Brown</h5>
                        <p class="text-muted small fw-bold text-uppercase letter-spacing-wide mb-0">Lead Architect</p>
                    </div>
                </div>
                <!-- Team Member 3 -->
                <div class="col-sm-6 col-lg-3 anim-fade-up" style="animation-delay: 0.3s;">
                    <div class="card border-0 bg-transparent">
                        <img src="{{ asset('assets/images/avatar/avatar-3.jpg') }}" class="rounded-4 mb-3 shadow-sm object-fit-cover mx-auto" alt="Sarah Johnson" style="width: 200px; height: 200px;">
                        <h5 class="fw-bold mb-1">Sarah Johnson</h5>
                        <p class="text-muted small fw-bold text-uppercase letter-spacing-wide mb-0">Head of Marketing</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 py-lg-8 text-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="container anim-fade-up">
            <h2 class="display-5 fw-bold text-white mb-4">Ready to transform your home?</h2>
            <p class="lead text-light-muted mb-5 mx-auto" style="max-width: 600px;">
                Explore our latest collection and find the perfect pieces that speak to your style.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern btn-lg px-5 text-dark" style="background-color: var(--accent);">Shop Collection</a>
                <a href="{{ route('furnish.contact') }}" class="btn btn-outline-light btn-modern btn-lg px-5">Contact Us</a>
            </div>
        </div>
    </section>

<style>
    /* Fade Up Animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-fade-up {
        animation: fadeUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
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
</style>

<script>
    // Intersection Observer to trigger counter animation when stats are visible
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 200; // Lower is slower

        const animateCounters = () => {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText.replace(/,/g, '');
                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc).toLocaleString();
                        setTimeout(updateCount, 15);
                    } else {
                        counter.innerText = target.toLocaleString() + (target > 1000 ? '+' : '');
                    }
                };
                updateCount();
            });
        };

        // Trigger when stats section is in view
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    animateCounters();
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.bg-dark');
        if(statsSection) {
            observer.observe(statsSection);
        }
    });
</script>
@endsection
