@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('products.category', $product->category->slug) }}" class="text-decoration-none">
                    {{ $product->category->name }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-md-6">
            <div class="bg-light rounded-4 p-0" style="height: 450px; overflow: hidden; position: relative;">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="img-fluid w-100 h-100"
                         style="object-fit: cover; object-position: center;">
                @else
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <i class="fas fa-cake-candles fa-6x text-secondary"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <span class="badge bg-primary mb-3">{{ $product->category->name }}</span>
            
            <h1 class="display-5 fw-bold mb-3">{{ $product->name }}</h1>
            
            <div class="mb-4">
                <span class="display-6 fw-bold text-danger">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            </div>

            <div class="mb-4">
                <h5>Description</h5>
                <p class="text-muted">{{ $product->description }}</p>
            </div>

            <div class="mb-4">
                <h5>Stock Status</h5>
                @php
                    $availableStock = $product->available_stock;
                    $pendingStock = $product->pending_orders_count;
                @endphp
                
                @if($availableStock > 0)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Tersedia: {{ $availableStock }}</strong>
                        @if($pendingStock > 0)
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $pendingStock }} unit sedang diproses untuk pesanan lain
                            </small>
                        @endif
                    </div>
                    
                    <div class="mt-2">
                        <span class="badge bg-info text-white">
                            <i class="fas fa-box me-1"></i> Stok fisik: {{ $product->stock }}
                        </span>
                        @if($pendingStock > 0)
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i> Diproses: {{ $pendingStock }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Stok Habis</strong>
                        @if($product->stock > 0)
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $product->stock }} unit sedang diproses
                            </small>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Quantity and Add to Cart -->
            @if($availableStock > 0)
                <div class="d-flex gap-3 align-items-center mb-4">
                    <div class="input-group" style="width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="{{ $availableStock }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                    </div>

                    <form action="{{ route('cart.add', $product) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="quantity" id="cart-quantity" value="1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                </div>
                
                @if($availableStock < $product->stock)
                    <div class="alert alert-warning py-2">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Hanya {{ $availableStock }} tersedia saat ini. {{ $pendingStock }} lainnya sedang diproses.
                        </small>
                    </div>
                @endif
            @else
                <button class="btn btn-secondary w-100 py-3 mb-4" disabled>
                    <i class="fas fa-cart-plus"></i> Out of Stock
                </button>
            @endif

            <!-- Product Meta -->
            <div class="border-top pt-3">
                <p class="mb-1"><small class="text-muted">SKU: #{{ $product->id }}</small></p>
                <p class="mb-1"><small class="text-muted">Category: {{ $product->category->name }}</small></p>
                @if($product->created_at)
                    <p class="mb-1"><small class="text-muted">Added: {{ $product->created_at->format('d M Y') }}</small></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts && $relatedProducts->count() > 0)
        <div class="mt-5 pt-5">
            <h3 class="mb-4">Related Products</h3>
            <div class="row g-4">
                @foreach($relatedProducts as $related)
                    @php
                        $relatedAvailable = $related->available_stock;
                    @endphp
                    <div class="col-md-3">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                @if($relatedAvailable <= 0)
                                    <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 m-2 rounded z-1">
                                        Habis
                                    </div>
                                @elseif($relatedAvailable < 5)
                                    <div class="position-absolute top-0 start-0 bg-warning text-dark px-3 py-1 m-2 rounded z-1">
                                        Sisa {{ $relatedAvailable }}
                                    </div>
                                @endif
                                
                                <div class="bg-light" style="height: 180px; overflow: hidden;">
                                    @if($related->image)
                                        <img src="{{ asset('storage/'.$related->image) }}" 
                                             alt="{{ $related->name }}" 
                                             class="w-100 h-100"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="fas fa-cake-candles fa-3x text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">{{ $related->name }}</h6>
                                <p class="text-danger fw-bold mb-2">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                                <a href="{{ route('products.show', $related->slug) }}" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function increaseQty() {
    let qty = document.getElementById('quantity');
    let cartQty = document.getElementById('cart-quantity');
    let max = {{ $availableStock }};
    let newValue = parseInt(qty.value) + 1;
    if (newValue <= max) {
        qty.value = newValue;
        if(cartQty) cartQty.value = newValue;
    }
}

function decreaseQty() {
    let qty = document.getElementById('quantity');
    let cartQty = document.getElementById('cart-quantity');
    let newValue = parseInt(qty.value) - 1;
    if (newValue >= 1) {
        qty.value = newValue;
        if(cartQty) cartQty.value = newValue;
    }
}
</script>
@endpush

@push('styles')
<style>
.product-card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    height: 100%;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.product-card:hover img {
    transform: scale(1.05);
    transition: transform 0.5s;
}
.product-card img {
    transition: transform 0.5s;
}
.input-group .btn {
    border: 1px solid #ced4da;
}
.input-group input {
    border-left: none;
    border-right: none;
    text-align: center;
}
.input-group input:focus {
    outline: none;
    box-shadow: none;
}
</style>
@endpush