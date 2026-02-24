@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-4">
    <!-- Header dengan Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2" style="width: 40px; height: 40px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">Order #{{ $order->order_number }}</h1>
                <small class="text-muted">Placed on {{ $order->created_at->format('d M Y H:i') }}</small>
            </div>
        </div>
        
        <!-- Status Badge -->
        @if($order->payment_status == 'paid')
            <span class="badge bg-success fs-6 p-3">✓ PAID</span>
        @elseif($order->payment_status == 'waiting_confirmation')
            <span class="badge bg-warning text-dark fs-6 p-3">⏳ WAITING</span>
        @else
            <span class="badge bg-danger fs-6 p-3">✗ UNPAID</span>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <!-- Order Items -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag me-2 text-primary"></i>
                        Order Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item->product_name }}</div>
                                        <small class="text-muted">SKU: #{{ $item->product_id }}</small>
                                    </td>
                                    <td class="text-center">Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold text-danger fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info -->
        <div class="col-md-4">
            <!-- Customer Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $order->customer_name }}</h6>
                            <small class="text-muted">{{ $order->customer_email }}</small>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="d-flex mb-2">
                            <i class="fas fa-phone-alt text-primary me-3" style="width: 20px;"></i>
                            <span>{{ $order->customer_phone }}</span>
                        </div>
                        <div class="d-flex">
                            <i class="fas fa-map-marker-alt text-primary me-3" style="width: 20px;"></i>
                            <span>{{ $order->address }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2 text-primary"></i>
                        Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        <span>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success">PAID</span>
                            @elseif($order->payment_status == 'waiting_confirmation')
                                <span class="badge bg-warning text-dark">WAITING</span>
                            @else
                                <span class="badge bg-danger">UNPAID</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method:</span>
                        <span class="fw-bold">{{ strtoupper($order->payment_method) }}</span>
                    </div>

                    @if($order->payment_proof)
                    <div class="mt-3">
                        <label class="text-muted mb-2">Payment Proof:</label>
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" 
                           class="btn btn-outline-info btn-sm w-100">
                            <i class="fas fa-eye me-2"></i>View Proof
                        </a>
                    </div>
                    @endif

                    @if($order->paid_at)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">Paid at: {{ $order->paid_at->format('d M Y H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Status Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Order Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted mb-2">Current Status:</label>
                        <div>
                            @if($order->status == 'pending')
                                <span class="badge bg-secondary fs-6 p-2">PENDING</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info fs-6 p-2">PROCESSING</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success fs-6 p-2">COMPLETED</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger fs-6 p-2">CANCELLED</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        @if($order->status == 'pending' && $order->payment_status == 'unpaid')
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 mb-2" 
                                        onclick="return confirm('Cancel this order?')">
                                    <i class="fas fa-times-circle me-2"></i>Cancel Order
                                </button>
                            </form>
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary w-100" 
                                        onclick="return confirm('Delete this order permanently?')">
                                    <i class="fas fa-trash me-2"></i>Hapus Order
                                </button>
                            </form>
                        @endif

                        @if($order->payment_status == 'waiting_confirmation')
                            <form action="{{ route('admin.orders.confirm-payment', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-check-circle me-2"></i>Confirm Payment
                                </button>
                            </form>
                        @endif

                        @if($order->payment_status == 'paid' && $order->status == 'pending')
                            <form action="{{ route('admin.orders.process', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-cog me-2"></i>Process Order
                                </button>
                            </form>
                        @endif

                        @if($order->status == 'processing')
                            <form action="{{ route('admin.orders.complete', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-check me-2"></i>Mark as Completed
                                </button>
                            </form>
                        @endif

                        @if($order->payment_status == 'paid')
                            <div class="btn-group mt-2">
                                <a href="{{ route('invoice.print', $order) }}" target="_blank" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                <a href="{{ route('invoice.thermal', $order) }}" target="_blank" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-receipt"></i> Thermal
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection