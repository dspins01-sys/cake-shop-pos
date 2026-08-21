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
    const finishUrl = @json(route('payment.midtrans.finish', $order));

    try {
        if (!token) {
            const response = await fetch(@json(route('midtrans.token', $order)), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'}
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal membuat sesi pembayaran.');
            token = data.token;
        }

        button.addEventListener('click', () => {
            window.snap.pay(token, {
                onSuccess: () => window.location.href = finishUrl,
                onPending: () => window.location.href = finishUrl,
                onError: () => { error.textContent = 'Pembayaran gagal. Silakan coba lagi.'; error.classList.remove('d-none'); },
                onClose: () => {}
            });
        });
    } catch (e) {
        button.disabled = true;
        error.textContent = e.message;
        error.classList.remove('d-none');
    }
});
</script>
@endpush
