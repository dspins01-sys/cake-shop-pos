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
            <div class="bg-light rounded-4 p-5 text-center" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded-3" style="max-height: 350px;">
                @else
                    <i class="fas fa-cake-candles fa-6x text-secondary"></i>
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
                @if($product->stock > 0)
                    <p class="text-success">
                        <i class="fas fa-check-circle"></i> 
                        In Stock ({{ $product->stock }} available)
                    </p>
                @else
                    <p class="text-danger">
                        <i class="fas fa-times-circle"></i> 
                        Out of Stock
                    </p>
                @endif
            </div>

            <!-- Quantity and Add to Cart -->
            <div class="d-flex gap-3 align-items-center mb-4">
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                    <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="{{ $product->stock }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                </div>

                @if($product->stock > 0)
                    <button class="btn btn-primary flex-grow-1" onclick="addToCart()">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                @else
                    <button class="btn btn-secondary flex-grow-1" disabled>
                        <i class="fas fa-cart-plus"></i> Out of Stock
                    </button>
                @endif
            </div>

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
                    <div class="col-md-3">
                        <div class="card product-card h-100">
                            <div class="bg-light p-3 text-center" style="height: 150px; display: flex; align-items: center; justify-content: center;">
                                @if($related->image)
                                    <img src="{{ asset('storage/'.$related->image) }}" alt="{{ $related->name }}" style="max-height: 120px; max-width: 100%;">
                                @else
                                    <i class="fas fa-cake-candles fa-3x text-secondary"></i>
                                @endif
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">{{ $related->name }}</h6>
                                <p class="text-danger fw-bold mb-2">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                                <a href="{{ route('products.show', $related->slug) }}" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    View Details
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
    let max = {{ $product->stock }};
    let newValue = parseInt(qty.value) + 1;
    if (newValue <= max) {
        qty.value = newValue;
    }
}

function decreaseQty() {
    let qty = document.getElementById('quantity');
    let newValue = parseInt(qty.value) - 1;
    if (newValue >= 1) {
        qty.value = newValue;
    }
}

function addToCart() {
    let qty = document.getElementById('quantity').value;
    alert('Added ' + qty + ' item(s) to cart! (Cart feature coming soon)');
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
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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