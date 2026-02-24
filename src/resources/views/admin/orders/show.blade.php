@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-4">
    <!-- Header dengan Back -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
        <small class="text-muted">Placed on {{ $order->created_at->format('d M Y H:i') }}</small>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
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
                            {{ $item->product_name }}
                            <small class="text-muted d-block">SKU: #{{ $item->product_id }}</small>
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
                        <td class="text-end fw-bold text-danger">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <!-- Customer Info -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">{{ $order->customer_name }}</h6>
                    <p class="mb-2">
                        <i class="fas fa-envelope text-secondary me-2" style="width: 20px;"></i>
                        {{ $order->customer_email }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone text-secondary me-2" style="width: 20px;"></i>
                        {{ $order->customer_phone }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-secondary me-2" style="width: 20px;"></i>
                        {{ $order->address }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment & Status -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
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
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Method:</span>
                        <span class="fw-bold">{{ strtoupper($order->payment_method) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Order Status:</span>
                        <span>
                            @if($order->status == 'pending')
                                <span class="badge bg-secondary">PENDING</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">PROCESSING</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">COMPLETED</span>
                            @else
                                <span class="badge bg-danger">{{ strtoupper($order->status) }}</span>
                            @endif
                        </span>
                    </div>

                    @if($order->payment_status == 'unpaid' && $order->status == 'pending')
                        <hr>
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100" 
                                        onclick="return confirm('Cancel this order?')">
                                    Cancel Order
                                </button>
                            </form>
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary w-100" 
                                        onclick="return confirm('Delete this order?')">
                                    Hapus Order
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection