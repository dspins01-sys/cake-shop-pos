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

            <div id="payment-live-status" class="alert alert-info mt-3 d-none"></div>

            @if(!config('payment.midtrans.production'))
            <div id="sandbox-test-box" class="mt-3 p-3 border rounded bg-light text-start">
                <div class="fw-semibold mb-1"><i class="fas fa-flask me-1"></i> Sandbox Test</div>
                <div class="small text-muted mb-2">Menjalankan flow payment yang sama seperti webhook Midtrans: paid → processing → stock → WhatsApp.</div>
                <button id="sandbox-test-button" type="button" class="btn btn-outline-dark btn-sm">
                    <i class="fas fa-check-circle me-1"></i> Simulate Payment Success
                </button>
                <div id="sandbox-test-status" class="small text-muted mt-2"></div>
            </div>
            @endif

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
    const liveStatus = document.getElementById('payment-live-status');
    const sandboxTestButton = document.getElementById('sandbox-test-button');
    const sandboxTestStatus = document.getElementById('sandbox-test-status');
    let token = @json($order->midtrans_token);
    let pollTimer = null;
    let pollBusy = false;

    const tokenUrl = @json('/payment/midtrans/token/' . $order->id);
    const statusUrl = @json('/payment/midtrans/status/' . $order->id);
    const finishUrl = @json('/payment/midtrans/finish/' . $order->id);
    const simulateUrl = @json('/payment/midtrans/simulate-paid/' . $order->id);

    const showError = (message) => {
        button.disabled = true;
        error.textContent = message;
        error.classList.remove('d-none');
    };

    const setLiveStatus = (message, type = 'info') => {
        if (!liveStatus) return;
        liveStatus.className = `alert alert-${type} mt-3`;
        liveStatus.textContent = message;
    };

    const checkPaymentStatus = async () => {
        if (pollBusy) return null;
        pollBusy = true;
        try {
            const response = await fetch(statusUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' },
                cache: 'no-store'
            });
            if (!response.ok) return null;

            const data = await response.json();
            if (data.payment_status === 'paid' || data.midtrans_status === 'settlement') {
                if (pollTimer) clearInterval(pollTimer);
                setLiveStatus('Pembayaran berhasil diterima. Mengalihkan...', 'success');
                window.location.href = finishUrl;
                return data;
            }

            if (['failed', 'expire', 'cancel', 'deny'].includes(data.midtrans_status)) {
                if (pollTimer) clearInterval(pollTimer);
                setLiveStatus('Pembayaran gagal atau kedaluwarsa. Silakan coba lagi.', 'danger');
                return data;
            }

            setLiveStatus('Menunggu konfirmasi pembayaran dari Midtrans...', 'info');
            return data;
        } catch (e) {
            console.warn('Midtrans status polling failed:', e);
            return null;
        } finally {
            pollBusy = false;
        }
    };

    const startPaymentPolling = () => {
        if (pollTimer) clearInterval(pollTimer);
        setLiveStatus('Menunggu konfirmasi pembayaran dari Midtrans...', 'info');
        checkPaymentStatus();
        pollTimer = setInterval(checkPaymentStatus, 2000);
    };

    @if(!config('payment.midtrans.production'))
    sandboxTestButton?.addEventListener('click', async () => {
        sandboxTestButton.disabled = true;
        sandboxTestStatus.textContent = 'Menjalankan simulasi settlement...';
        setLiveStatus('Memproses simulasi pembayaran...', 'info');

        try {
            const response = await fetch(simulateUrl, {
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
                throw new Error(data.message || `Simulation failed (HTTP ${response.status}).`);
            }

            sandboxTestStatus.textContent = 'Settlement berhasil. Menunggu UI update...';
            startPaymentPolling();
        } catch (e) {
            sandboxTestButton.disabled = false;
            sandboxTestStatus.textContent = e.message || 'Simulasi gagal.';
            setLiveStatus(e.message || 'Simulasi pembayaran gagal.', 'danger');
        }
    });
    @endif

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
            if (!response.ok) throw new Error(data.message || `Payment session failed (HTTP ${response.status}).`);
            if (!data.token) throw new Error('Midtrans tidak mengembalikan payment token.');
            token = data.token;
        }

        if (typeof window.snap === 'undefined') {
            throw new Error('Midtrans Snap gagal dimuat. Cek koneksi atau Client Key Sandbox.');
        }

        button.addEventListener('click', () => {
            window.snap.pay(token, {
                onSuccess: () => startPaymentPolling(),
                onPending: () => startPaymentPolling(),
                onError: () => {
                    setLiveStatus('Pembayaran gagal. Silakan coba lagi.', 'danger');
                    startPaymentPolling();
                },
                onClose: () => {
                    // Do not treat closing the Snap popup as a failed payment.
                    checkPaymentStatus();
                }
            });
        });

        // Keep the page in sync even if Midtrans sends the notification while the popup is open.
        startPaymentPolling();
    } catch (e) {
        showError(e.message || 'Gagal membuat sesi pembayaran.');
        console.error('Midtrans initialization error:', e);
    }
});
</script>
@endpush
