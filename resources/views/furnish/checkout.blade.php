@extends('layouts.furnish')

@section('title', 'Checkout')

@section('content')
<div class="py-5 py-lg-8" style="background-color: var(--bg-soft); min-height: 80vh;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center text-md-start">
                <a href="{{ route('cart.index') }}" class="text-decoration-none text-muted mb-3 d-inline-block fw-semibold">
                    <i class="bi bi-arrow-left me-2"></i> Back to Cart
                </a>
                <h1 class="display-5 mb-2 fw-bold letter-spacing-tight">Secure Checkout</h1>
                <p class="text-muted lead">Complete your details below to place your order.</p>
            </div>
        </div>

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf
            <div class="row g-5">
                <!-- Left Column: Billing Details & Payment -->
                <div class="col-lg-7">
                    
                    <!-- Billing Details -->
                    <div class="card card-modern border-0 mb-5 p-3">
                        <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                            <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                <span class="badge bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
                                Shipping Details
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Country</label>
                                    <input type="text" name="country" class="form-control-modern form-control @error('country') is-invalid @enderror" placeholder="e.g. United States" value="{{ old('country') }}" required>
                                    @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">City</label>
                                    <input type="text" name="city" class="form-control-modern form-control @error('city') is-invalid @enderror" placeholder="e.g. New York" value="{{ old('city') }}" required>
                                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Street Address</label>
                                    <input type="text" name="street" class="form-control-modern form-control @error('street') is-invalid @enderror" placeholder="House number and street name" value="{{ old('street') }}" required>
                                    @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control-modern form-control @error('phone') is-invalid @enderror" placeholder="Phone number for delivery updates" value="{{ old('phone') }}" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card card-modern border-0 p-3">
                        <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                            <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                <span class="badge bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                                Payment Method
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Payment Options -->
                            <div class="d-flex flex-column gap-3">
                                <!-- COD -->
                                <label class="payment-option position-relative border rounded-4 p-4 cursor-pointer transition-all" for="cod">
                                    <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-4" type="radio" name="payment_method" id="cod" value="cod" checked>
                                    <div class="ms-5 ps-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark d-block mb-1">Cash on Delivery (COD)</span>
                                            <small class="text-muted">Pay with cash upon delivery.</small>
                                        </div>
                                        <i class="bi bi-cash-stack text-success fs-3 opacity-50"></i>
                                    </div>
                                </label>

                                <!-- Credit Card -->
                                <label class="payment-option position-relative border rounded-4 p-4 cursor-pointer transition-all" for="credit_card">
                                    <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-4" type="radio" name="payment_method" id="credit_card" value="credit_card" {{ old('payment_method') === 'credit_card' ? 'checked' : '' }}>
                                    <div class="ms-5 ps-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark d-block mb-1">Credit / Debit Card</span>
                                            <small class="text-muted">Secure encrypted payment.</small>
                                        </div>
                                        <div class="d-flex gap-2 opacity-50">
                                            <i class="bi bi-credit-card-fill text-primary fs-4"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Hidden Credit Card Form -->
                            <div id="credit_card_form" class="mt-4 p-4 bg-light rounded-4 border border-soft" style="display: {{ old('payment_method') === 'credit_card' ? 'block' : 'none' }};">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Name on Card</label>
                                        <input type="text" name="card_name" id="card_name" class="form-control-modern form-control bg-white @error('card_name') is-invalid @enderror" placeholder="John Doe" value="{{ old('card_name') }}">
                                        @error('card_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Card Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-0 ps-4 pe-2 rounded-start-pill"><i class="bi bi-credit-card text-muted"></i></span>
                                            <input type="text" name="card_number" id="card_number" class="form-control form-control-modern bg-white rounded-end-pill @error('card_number') is-invalid @enderror" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" placeholder="0000 0000 0000 0000" maxlength="16" value="{{ old('card_number') }}">
                                            @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Expiry</label>
                                        <input type="text" name="card_expiry" id="card_expiry" class="form-control-modern form-control bg-white @error('card_expiry') is-invalid @enderror" placeholder="MM/YY" maxlength="5" value="{{ old('card_expiry') }}">
                                        @error('card_expiry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">CVC</label>
                                        <input type="password" name="card_cvc" id="card_cvc" class="form-control-modern form-control bg-white @error('card_cvc') is-invalid @enderror" placeholder="123" maxlength="3">
                                        @error('card_cvc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Right Column: Order Summary -->
                <div class="col-lg-5">
                    <div class="card card-modern border-0 position-sticky p-2" style="top: 100px;">
                        <div class="card-body p-4 p-lg-5">
                            <h4 class="mb-4 fw-bold">Order Summary</h4>
                            
                            <div class="order-items mb-4 pe-2" style="max-height: 350px; overflow-y: auto;">
                                @php $subtotal = 0; @endphp
                                @foreach($cart->items as $item)
                                    @php 
                                        $subtotal += ($item->price * $item->quantity); 
                                        $img = $item->product ? $item->product->images->first() : null;
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
                                    <div class="d-flex align-items-center py-3 border-bottom border-soft last-border-none">
                                        <div class="position-relative">
                                            <img src="{{ $imgSrc }}" alt="{{ $item->product ? $item->product->name : 'Product' }}" class="rounded-3 object-fit-cover bg-light" style="width: 64px; height: 64px;">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark border border-white" style="font-size: 0.75rem;">
                                                {{ $item->quantity }}
                                            </span>
                                        </div>
                                        <div class="ms-4 flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark">{{ $item->product ? $item->product->name : 'Unknown Product' }}</h6>
                                            <div class="fw-semibold text-muted">
                                                ${{ number_format($item->price, 2) }}
                                            </div>
                                        </div>
                                        <div class="fw-bold text-dark fs-5">
                                            ${{ number_format($item->price * $item->quantity, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span>Subtotal</span>
                                <span class="fw-semibold text-dark">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-4 text-muted">
                                <span>Shipping</span>
                                <span class="fw-semibold text-success">Free</span>
                            </div>
                            
                            <hr class="text-muted opacity-25 mb-4">
                            
                            <div class="d-flex justify-content-between mb-5 align-items-center">
                                <span class="h5 mb-0 fw-bold">Total</span>
                                <span class="h3 mb-0 fw-bold text-dark">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            <button type="submit" class="btn btn-primary-modern btn-modern btn-lg w-100 d-flex justify-content-center align-items-center gap-2">
                                <i class="bi bi-lock-fill opacity-75"></i> Place Order Securely
                            </button>
                            
                            <p class="text-center text-muted small mt-4 mb-0 lh-sm">
                                By placing your order, you agree to our Terms of Service and Privacy Policy.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Payment Option Styles */
    .payment-option {
        background-color: var(--surface);
        border-color: var(--border-soft) !important;
    }
    .payment-option:hover {
        border-color: var(--primary) !important;
        background-color: var(--bg-soft);
    }
    .payment-option input:checked ~ div {
        color: var(--primary) !important;
    }
    /* Highlight selected radio wrapper */
    .payment-option:has(input:checked) {
        border-color: var(--primary) !important;
        background-color: #f8fafc;
        box-shadow: 0 0 0 1px var(--primary);
    }
    .last-border-none:last-child {
        border-bottom: none !important;
    }
    
    /* Custom Scrollbar for Order Items */
    .order-items::-webkit-scrollbar {
        width: 6px;
    }
    .order-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .order-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .order-items::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codRadio = document.getElementById('cod');
        const ccRadio = document.getElementById('credit_card');
        const ccForm = document.getElementById('credit_card_form');
        const ccInputs = ccForm.querySelectorAll('input');

        function toggleCCForm() {
            if (ccRadio.checked) {
                // Smooth slide down
                ccForm.style.display = 'block';
                ccForm.style.opacity = '0';
                setTimeout(() => {
                    ccForm.style.transition = 'opacity 0.3s ease';
                    ccForm.style.opacity = '1';
                }, 10);
                
                // Make inputs required when visible
                ccInputs.forEach(input => input.setAttribute('required', 'required'));
            } else {
                ccForm.style.opacity = '0';
                setTimeout(() => {
                    ccForm.style.display = 'none';
                }, 300);
                
                // Remove required when hidden so form can submit
                ccInputs.forEach(input => {
                    input.removeAttribute('required');
                    input.classList.remove('is-invalid');
                });
            }
        }

        codRadio.addEventListener('change', toggleCCForm);
        ccRadio.addEventListener('change', toggleCCForm);
        
        // Run once on load in case of validation redirect
        toggleCCForm();
    });
</script>
@endsection
