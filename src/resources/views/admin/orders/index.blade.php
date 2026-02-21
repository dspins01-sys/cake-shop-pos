@extends('layouts.app')

@section('title', 'Manage Orders')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Manage Orders</h1>
    @php
        $hasProcessed = $orders->whereIn('status', ['processing', 'completed'])->count() > 0 
            || $orders->where('payment_status', 'paid')->count() > 0;
    @endphp
    
    @if(!$hasProcessed && $orders->total() > 0)
        <form action="{{ route('admin.orders.clear-all') }}" method="POST" 
              onsubmit="return confirm('⚠️ PERINGATAN!\n\nIni akan menghapus SEMUA order termasuk itemnya!\nData akan hilang permanen.\n\nYakin lanjut?')">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt me-2"></i>Clear All Orders
            </button>
        </form>
    @endif
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                {{ $order->customer_name }}<br>
                                <small class="text-muted">{{ $order->customer_email }}</small>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>
                                @if($order->payment_status == 'unpaid')
                                    <span class="badge bg-danger">Unpaid</span>
                                @elseif($order->payment_status == 'waiting_confirmation')
                                    <span class="badge bg-warning text-dark">Waiting Confirmation</span>
                                @elseif($order->payment_status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-secondary">Pending</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($order->payment_proof)
                                    <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">Detail</a>
                                
                                @if($order->payment_status != 'paid' && $order->status != 'processing' && $order->status != 'completed')
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Hapus order ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No orders yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection