@extends('layouts.app')

@section('title', isset($category) ? $category->name : 'Our Products')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">
            @if(isset($category))
                {{ $category->name }}
            @else
                Our Delicious Products
            @endif
        </h1>
        <p class="lead text-muted">
            @if(isset($category))
                {{ $category->description }}
            @else
                Fresh baked cakes, cookies, and pastries made with love
            @endif
        </p>
    </div>

    <!-- Categories Filter -->
    <div class="text-center mb-5">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ route('home') }}" 
               class="category-badge {{ !isset($category) ? 'active' : '' }}">
                All Products
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('products.category', $cat->slug) }}" 
                   class="category-badge {{ isset($category) && $category->id == $cat->id ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4">
        @forelse($products as $product)
            @php
                $availableStock = $product->available_stock;
                $pendingStock = $product->pending_orders_count;
            @endphp
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        @if($availableStock <= 0)
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 m-2 rounded">
                                Out of Stock
                            </div>
                        @elseif($availableStock < 5)
                            <div class="position-absolute top-0 start-0 bg-warning text-dark px-3 py-1 m-2 rounded">
                                Low Stock
                            </div>
                        @endif
                        
                        <!-- Product Image -->
                        <div class="position-relative">
                            @if($availableStock <= 0)
                                <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 m-2 rounded">
                                    Out of Stock
                                </div>
                            @elseif($availableStock < 5)
                                <div class="position-absolute top-0 start-0 bg-warning text-dark px-3 py-1 m-2 rounded">
                                    Low Stock
                                </div>
                            @endif
                            
                            <div class="bg-light product-img d-flex align-items-center justify-content-center" style="height: 200px;">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         class="img-fluid"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'; this.onerror=null;">
                                @else
                                    <i class="fas fa-cake-candles fa-4x text-secondary"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-light text-dark">
                                {{ $product->category->name }}
                            </span>
                        </div>
                        
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text text-muted small">
                            {{ Str::limit($product->description, 60) }}
                        </p>
                        
                        <!-- STOCK INFO - REAL TIME -->
                        <div class="mt-2 mb-2">
                            @if($availableStock > 0)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> 
                                    Tersedia: {{ $availableStock }}
                                </span>
                                @if($pendingStock > 0)
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $pendingStock }} diproses
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle me-1"></i> 
                                    Habis
                                </span>
                                @if($product->stock > 0)
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $product->stock }} diproses
                                    </small>
                                @endif
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price text-danger fw-bold fs-5">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="btn btn-sm btn-outline-primary">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h3>No Products Found</h3>
                <p class="text-muted">Check back later for new products!</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-5">
        {{ $products->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
.category-badge {
    background: #f8f9fa;
    padding: 8px 20px;
    border-radius: 25px;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s;
    display: inline-block;
    margin: 5px;
}
.category-badge:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-2px);
}
.category-badge.active {
    background: #ff6b6b;
    color: white;
    border-color: #ff6b6b;
}
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
.badge {
    font-size: 0.8rem;
    padding: 5px 8px;
}
</style>
@endpush