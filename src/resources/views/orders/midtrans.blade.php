@extends('layouts.app')

@section('title', 'Pembayaran ' . $order->order_number)

@section('content')
<div class="container py-5" style="max-width:760px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="mb-3"><i class="fas fa-credit-card fa-3x text-primary"></i></div>
            <h2 class="mb-2">Pembayaran Pesanan</h2>
            <p class="text-muted mb-4">{{ $order->order_number }}</p>
            <div class="alert alert-light border d-flex justify-content-between"><span>Total</span><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
            <button id="pay-button" class="btn btn-primary btn-lg w-100 py-3"><i class="fas fa-lock me-2"></i> Bayar Sekarang</button>
            <a href="{{ route('payment.midtrans.finish', $order) }}" class="btn btn-link mt-2">Lihat status pembayaran</a>
            <div id="payment-error" class="alert alert-danger mt-3 d-none"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ config('payment.midtrans.snap_js_url') }}" data-client-key="{{ config('payment.midtrans.client_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const button = document.getElementById('pay-button');
    const error = document.getElementById('payment-error');
    let token = @json($order->midtrans_token);

    // Use relative URLs so Cloudflare/reverse-proxy scheme detection cannot turn
    // the request into HTTP and cause browser Mixed Content / Failed to fetch.
    const tokenUrl = @json('/payment/midtrans/token/' . $order->id);
    const finishUrl = @json('/payment/midtrans/finish/' . $order->id);

    const showError = (message) => {
        button.disabled = true;
        error.textContent = message;
        error.classList.remove('d-none');
    };

    try {
        if (!token) {
            const response = await fetch(tokenUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            const raw = await response.text();
            let data = {};
            try { data = raw ? JSON.parse(raw) : {}; } catch (_) {}

            if (!response.ok) {
                throw new Error(data.message || `Payment session failed (HTTP ${response.status}).`);
            }

            if (!data.token) {
                throw new Error('Midtrans tidak mengembalikan payment token.');
            }

            token = data.token;
        }

        if (typeof window.snap === 'undefined') {
            throw new Error('Midtrans Snap gagal dimuat. Cek koneksi atau Client Key Sandbox.');
        }

        button.addEventListener('click', () => {
            window.snap.pay(token, {
                onSuccess: () => window.location.href = finishUrl,
                onPending: () => window.location.href = finishUrl,
                onError: () => showError('Pembayaran gagal. Silakan coba lagi.'),
                onClose: () => {}
            });
        });
    } catch (e) {
        showError(e.message || 'Gagal membuat sesi pembayaran.');
        console.error('Midtrans initialization error:', e);
    }
});
</script>
@endpush
