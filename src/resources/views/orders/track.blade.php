@extends('layouts.app')

@section('title', 'Order Tracking')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Order Status</h2>
                <p class="text-muted">Order #{{ $order->order_number }}</p>
            </div>

            <!-- Status Timeline -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="position-relative">
                        <!-- Progress Line -->
                        <div class="progress" style="height: 3px; position: absolute; top: 25px; left: 15%; right: 15%; z-index: 1;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ 
                                    $order->status == 'completed' ? '100' : 
                                    ($order->status == 'processing' ? '75' : 
                                    ($order->payment_status == 'paid' ? '50' : 
                                    ($order->payment_status == 'waiting_confirmation' ? '25' : '0'))) 
                                 }}%"></div>
                        </div>

                        <!-- Status Steps -->
                        <div class="row text-center position-relative" style="z-index: 2;">
                            <div class="col-3">
                                <div class="rounded-circle bg-{{ $order->payment_status != 'unpaid' ? 'success' : 'secondary' }} text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="mt-2 mb-0 fw-bold">Order Placed</p>
                                <small class="text-muted">{{ $order->created_at->format('d M H:i') }}</small>
                            </div>
                            <div class="col-3">
                                <div class="rounded-circle bg-{{ $order->payment_status == 'paid' || $order->payment_status == 'waiting_confirmation' ? 'success' : 'secondary' }} text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <p class="mt-2 mb-0 fw-bold">Payment</p>
                                <small class="text-muted">
                                    @if($order->payment_status == 'paid')
                                        Confirmed
                                    @elseif($order->payment_status == 'waiting_confirmation')
                                        Waiting Confirmation
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                            <div class="col-3">
                                <div class="rounded-circle bg-{{ $order->status == 'processing' || $order->status == 'completed' ? 'success' : 'secondary' }} text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-box"></i>
                                </div>
                                <p class="mt-2 mb-0 fw-bold">Processing</p>
                                <small class="text-muted">
                                    @if($order->status == 'processing')
                                        In Progress
                                    @elseif($order->status == 'completed')
                                        Done
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                            <div class="col-3">
                                <div class="rounded-circle bg-{{ $order->status == 'completed' ? 'success' : 'secondary' }} text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <p class="mt-2 mb-0 fw-bold">Delivered</p>
                                <small class="text-muted">
                                    @if($order->status == 'completed')
                                        Completed
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Order Number:</strong> {{ $order->order_number }}</p>
                            <p class="mb-2"><strong>Order Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                            <p class="mb-2"><strong>Customer:</strong> {{ $order->customer_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Total:</strong> Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                            <p class="mb-2"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                            <p class="mb-2"><strong>Payment Status:</strong> 
                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'waiting_confirmation' ? 'warning' : 'danger') }}">
                                    {{ $order->payment_status }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <h6 class="mt-3 mb-2">Order Items:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
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
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <a href="{{ route('order.track.form') }}" class="btn btn-outline-primary">
                    <i class="fas fa-search me-2"></i>Track Another Order
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection