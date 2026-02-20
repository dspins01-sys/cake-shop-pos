@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Dashboard</h1>
    
    <!-- QUICK ACTION BUTTONS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <h5>Manage Products</h5>
                    <p class="text-muted small">Add, edit, or delete products</p>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right"></i> Go to Products
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-3">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h5>Manage Orders</h5>
                    <p class="text-muted small">View and process orders</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-arrow-right"></i> Go to Orders
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-info mb-3">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5>Reports</h5>
                    <p class="text-muted small">Sales and inventory reports</p>
                    <a href="#" class="btn btn-info btn-sm text-white">
                        <i class="fas fa-arrow-right"></i> View Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5>My Profile</h5>
                    <p class="text-muted small">Manage your account</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-arrow-right"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
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
                        <div class="table-responsive">
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
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No orders yet</p>
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
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $product->category->name ?? 'No category' }}</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">{{ $product->stock }} left</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center py-3">All products have sufficient stock</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center text-muted mt-4">
        <small>Logged in as <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->role }})</small>
    </div>
</div>
@endsection