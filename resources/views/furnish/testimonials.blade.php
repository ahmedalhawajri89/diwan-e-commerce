@extends('layouts.furnish')

@section('title', 'Testimonials')

@section('content')
<!-- Hero Section -->
<section class="py-5 py-lg-10 overflow-hidden position-relative" style="background: linear-gradient(135deg, var(--bg-soft) 0%, #ffffff 100%);">
    <div class="position-absolute bg-primary opacity-5 rounded-circle blur-3xl anim-blob" style="width: 400px; height: 400px; top: -10%; right: -5%; filter: blur(60px);"></div>
    <div class="container text-center position-relative z-1">
        <div class="row justify-content-center">
            <div class="col-lg-8 anim-fade-up">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide">REVIEWS</span>
                <h1 class="display-4 fw-bold mb-3 letter-spacing-tight text-dark">What Our Customers Say</h1>
                <p class="text-muted lead mx-auto" style="max-width: 600px;">
                    We value each voice and every experience. Discover how Furnish has transformed living spaces around the world.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Grid -->
<section class="py-5 py-lg-8" style="margin-top: -40px;">
    <div class="container position-relative z-1">
        <div class="row g-4 g-lg-5">
            
            @php
                $reviews = [
                    ['name' => 'John Deo', 'role' => 'RMO, Company Name', 'text' => "Furnish completely transformed my living room. The quality is unmatched and the modern aesthetic is exactly what I was looking for. Highly recommended!"],
                    ['name' => 'Ria Dong', 'role' => 'CEO, Studio Designs', 'text' => "As a designer, I am extremely particular about furniture. Furnish exceeded my expectations with their attention to detail and sustainable materials."],
                    ['name' => 'Kenneth Gumbs', 'role' => 'Homeowner', 'text' => "The ordering process was seamless, and the delivery was incredibly fast. The sofa I bought is now the centerpiece of my home."],
                    ['name' => 'Sarah Connor', 'role' => 'Interior Architect', 'text' => "I constantly recommend Furnish to my clients. They offer high-end luxury without the ridiculous markup. Brilliant craftsmanship."],
                    ['name' => 'Joseph Perez', 'role' => 'Quality Inspector', 'text' => "I've inspected a lot of furniture in my career. The build quality, stitching, and frame integrity of these pieces are absolutely top-tier."],
                    ['name' => 'Christopher Caruso', 'role' => 'Deputy Manager', 'text' => "Customer service was fantastic. They helped me pick the right fabric for my dining chairs, and the result is stunning."],
                ];
            @endphp

            @foreach($reviews as $index => $review)
            <div class="col-md-6 col-lg-4 anim-fade-up" style="animation-delay: {{ 0.1 * ($index + 1) }}s;">
                <div class="card card-modern border-0 h-100 p-4 p-lg-5 position-relative">
                    <i class="bi bi-quote position-absolute top-0 end-0 mt-4 me-4 text-primary opacity-10" style="font-size: 4rem;"></i>
                    
                    <div class="d-flex mb-4 gap-1 text-accent">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>

                    <p class="text-muted lh-lg mb-4 flex-grow-1" style="font-size: 1.05rem;">
                        "{{ $review['text'] }}"
                    </p>

                    <div class="d-flex align-items-center gap-3 pt-4 border-top border-soft">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                            {{ substr($review['name'], 0, 1) }}
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ $review['name'] }}</h6>
                            <small class="text-muted text-uppercase letter-spacing-wide" style="font-size: 0.7rem;">{{ $review['role'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>

        <div class="row mt-5 pt-4">
            <div class="col-12 text-center anim-fade-up" style="animation-delay: 0.8s;">
                <button class="btn btn-outline-dark btn-modern px-5 py-3 d-inline-flex align-items-center gap-2 group">
                    <span class="fw-bold">Load More Reviews</span>
                    <i class="bi bi-arrow-down transition-transform group-hover:translate-y-1"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-fade-up {
        animation: fadeUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
    }
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
@endsection
