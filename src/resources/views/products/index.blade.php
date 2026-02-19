@extends('layouts.app')

@section('title', isset($category) ? $category->name : 'Our Products')

@push('styles')
<style>
    /* Category Filter Styles - Simple & Clean */
    .category-badge {
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 500;
        text-decoration: none;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        display: inline-block;
        margin: 0 5px 10px;
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
    
    /* Product Card Styles (tetap sederhana) */
    .product-card {
        border: 1px solid #e9ecef;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .product-img {
        height: 200px;
        overflow: hidden;
        background: #f8f9fa;
    }
    
    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .price {
        color: #ff6b6b;
        font-weight: bold;
        font-size: 1.25rem;
    }
</style>
@endpush

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

    <!-- Categories Filter - INI YG DIKASIH STYLE -->
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
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        @if($product->stock <= 0)
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 m-2 rounded">
                                Out of Stock
                            </div>
                        @elseif($product->stock < 5)
                            <div class="position-absolute top-0 start-0 bg-warning text-dark px-3 py-1 m-2 rounded">
                                Low Stock
                            </div>
                        @endif
                        
                        <!-- Product Image -->
                        <a href="{{ route('products.show', $product->slug) }}" class="d-block product-img">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'; this.onerror=null;">
                            @else
                                <div class="h-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-cake-candles fa-4x text-secondary"></i>
                                </div>
                            @endif
                        </a>
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
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price">
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