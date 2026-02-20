@extends('layouts.app')

@section('title', 'Track Order')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-search me-2 text-primary"></i>
                        Track Your Order
                    </h4>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('order.track') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Order Number</label>
                            <input type="text" name="order_number" class="form-control" 
                                   placeholder="e.g. INV-20260220-XXXX" required>
                            <small class="text-muted">Found in your order confirmation email</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="your@email.com" required>
                            <small class="text-muted">The email you used when ordering</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-search me-2"></i>Track Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection