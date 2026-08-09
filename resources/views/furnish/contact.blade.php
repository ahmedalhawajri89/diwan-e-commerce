@extends('layouts.furnish')

@section('title', 'Contact Us')

@section('content')
    <!-- Modern Hero Section -->
    <section class="py-5 py-lg-8" style="background: linear-gradient(135deg, var(--bg-soft) 0%, #ffffff 100%);">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-4 fw-bold letter-spacing-wide">SUPPORT 24/7</span>
                    <h1 class="display-4 fw-bold mb-3 letter-spacing-tight">Get in Touch</h1>
                    <p class="lead text-muted mb-0" style="font-weight: 400; line-height: 1.8;">
                        Have a question about our products or need help with your order? <br class="d-none d-md-block">
                        Our team is here to assist you. We usually respond within 2 hours.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Contact Section -->
    <section class="py-5 py-lg-8" style="margin-top: -30px;">
        <div class="container position-relative z-1">
            <div class="row g-5">
                
                <!-- Left Side: Contact Info Cards -->
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- Contact Card 1 -->
                        <div class="card card-modern border-0 p-4 d-flex flex-row align-items-start gap-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-geo-alt-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-uppercase letter-spacing-wide text-xs">Headquarters</h6>
                                <p class="text-muted mb-0 small lh-lg">
                                    123 Furniture Street,<br>
                                    Design District, Mumbai 400001,<br>
                                    India
                                </p>
                            </div>
                        </div>

                        <!-- Contact Card 2 -->
                        <div class="card card-modern border-0 p-4 d-flex flex-row align-items-start gap-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-uppercase letter-spacing-wide text-xs">Email Us</h6>
                                <p class="text-muted mb-1 small">For support and questions:</p>
                                <a href="mailto:support@furnish.com" class="text-dark fw-bold text-decoration-none hover-primary transition-all">support@furnish.com</a>
                            </div>
                        </div>

                        <!-- Contact Card 3 -->
                        <div class="card card-modern border-0 p-4 d-flex flex-row align-items-start gap-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-telephone-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-uppercase letter-spacing-wide text-xs">Call Us</h6>
                                <p class="text-muted mb-1 small">Mon-Fri from 9am to 6pm:</p>
                                <a href="tel:+919876543210" class="text-dark fw-bold text-decoration-none hover-primary transition-all">+91 98765 43210</a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Side: Contact Form -->
                <div class="col-lg-8">
                    <div class="card card-modern border-0 p-4 p-lg-5">
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold mb-0">Send a Message</h4>
                            <i class="bi bi-chat-text text-muted opacity-25 fs-3 d-none d-sm-block"></i>
                        </div>
                        
                        <form method="POST" action="{{ route('contact.store') }}" class="needs-validation">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Full Name</label>
                                    <input type="text" name="name" class="form-control-modern form-control @error('name') is-invalid @enderror" placeholder="John Doe" required value="{{ old('name') }}" />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Email Address</label>
                                    <input type="email" name="email" class="form-control-modern form-control @error('email') is-invalid @enderror" placeholder="john@example.com" required value="{{ old('email') }}" />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Subject</label>
                                    <input type="text" name="subject" class="form-control-modern form-control @error('subject') is-invalid @enderror" placeholder="How can we help you?" value="{{ old('subject') }}" required />
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Message</label>
                                    <textarea name="message" class="form-control-modern form-control @error('message') is-invalid @enderror" rows="5" placeholder="Write your details here..." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12 mt-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-lock text-success"></i>
                                        Your data is secure and will not be shared.
                                    </div>
                                    <button type="submit" class="btn btn-primary-modern btn-modern px-5 d-flex align-items-center gap-2" id="submitBtn">
                                        Send Message <i class="bi bi-send-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Embedded Map Section -->
    <section class="pb-0">
        <div class="container-fluid px-0">
            <div class="card border-0 rounded-0 overflow-hidden" style="height: 400px;">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3769.562818737127!2d72.87765531490345!3d19.119677355555584!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7c8f92a2b3a2d%3A0x1a7e7d9b8b94d17a!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1676474830123!5m2!1sen!2sin"
                    width="100%"
                    height="100%"
                    style="border:0; filter: grayscale(100%) contrast(1.2) opacity(0.8);"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- Optional JS for button loading state -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.needs-validation');
            const btn = document.getElementById('submitBtn');
            
            form.addEventListener('submit', function(e) {
                if(form.checkValidity()) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
                    btn.classList.add('disabled');
                }
            });
        });
    </script>
@endsection
