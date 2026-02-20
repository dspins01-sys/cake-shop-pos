@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Shopping Cart</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle me-2"></i> Perhatian!</strong>
        <div class="mt-2">
            {!! nl2br(e(session('warning'))) !!}
        </div>
        
        @if(session('stock_issues'))
            <div class="mt-3">
                <button class="btn btn-sm btn-warning" onclick="fixStockIssues()">
                    <i class="fas fa-magic me-1"></i> Sesuaikan Otomatis
                </button>
                <small class="text-muted ms-2">(Quantity akan disesuaikan dengan stok tersedia)</small>
            </div>
        @endif
        
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@push('scripts')
<script>
function fixStockIssues() {
    const issues = @json(session('stock_issues', []));
    
    issues.forEach(issue => {
        if (issue.id && issue.available) {
            // Cari form input untuk produk ini
            const form = document.querySelector(`form[action*="cart/update/${issue.id}"]`);
            if (form) {
                const input = form.querySelector('input[name="quantity"]');
                if (input) {
                    input.value = issue.available;
                    form.submit();
                }
            }
        }
    });
}
</script>
@endpush

    @if(empty($cart))
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added any items yet.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-store"></i> Continue Shopping
            </a>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        @foreach($cart as $id => $item)
                        <div class="row mb-3 pb-3 border-bottom align-items-center">
                            <div class="col-md-2">
                            <a href="{{ route('products.show', $item['slug'] ?? '') }}" class="d-block">
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                    style="width: 100px; height: 100px; overflow: hidden; position: relative;">
                                    @if(isset($item['image']) && $item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" 
                                            alt="{{ $item['name'] }}"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                            class="img-fluid"
                                            onerror="this.style.display='none'; this.parentNode.innerHTML='<i class=\'fas fa-cake-candles fa-2x text-secondary\'></i>';">
                                    @else
                                        <i class="fas fa-cake-candles fa-2x text-secondary"></i>
                                    @endif
                                </div>
                            </a>
                        </div>
                            <div class="col-md-4">
                                <h5 class="mb-1">{{ $item['name'] }}</h5>
                                <p class="text-muted small mb-0">Stock: {{ $item['stock'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-2">
                                <span class="price">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-2">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="d-flex">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" 
                                        min="0" max="{{ $item['stock'] ?? 999 }}" class="form-control form-control-sm" 
                                        style="width: 80px;" onchange="this.form.submit()">
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="fw-bold">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger" 
                                            onclick="return confirm('Remove this item?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                            <form action="{{ route('cart.clear') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Clear all items?')">
                                    <i class="fas fa-trash"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
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
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span class="price">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>

                        <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
