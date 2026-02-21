@extends('layouts.app')

@section('title', 'Order Success')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <div class="display-1 text-success mb-3">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="display-4 fw-bold">Thank You!</h1>
        <p class="lead">Your order has been placed successfully.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2 text-primary"></i>
                        Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Order Number</div>
                        <div class="col-sm-8 fw-bold">{{ $order->order_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Date</div>
                        <div class="col-sm-8">{{ $order->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Total</div>
                        <div class="col-sm-8 fw-bold text-danger fs-5">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-warning text-dark px-3 py-2">PENDING</span>
                            <small class="text-muted ms-2">(Waiting for payment)</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-university me-2 text-primary"></i>
                        Payment Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please transfer the total amount to one of the following bank accounts:
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" 
                                     alt="BCA" style="height: 30px; margin-bottom: 10px;">
                                <p class="mb-1"><strong>Bank BCA</strong></p>
                                <p class="mb-1">Account No: <strong class="text-primary">1234567890</strong></p>
                                <p class="mb-0">Account Name: SweetCake Bakery</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" 
                                     alt="Mandiri" style="height: 30px; margin-bottom: 10px;">
                                <p class="mb-1"><strong>Bank Mandiri</strong></p>
                                <p class="mb-1">Account No: <strong class="text-primary">9876543210</strong></p>
                                <p class="mb-0">Account Name: SweetCake Bakery</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded">
                        <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Important:</h6>
                        <ul class="mb-0 small">
                            <li>Transfer exactly <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></li>
                            <li>Include your order number <strong>{{ $order->order_number }}</strong> in the transfer description</li>
                            <li>After payment, upload your transfer receipt below</li>
                            <li>Order will be processed after payment confirmation (1x24 hours)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>
            Upload Payment Proof
        </h5>
    </div>
    <div class="card-body">
        @if($order->payment_proof)
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                Proof uploaded! Status: <strong>{{ $order->payment_status }}</strong>
            </div>
            @if($order->payment_status == 'waiting_confirmation')
                <p class="text-muted">Your proof is being reviewed by admin.</p>
            @endif
        @else
            <form action="{{ route('order.upload-proof', $order) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(!auth()->check())
        <input type="hidden" name="email" value="{{ $order->customer_email }}">
    @endif
    <div class="mb-3">
        <label class="form-label">Transfer Receipt</label>
        <input type="file" class="form-control @error('payment_proof') is-invalid @enderror" 
               name="payment_proof" accept="image/*,.pdf" required>
        <small class="text-muted">Max file size: 2MB. Allowed: JPG, PNG, PDF</small>
        @error('payment_proof')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-upload me-2"></i>Upload Proof
    </button>
</form>
        @endif
    </div>
</div>

            <div class="text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <a href="#" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-download me-2"></i>Download Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection