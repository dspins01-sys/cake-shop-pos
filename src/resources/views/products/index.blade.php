@extends('layouts.app')

@section('title', isset($category) ? $category->name : 'Our Products')

@push('styles')
<style>
    /* Category Filter Styles */
    .category-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-bottom: 30px;
    }
    
    .category-badge {
        padding: 10px 24px;
        border-radius: 30px;
        font-weight: 500;
        text-decoration: none;
        color: #555;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        letter-spacing: 0.3px;
    }
    
    .category-badge:hover {
        background: #e9ecef;
        color: #333;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #dee2e6;
    }
    
    .category-badge.active {
        background: linear-gradient(135deg, #ff6b6b, #ff5252);
        color: white;
        border-color: #ff5252;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }
    
    .category-badge.active:hover {
        background: linear-gradient(135deg, #ff5252, #ff3838);
        box-shadow: 0 6px 15px rgba(255, 107, 107, 0.4);
    }

    /* Product Card Styles */
    .product-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        background: white;
        height: 100%;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .product-img {
        height: 220px;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
    }
    
    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    
    .product-card:hover .product-img img {
        transform: scale(1.1);
    }
    
    .product-img .placeholder-icon {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }
    
    .stock-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 10;
        padding: 5px 15px;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .stock-badge.out {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }
    
    .stock-badge.low {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #333;
    }
    
    .category-label {
        display: inline-block;
        background: #f8f9fa;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        line-height: 1.4;
        height: 2.8rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .product-description {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 15px;
        height: 2.6rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .product-price {
        color: #ff6b6b;
        font-weight: 700;
        font-size: 1.3rem;
    }
    
    .btn-detail {
        background: white;
        border: 2px solid #ff6b6b;
        color: #ff6b6b;
        border-radius: 25px;
        padding: 5px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-detail:hover {
        background: #ff6b6b;
        color: white;
        transform: scale(1.05);
    }
    
    /* Header Styles */
    .page-header {
        position: relative;
        padding: 40px 0 20px;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        position: relative;
        display: inline-block;
    }
    
    .page-header h1:after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #ff6b6b, #ffb6b6);
        border-radius: 2px;
    }
    
    .page-header .lead {
        color: #6c757d;
        font-size: 1.1rem;
        margin-top: 20px;
    }
    
    /* Pagination Styles */
    .pagination {
        gap: 5px;
    }
    
    .pagination .page-link {
        border-radius: 10px;
        color: #555;
        border: 1px solid #e9ecef;
        padding: 8px 15px;
        transition: all 0.3s;
    }
    
    .pagination .page-link:hover {
        background: #ff6b6b;
        color: white;
        border-color: #ff6b6b;
        transform: translateY(-2px);
    }
    
    .pagination .active .page-link {
        background: #ff6b6b;
        border-color: #ff6b6b;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="text-center page-header">
        <h1 class="fw-bold">
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
    <div class="category-filter">
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

    <!-- Products Grid -->
    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card product-card">
                    <div class="position-relative">
                        @if($product->stock <= 0)
                            <div class="stock-badge out">
                                <i class="fas fa-times-circle me-1"></i> Out of Stock
                            </div>
                        @elseif($product->stock < 5)
                            <div class="stock-badge low">
                                <i class="fas fa-exclamation-triangle me-1"></i> Low Stock ({{ $product->stock }})
                            </div>
                        @endif
                        
                        <!-- Product Image -->
                        <a href="{{ route('products.show', $product->slug) }}" class="product-img d-block">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'; this.onerror=null;">
                            @else
                                <div class="placeholder-icon">
                                    <i class="fas fa-cake-candles fa-4x text-secondary"></i>
                                </div>
                            @endif
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <span class="category-label">
                            <i class="fas fa-tag me-1"></i> {{ $product->category->name }}
                        </span>
                        
                        <h5 class="product-title">{{ $product->name }}</h5>
                        <p class="product-description">{{ Str::limit($product->description, 60) }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="btn btn-detail btn-sm">
                                <i class="fas fa-eye me-1"></i> Detail
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