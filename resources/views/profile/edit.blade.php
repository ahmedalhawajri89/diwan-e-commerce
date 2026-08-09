@extends('layouts.furnish')

@section('title', 'My Dashboard')

@section('content')
<div class="py-5 py-lg-8" style="background-color: var(--bg-soft); min-height: 80vh;">
    <div class="container">
        
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold mb-0 letter-spacing-tight">My Dashboard</h2>
                <p class="text-muted">Manage your profile, track orders, and update settings.</p>
            </div>
        </div>

        <div class="row g-4 g-lg-5">
            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <div class="card card-modern border-0 mb-4 position-sticky" style="top: 100px;">
                    <div class="card-body p-0">
                        <!-- Profile Summary -->
                        <div class="text-center p-4 border-bottom border-soft bg-light rounded-top-lg">
                            <div class="position-relative d-inline-block mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 15px; height: 15px;" title="Online"></span>
                            </div>
                            <h5 class="fw-bold mb-1 text-dark">{{ auth()->user()->name }}</h5>
                            <p class="text-muted small mb-2 text-truncate">{{ auth()->user()->email }}</p>
                            @if(auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists())
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small fw-bold"><i class="bi bi-shield-check me-1"></i> Admin</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold"><i class="bi bi-check-circle me-1"></i> Verified Member</span>
                            @endif
                        </div>

                        <!-- Navigation -->
                        <div class="nav flex-column nav-pills custom-nav-pills p-3 gap-1" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active text-start rounded-3 px-3 py-2 fw-semibold d-flex align-items-center" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
                                <i class="bi bi-person me-3 opacity-75 fs-5"></i> Profile Info
                            </button>
                            <button class="nav-link text-start rounded-3 px-3 py-2 fw-semibold d-flex align-items-center" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab" aria-controls="v-pills-orders" aria-selected="false">
                                <i class="bi bi-box-seam me-3 opacity-75 fs-5"></i> My Orders
                                @if($orders->count() > 0)
                                    <span class="badge bg-primary ms-auto rounded-pill">{{ $orders->count() }}</span>
                                @endif
                            </button>
                            <button class="nav-link text-start rounded-3 px-3 py-2 fw-semibold d-flex align-items-center" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">
                                <i class="bi bi-gear me-3 opacity-75 fs-5"></i> Settings
                            </button>
                            
                            <hr class="my-2 border-soft">
                            
                            <form method="POST" action="{{ route('logout') }}" class="w-100">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger text-decoration-none text-start w-100 px-3 py-2 fw-semibold d-flex align-items-center">
                                    <i class="bi bi-box-arrow-right me-3 opacity-75 fs-5"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- ==========================================
                         TAB 1: PROFILE INFO
                    =========================================== -->
                    <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                        <div class="card card-modern border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 fw-bold">Personal Information</h4>
                                <i class="bi bi-person text-muted opacity-50 fs-3"></i>
                            </div>
                            <div class="card-body p-4">
                                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                                    @csrf
                                </form>

                                <form method="post" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('patch')

                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Full Name</label>
                                            <input type="text" class="form-control-modern form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Email Address</label>
                                            <input type="email" class="form-control-modern form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3">
                                            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                                            <div>
                                                <p class="mb-1 fw-bold">Your email address is unverified.</p>
                                                <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline text-dark">Click here to re-send the verification email.</button>
                                            </div>
                                        </div>
                                        @if (session('status') === 'verification-link-sent')
                                            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                                                A new verification link has been sent to your email address.
                                            </div>
                                        @endif
                                    @endif

                                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-soft">
                                        <button type="submit" class="btn btn-primary-modern btn-modern px-5">Save Changes</button>
                                        @if (session('status') === 'profile-updated')
                                            <span class="text-success small fw-bold bg-success bg-opacity-10 px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Profile Updated</span>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ==========================================
                         TAB 2: MY ORDERS
                    =========================================== -->
                    <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                        <div class="card card-modern border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 fw-bold">Order History</h4>
                                <i class="bi bi-box-seam text-muted opacity-50 fs-3"></i>
                            </div>
                            <div class="card-body p-4">
                                @if($orders->isEmpty())
                                    <div class="text-center py-5">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                            <i class="bi bi-cart-x text-muted opacity-50" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="fw-bold">No orders found</h5>
                                        <p class="text-muted mb-4">Looks like you haven't placed an order yet.</p>
                                        <a href="{{ route('furnish.products') }}" class="btn btn-primary-modern btn-modern px-4">Start Shopping</a>
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-4">
                                        @foreach($orders as $order)
                                            <div class="card border-soft rounded-4 overflow-hidden shadow-sm transition-all hover-shadow">
                                                <!-- Order Header -->
                                                <div class="card-header bg-light border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                    <div>
                                                        <span class="text-muted small text-uppercase letter-spacing-wide fw-bold d-block mb-1">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="fw-semibold text-dark"><i class="bi bi-calendar3 text-muted me-2"></i>{{ $order->created_at->format('M d, Y') }}</span>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                                            <i class="bi bi-credit-card me-1"></i> {{ ucfirst($order->payment_status) }}
                                                        </span>
                                                        <span class="badge {{ $order->status === 'delivered' ? 'bg-success' : ($order->status === 'cancelled' ? 'bg-danger' : 'bg-primary') }} rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                                            <i class="bi bi-truck me-1"></i> {{ ucfirst($order->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Order Body -->
                                                <div class="card-body p-0">
                                                    <ul class="list-group list-group-flush border-top border-soft">
                                                        @foreach($order->items as $item)
                                                            <li class="list-group-item px-4 py-3 border-bottom border-soft bg-transparent d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center">
                                                                    @php
                                                                        $imgSrc = asset('assets/images/product-img-1.jpg');
                                                                        if($item->product_image_snapshot) {
                                                                            if (str_starts_with($item->product_image_snapshot, 'http')) {
                                                                                $imgSrc = $item->product_image_snapshot;
                                                                            } elseif (str_starts_with($item->product_image_snapshot, 'assets/')) {
                                                                                $imgSrc = asset($item->product_image_snapshot);
                                                                            } else {
                                                                                $imgSrc = asset('storage/' . $item->product_image_snapshot);
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <div class="position-relative">
                                                                        <img src="{{ $imgSrc }}" alt="{{ $item->product_name_snapshot }}" class="rounded-3 shadow-sm object-fit-cover" style="width: 56px; height: 56px;">
                                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark border border-white" style="font-size: 0.65rem;">
                                                                            {{ $item->quantity }}
                                                                        </span>
                                                                    </div>
                                                                    <span class="ms-4 fw-bold text-dark">{{ $item->product_name_snapshot }}</span>
                                                                </div>
                                                                <div class="fw-bold text-dark">
                                                                    ${{ number_format($item->price, 2) }}
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                
                                                <!-- Order Footer -->
                                                <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small fw-semibold">Total Items: {{ $order->items->sum('quantity') }}</span>
                                                    <div class="text-end">
                                                        <span class="text-muted small fw-bold me-2 text-uppercase letter-spacing-wide">Total</span>
                                                        <span class="fw-bold text-primary fs-4">${{ number_format($order->total_price, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ==========================================
                         TAB 3: SETTINGS
                    =========================================== -->
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                        <!-- Update Password -->
                        <div class="card card-modern border-0 mb-4">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 fw-bold">Update Password</h4>
                                <i class="bi bi-shield-lock text-muted opacity-50 fs-3"></i>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small mb-4">Ensure your account is using a long, random password to stay secure.</p>
                                
                                <form method="post" action="{{ route('password.update') }}">
                                    @csrf
                                    @method('put')

                                    <div class="mb-4">
                                        <label for="update_password_current_password" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Current Password</label>
                                        <input type="password" class="form-control-modern form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="update_password_current_password" name="current_password" autocomplete="current-password">
                                        @error('current_password', 'updatePassword')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label for="update_password_password" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">New Password</label>
                                            <input type="password" class="form-control-modern form-control @error('password', 'updatePassword') is-invalid @enderror" id="update_password_password" name="password" autocomplete="new-password">
                                            @error('password', 'updatePassword')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="update_password_password_confirmation" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Confirm Password</label>
                                            <input type="password" class="form-control-modern form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
                                            @error('password_confirmation', 'updatePassword')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-soft">
                                        <button type="submit" class="btn btn-dark btn-modern px-5">Update Password</button>
                                        @if (session('status') === 'password-updated')
                                            <span class="text-success small fw-bold bg-success bg-opacity-10 px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Password Updated</span>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Account -->
                        <div class="card card-modern border-danger border-opacity-25 border-1 shadow-sm rounded-4">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="text-danger mb-0 fw-bold">Danger Zone</h5>
                                <i class="bi bi-exclamation-triangle text-danger opacity-50 fs-4"></i>
                            </div>
                            <div class="card-body p-4 bg-danger bg-opacity-10 rounded-bottom-4">
                                <p class="text-dark mb-4">Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.</p>
                                <button type="button" class="btn btn-danger btn-modern px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                                    <i class="bi bi-trash me-2"></i> Delete Account
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <div class="modal-header bg-danger text-white border-0 py-4">
                    <h5 class="modal-title fw-bold" id="confirmUserDeletionModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Delete Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">
                        Are you absolutely sure you want to delete your account? This action cannot be undone. Please enter your password to confirm.
                    </p>
                    
                    <div class="mt-4">
                        <label for="password" class="form-label text-muted small text-uppercase letter-spacing-wide fw-bold mb-2">Password</label>
                        <input type="password" class="form-control-modern form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" placeholder="Enter your password" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light btn-modern fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-modern px-4 shadow-sm">Confirm Deletion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Custom Nav Pills for Sidebar */
    .custom-nav-pills .nav-link {
        color: var(--text-muted);
        transition: var(--transition);
        border: 1px solid transparent;
    }
    .custom-nav-pills .nav-link:hover {
        background-color: var(--bg-soft);
        color: var(--primary);
    }
    .custom-nav-pills .nav-link.active {
        background-color: var(--primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }
    .custom-nav-pills .nav-link.active i {
        color: white !important;
        opacity: 1 !important;
    }
    
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md) !important;
    }
</style>

@if($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var deleteModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
            deleteModal.show();
        });
    </script>
@endif
@endsection
