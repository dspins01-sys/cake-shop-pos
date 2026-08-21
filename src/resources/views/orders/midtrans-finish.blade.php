@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="container py-5" style="max-width:760px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5 text-center">
            @if($order->payment_status === 'paid')
                <div class="display-2 text-success mb-3"><i class="fas fa-check-circle"></i></div>
                <h1 class="fw-bold">Payment Successful</h1>
                <p class="text-muted">Terima kasih. Pembayaran {{ $order->order_number }} sudah diterima.</p>
            @elseif($order->payment_status === 'failed')
                <div class="display-2 text-danger mb-3"><i class="fas fa-times-circle"></i></div>
                <h1 class="fw-bold">Payment Failed</h1>
                <p class="text-muted">Pembayaran belum berhasil. Silakan coba lagi.</p>
                <a href="{{ route('payment.midtrans', $order) }}" class="btn btn-primary">Coba Lagi</a>
            @else
                <div class="display-2 text-warning mb-3"><i class="fas fa-clock"></i></div>
                <h1 class="fw-bold">Payment Pending</h1>
                <p class="text-muted">Status pembayaran masih menunggu konfirmasi dari Midtrans.</p>
                <a href="{{ route('payment.midtrans', $order) }}" class="btn btn-primary">Buka Pembayaran</a>
            @endif

            <div class="border rounded p-3 mt-4 text-start">
                <div class="d-flex justify-content-between mb-2"><span>Order</span><strong>{{ $order->order_number }}</strong></div>
                <div class="d-flex justify-content-between"><span>Total</span><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3">Kembali ke Home</a>
        </div>
    </div>
</div>
@endsection
