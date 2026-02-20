@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Order #{{ $order->order_number }}</h1>
        <p class="text-muted mb-0">Placed on {{ $order->created_at->format('d M Y H:i') }}</p>
    </div>
    <div class="btn-group me-2">
        <a href="{{ route('invoice.print', $order) }}" target="_blank" class="btn btn-info">
            <i class="fas fa-print"></i> Print A4
        </a>
        <a href="{{ route('invoice.thermal', $order) }}" target="_blank" class="btn btn-secondary">
            <i class="fas fa-receipt"></i> Thermal (Struk)
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Order Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>Rp {{ number_format($order->total, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info -->
        <div class="col-md-4">
            <!-- Customer Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                    <p class="mb-1">{{ $order->customer_email }}</p>
                    <p class="mb-1">{{ $order->customer_phone }}</p>
                    <p class="mb-0">{{ $order->address }}</p>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                                        <strong>Status:</strong><br>
                        @if($order->payment_status == 'unpaid')
                            <span class="badge bg-danger">Unpaid</span>
                        @elseif($order->payment_status == 'waiting_confirmation')
                            <span class="badge bg-warning text-dark">Waiting Confirmation</span>
                        @elseif($order->payment_status == 'paid')
                            <span class="badge bg-success">Paid</span>
                        @endif
                    </p>
                    <p class="mb-2">
                        <strong>Method:</strong><br>
                        {{ strtoupper($order->payment_method) }}
                    </p>
                    @if($order->payment_proof)
                    <p class="mb-2">
                        <strong>Proof:</strong><br>
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i> View Proof
                        </a>
                    </p>
                    @endif
                    @if($order->paid_at)
                    <p class="mb-0">
                        <strong>Paid at:</strong><br>
                        {{ $order->paid_at->format('d M Y H:i') }}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Current Status:</strong><br>
                        @if($order->status == 'pending')
                            <span class="badge bg-secondary">Pending</span>
                        @elseif($order->status == 'processing')
                            <span class="badge bg-info">Processing</span>
                        @elseif($order->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($order->status == 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @endif
                    </p>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
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

                        @if($order->status != 'cancelled' && $order->status != 'completed')
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" 
                                onsubmit="return confirm('Cancel this order?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-times me-2"></i>Cancel Order
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