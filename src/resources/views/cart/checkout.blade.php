@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.process') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('customer_name') is-invalid @enderror" 
                                       id="customer_name" 
                                       name="customer_name" 
                                       value="{{ old('customer_name') }}" 
                                       required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('customer_email') is-invalid @enderror" 
                                       id="customer_email" 
                                       name="customer_email" 
                                       value="{{ old('customer_email') }}" 
                                       required>
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control @error('customer_phone') is-invalid @enderror" 
                                       id="customer_phone" 
                                       name="customer_phone" 
                                       value="{{ old('customer_phone') }}" 
                                       required>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3" 
                                          required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2 text-primary"></i>
                            Payment Method
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="manual" value="manual" checked>
                            <label class="form-check-label" for="manual">
                                <strong>Manual Bank Transfer</strong>
                                <p class="text-muted small mb-0">Transfer ke rekening bank kami, lalu upload bukti pembayaran</p>
                            </label>
                        </div>

                        <!-- Bank Info (akan tampil kalo pilih manual) -->
                        <div id="bankInfo" class="mt-3 p-4 bg-light rounded border">
                            <h6 class="mb-3"><i class="fas fa-university me-2"></i>Bank Account Details:</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-white rounded">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" 
                                             alt="BCA" style="height: 30px; margin-bottom: 10px;">
                                        <p class="mb-1"><strong>Bank BCA</strong></p>
                                        <p class="mb-1">Account No: <strong class="text-primary">1234567890</strong></p>
                                        <p class="mb-0">Account Name: <strong>SweetCake Bakery</strong></p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-white rounded">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Bank_Mandiri_logo.svg" 
                                             alt="Mandiri" style="height: 30px; margin-bottom: 10px;">
                                        <p class="mb-1"><strong>Bank Mandiri</strong></p>
                                        <p class="mb-1">Account No: <strong class="text-primary">9876543210</strong></p>
                                        <p class="mb-0">Account Name: <strong>SweetCake Bakery</strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important:</strong> After making payment, please upload your transfer receipt/proof below.
                                Your order will be processed after payment confirmation.
                            </div>
                        </div>

                        <!-- Payment Proof Upload (tampil setelah pilih manual) -->
                        <div id="paymentProofSection" class="mt-4 p-3 border rounded" style="display: none;">
                            <h6 class="mb-3"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Payment Proof</h6>
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Transfer Receipt (Image/PDF)</label>
                                <input type="file" 
                                       class="form-control @error('payment_proof') is-invalid @enderror" 
                                       id="payment_proof" 
                                       name="payment_proof" 
                                       accept="image/*,.pdf">
                                <small class="text-muted">Max file size: 2MB. Allowed: JPG, PNG, PDF</small>
                                @error('payment_proof')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2 text-primary"></i>
                            Additional Notes (Optional)
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" 
                                  name="notes" 
                                  rows="2" 
                                  placeholder="Any special requests or notes for your order...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-bag me-2 text-primary"></i>
                            Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="mb-3">
                            @foreach($cart as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="fw-bold">{{ $item['name'] }}</span>
                                    <span class="text-muted small d-block">Qty: {{ $item['quantity'] }}</span>
                                </div>
                                <span class="fw-bold">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </span>
                            </div>
                            @endforeach
                        </div>

                        <!-- Price Calculation -->
                        @php
                            $subtotal = $total;
                            $tax = $subtotal * 0.1; // 10% tax
                            $grandTotal = $subtotal + $tax;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span class="text-danger">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>

                        <hr>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-primary w-100 py-3 mb-2">
                            <i class="fas fa-check-circle me-2"></i>
                            Place Order (Manual Transfer)
                        </button>
                        
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Cart
                        </a>

                        <!-- Security Note -->
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Your information is secure
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Toggle payment proof section based on payment method
    document.addEventListener('DOMContentLoaded', function() {
        const manualRadio = document.getElementById('manual');
        const paymentProofSection = document.getElementById('paymentProofSection');
        
        function togglePaymentProof() {
            if (manualRadio.checked) {
                paymentProofSection.style.display = 'block';
            } else {
                paymentProofSection.style.display = 'none';
            }
        }
        
        // Initial check
        togglePaymentProof();
        
        // Listen for changes
        manualRadio.addEventListener('change', togglePaymentProof);
    });
</script>
@endpush

@push('styles')
<style>
.sticky-top {
    position: sticky;
    top: 20px;
    z-index: 100;
}
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
}
.card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.form-check-input:checked {
    background-color: #ff6b6b;
    border-color: #ff6b6b;
}
.btn-primary {
    background-color: #ff6b6b;
    border-color: #ff6b6b;
}
.btn-primary:hover {
    background-color: #ff5252;
    border-color: #ff5252;
}
</style>
@endpush