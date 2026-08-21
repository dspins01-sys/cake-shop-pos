@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="container py-5" style="max-width:760px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5 text-center">
            <div id="payment-status-content">
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
                    <div class="spinner-border text-warning my-3" role="status" aria-hidden="true"></div>
                    <div class="small text-muted">Memeriksa status otomatis...</div>
                    <a href="{{ route('payment.midtrans', $order) }}" class="btn btn-primary mt-3">Buka Pembayaran</a>
                @endif
            </div>

            <div class="border rounded p-3 mt-4 text-start">
                <div class="d-flex justify-content-between mb-2"><span>Order</span><strong>{{ $order->order_number }}</strong></div>
                <div class="d-flex justify-content-between"><span>Total</span><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3">Kembali ke Home</a>
        </div>
    </div>
</div>
@endsection

@if($order->payment_status !== 'paid' && $order->payment_status !== 'failed')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusUrl = @json(route('midtrans.status', $order));
    const finishUrl = @json(route('payment.midtrans.finish', $order));
    let busy = false;

    const poll = async () => {
        if (busy) return;
        busy = true;
        try {
            const response = await fetch(statusUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' },
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();

            if (data.payment_status === 'paid' || data.midtrans_status === 'settlement') {
                window.location.replace(finishUrl + '?updated=1');
            } else if (['failed', 'expire', 'cancel', 'deny'].includes(data.midtrans_status)) {
                window.location.replace(finishUrl + '?failed=1');
            }
        } catch (e) {
            console.warn('Payment status polling failed:', e);
        } finally {
            busy = false;
        }
    };

    poll();
    setInterval(poll, 2000);
});
</script>
@endpush
@endif
