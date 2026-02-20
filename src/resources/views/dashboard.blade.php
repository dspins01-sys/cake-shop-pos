@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Dashboard</h1>
    
       <!-- QUICK MENU CARDS - TAMBAHAN -->
    <div class="row mb-4">
        <!-- Profile Menu -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 text-primary me-3">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1">My Profile</h5>
                        <p class="card-text text-muted small mb-2">{{ Auth::user()->email }}</p>
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Menu -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 text-success me-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1">Manage Products</h5>
                        <p class="card-text text-muted small mb-2">{{ $totalProducts }} products in catalog</p>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-arrow-right"></i> Go to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END QUICK MENU CARDS -->
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <h2>{{ $totalProducts }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Categories</h5>
                    <h2>{{ $totalCategories }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <h2>{{ $totalOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Low Stock</h5>
                    <h2>{{ $lowStockProducts->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td>{{ $order->status }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No orders yet</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Low Stock Alert</h5>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <ul class="list-group">
                            @foreach($lowStockProducts as $product)
                                <li class="list-group-item d-flex justify-content-between">
                                    {{ $product->name }}
                                    <span class="badge bg-danger">Stock: {{ $product->stock }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">All products have sufficient stock</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection