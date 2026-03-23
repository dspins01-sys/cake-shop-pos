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
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                                <span class="badge bg-warning text-dark">WAITING CONFIRMATION</span>
                            @else
                                <span class="badge bg-danger">UNPAID</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Method:</span>
                        <span class="fw-bold">{{ strtoupper($order->payment_method) }}</span>
                    </div>

                    @if($order->payment_proof)
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Payment Proof:</span>
                        <span>
                            <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i> View Proof
                            </a>
                        </span>
                    </div>
                    @endif

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
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">CANCELLED</span>
                            @else
                                <span class="badge bg-danger">{{ strtoupper($order->status) }}</span>
                            @endif
                        </span>
                    </div>

                    <!-- ACTION BUTTONS SECTION -->
                    <hr>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- CONFIRM PAYMENT BUTTON - For orders waiting confirmation -->
                        @if($order->payment_status == 'waiting_confirmation')
                            <form action="{{ route('admin.orders.confirm-payment', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" 
                                        onclick="return confirm('Confirm payment for this order? This will update stock and notify customer.')">
                                    <i class="fas fa-check-circle me-2"></i> Confirm Payment
                                </button>
                            </form>
                        @endif

                        <!-- PROCESS ORDER BUTTON - For paid orders not yet processing -->
                        @if($order->payment_status == 'paid' && $order->status == 'pending')
                            <form action="{{ route('admin.orders.process', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-cogs me-2"></i> Process Order
                                </button>
                            </form>
                        @endif

                        <!-- COMPLETE ORDER BUTTON - For processing orders -->
                        @if($order->status == 'processing')
                            <form action="{{ route('admin.orders.complete', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-check-double me-2"></i> Complete Order
                                </button>
                            </form>
                        @endif

                        <!-- CANCEL BUTTON - For unpaid or waiting orders -->
                        @if(in_array($order->payment_status, ['unpaid', 'waiting_confirmation']) && $order->status != 'cancelled')
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100" 
                                        onclick="return confirm('Cancel this order?')">
                                    <i class="fas fa-times-circle me-2"></i> Cancel Order
                                </button>
                            </form>
                        @endif

                        <!-- DELETE BUTTON - Only for cancelled or pending unpaid orders -->
                        @if(($order->payment_status == 'unpaid' && $order->status == 'pending') || $order->status == 'cancelled')
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary w-100" 
                                        onclick="return confirm('Delete this order permanently?')">
                                    <i class="fas fa-trash me-2"></i> Delete Order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection