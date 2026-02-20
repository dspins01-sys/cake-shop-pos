@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Dashboard</h1>
        <!-- QUICK ACTION BUTTONS - SUPER SIMPLE -->
    <div class="mb-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-primary me-2">
            <i class="fas fa-box"></i> Manage Products
        </a>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
            <i class="fas fa-user"></i> My Profile
        </a>
    </div>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
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