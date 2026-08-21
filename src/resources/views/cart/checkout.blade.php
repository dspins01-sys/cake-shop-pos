@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('cart.checkout.process') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="shipping_destination_id" id="shipping_destination_id">
        <input type="hidden" name="shipping_province" id="shipping_province">
        <input type="hidden" name="shipping_city" id="shipping_city">
        <input type="hidden" name="shipping_district" id="shipping_district">
        <input type="hidden" name="courier" id="courier_value">
        <input type="hidden" name="courier_service" id="courier_service_value">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Customer Information</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Full Name *</label><input class="form-control" name="customer_name" value="{{ old('customer_name') }}" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" class="form-control" name="customer_email" value="{{ old('customer_email') }}" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone *</label><input class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" required></div>
                            <div class="col-12 mb-3"><label class="form-label">Delivery Address *</label><textarea class="form-control" name="address" rows="3" required>{{ old('address') }}</textarea></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Shipping</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Province *</label><select id="province" class="form-select"><option value="">Loading...</option></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">City *</label><select id="city" class="form-select" disabled><option value="">Select province first</option></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">District *</label><select id="district" class="form-select" disabled><option value="">Select city first</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Courier *</label><select id="courier" class="form-select" disabled><option value="">Select courier</option><option value="jne">JNE</option><option value="jnt">J&T</option><option value="sicepat">SiCepat</option><option value="anteraja">AnterAja</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Service *</label><select id="service" class="form-select" disabled><option value="">Select courier first</option></select></div>
                        </div>
                        <div id="shipping-status" class="small text-muted">Package weight: <strong>{{ number_format($weight) }} gram</strong></div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Payment Method</h5></div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="midtrans" value="midtrans" checked>
                            <label class="form-check-label" for="midtrans"><strong>Midtrans</strong><div class="text-muted small">QRIS, GoPay, bank transfer, e-wallet, kartu, dan metode yang tersedia di akun Midtrans.</div></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="manual" value="manual">
                            <label class="form-check-label" for="manual"><strong>Manual Bank Transfer</strong><div class="text-muted small">Upload bukti transfer setelah order dibuat.</div></label>
                        </div>
                        <div id="manualProof" class="mt-3 d-none"><label class="form-label">Payment Proof *</label><input type="file" class="form-control" name="payment_proof" accept="image/*,.pdf"></div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Notes</h5></div>
                    <div class="card-body"><textarea class="form-control" name="notes" rows="2" placeholder="Special request...">{{ old('notes') }}</textarea></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top:20px">
                    <div class="card-header bg-white"><h5 class="mb-0">Order Summary</h5></div>
                    <div class="card-body">
                        @foreach($cart as $item)
                            <div class="d-flex justify-content-between mb-2"><span>{{ $item['name'] }} × {{ $item['quantity'] }}</span><strong>Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</strong></div>
                        @endforeach
                        @php($tax = round($total * 0.10))
                        <hr>
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between"><span>Tax (10%)</span><span>Rp {{ number_format($tax, 0, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between"><span>Shipping</span><span id="shipping-price">Rp 0</span></div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span id="grand-total">Rp {{ number_format($total + $tax, 0, ',', '.') }}</span></div>
                        <button class="btn btn-primary w-100 py-3 mt-3" type="submit"><i class="fas fa-lock me-2"></i>Continue to Payment</button>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 mt-2">Back to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const weight = @json($weight);
const province = document.getElementById('province');
const city = document.getElementById('city');
const district = document.getElementById('district');
const courier = document.getElementById('courier');
const service = document.getElementById('service');
const destination = document.getElementById('shipping_destination_id');
const courierValue = document.getElementById('courier_value');
const serviceValue = document.getElementById('courier_service_value');
const shippingPrice = document.getElementById('shipping-price');
const grandTotal = document.getElementById('grand-total');
const baseTotal = {{ (int) round($total * 1.10) }};

const money = value => 'Rp ' + Number(value).toLocaleString('id-ID');
async function json(url, options = {}) { const r = await fetch(url, {headers:{'Accept':'application/json'}, ...options}); const d = await r.json(); if(!r.ok) throw new Error(d.message || 'Request gagal'); return d.data || []; }
function reset(el, text) { el.innerHTML = `<option value="">${text}</option>`; el.disabled = true; }

(async () => {
    try {
        const data = await json('/shipping/provinces');
        province.innerHTML = '<option value="">Select province</option>' + data.map(x => `<option value="${x.id}" data-name="${x.name}">${x.name}</option>`).join('');
    } catch(e) { province.innerHTML = '<option value="">RajaOngkir unavailable</option>'; }
})();

province.addEventListener('change', async () => {
    reset(city, 'Loading...'); reset(district, 'Select city first'); reset(courier, 'Select courier'); reset(service, 'Select courier first');
    const selected = province.options[province.selectedIndex];
    document.getElementById('shipping_province').value = selected?.dataset.name || '';
    if(!province.value) return;
    try { const data = await json('/shipping/cities/' + province.value); city.innerHTML = '<option value="">Select city</option>' + data.map(x => `<option value="${x.id}" data-name="${x.name}">${x.name}</option>`).join(''); city.disabled=false; } catch(e) { reset(city, 'Failed to load cities'); }
});

city.addEventListener('change', async () => {
    reset(district, 'Loading...'); reset(courier, 'Select courier'); reset(service, 'Select courier first');
    const selected = city.options[city.selectedIndex]; document.getElementById('shipping_city').value = selected?.dataset.name || '';
    if(!city.value) return;
    try { const data = await json('/shipping/districts/' + city.value); district.innerHTML = '<option value="">Select district</option>' + data.map(x => `<option value="${x.id}" data-name="${x.name}">${x.name}</option>`).join(''); district.disabled=false; } catch(e) { reset(district, 'Failed to load districts'); }
});

district.addEventListener('change', () => {
    const selected = district.options[district.selectedIndex];
    document.getElementById('shipping_district').value = selected?.dataset.name || '';
    destination.value = district.value || '';
    courier.disabled = !district.value;
    service.disabled = true; service.innerHTML = '<option value="">Select courier first</option>';
});

courier.addEventListener('change', async () => {
    reset(service, 'Loading services...'); courierValue.value = courier.value; serviceValue.value = '';
    if(!courier.value || !district.value) return;
    try {
        const data = await json('/shipping/costs', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'}, body:JSON.stringify({destination_id:district.value,weight,courier:courier.value})});
        service.innerHTML = '<option value="">Select service</option>' + data.map(x => `<option value="${x.service}" data-cost="${x.cost}" data-etd="${x.etd || ''}">${x.service} — ${money(x.cost)} (${x.etd || '-'} day)</option>`).join(''); service.disabled=false;
    } catch(e) { reset(service, 'Failed to load services'); }
});

service.addEventListener('change', () => {
    const option = service.options[service.selectedIndex]; const cost = Number(option?.dataset.cost || 0);
    serviceValue.value = service.value; shippingPrice.textContent = money(cost); grandTotal.textContent = money(baseTotal + cost);
});

document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', () => document.getElementById('manualProof').classList.toggle('d-none', document.getElementById('manual').checked === false)));
</script>
@endpush
