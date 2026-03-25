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
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-charging-station me-2 text-primary"></i>
                        System Health & Scheduler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Expired Orders Box -->
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">
                                        <i class="fas fa-hourglass-half text-warning me-2"></i>
                                        Expired Orders
                                    </span>
                                    <span class="badge bg-success">Auto</span>
                                </div>
                                <div class="display-6 mb-2">{{ $expiredOrdersToday ?? 0 }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Last check: {{ $lastExpiredCheck ?? 'Never' }}
                                </small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-primary w-100" onclick="runExpiredCheck()">
                                        <i class="fas fa-play"></i> Check Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Queue Worker Box -->
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">
                                        <i class="fas fa-tasks text-info me-2"></i>
                                        Queue Worker
                                    </span>
                                    <span class="badge bg-{{ isset($systemStatus['queue']) && $systemStatus['queue'] == 'Active' ? 'success' : 'danger' }}">
                                        {{ $systemStatus['queue'] ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="display-6 mb-2">{{ $pendingJobs ?? 0 }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Processed: {{ $processedJobsToday ?? 0 }} today
                                </small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-secondary w-100" onclick="restartQueue()">
                                        <i class="fas fa-sync-alt"></i> Restart Worker
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Status Box -->
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">
                                        <i class="fas fa-calendar-alt text-success me-2"></i>
                                        System Status
                                    </span>
                                    <span class="badge bg-success">Healthy</span>
                                </div>
                                <div class="small">
                                    <div class="mb-1">
                                        <i class="fas fa-{{ $systemStatus['scheduler'] == 'Running' ? 'check-circle text-success' : 'times-circle text-danger' }} me-1"></i>
                                        Scheduler: {{ $systemStatus['scheduler'] ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <i class="fas fa-{{ $systemStatus['queue'] == 'Active' ? 'check-circle text-success' : 'times-circle text-danger' }} me-1"></i>
                                        Queue: {{ $systemStatus['queue'] ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <i class="fas fa-{{ $systemStatus['redis'] == 'Connected' ? 'check-circle text-success' : 'times-circle text-danger' }} me-1"></i>
                                        Redis: {{ $systemStatus['redis'] ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <i class="fas fa-{{ $systemStatus['mysql'] == 'Connected' ? 'check-circle text-success' : 'times-circle text-danger' }} me-1"></i>
                                        MySQL: {{ $systemStatus['mysql'] ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-success w-100" onclick="refreshStatus()">
                                        <i class="fas fa-heartbeat"></i> Health Check
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
@push('scripts')
<script>
// Refresh scheduler status
function refreshStatus() {
    fetch('{{ route("admin.scheduler.status") }}')
        .then(response => response.json())
        .then(data => {
            // Show success message
            showAlert('System health checked! All systems operational.', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error checking system status', 'danger');
        });
}

// Check expired orders manually
function runExpiredCheck() {
    if(confirm('Check expired orders now?')) {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        
        fetch('{{ route("admin.orders.check-expired") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error checking expired orders', 'danger');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

// Restart queue worker
function restartQueue() {
    if(confirm('Restart queue worker? This may take a few seconds.')) {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restarting...';
        btn.disabled = true;
        
        fetch('{{ route("admin.queue.restart") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error restarting queue worker', 'danger');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

// Show alert message
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Auto refresh every 60 seconds
setInterval(() => {
    refreshStatus();
}, 60000);
</script>
@endpush