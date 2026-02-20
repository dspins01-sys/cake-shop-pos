<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ff6b6b;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #ff6b6b;
            margin: 0 0 5px;
            font-size: 28px;
        }
        .header p {
            margin: 2px 0;
            color: #666;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            text-align: center;
        }
        .company-info, .customer-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .company-info h3, .customer-info h3 {
            margin-top: 0;
            color: #ff6b6b;
            font-size: 16px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #ff6b6b;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            font-weight: bold;
            background: #f2f2f2;
        }
        .total-row td {
            border-top: 2px solid #ff6b6b;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
            text-align: center;
            color: #666;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            width: 200px;
            text-align: center;
        }
        .signature .line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #28a745;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .print-button {
            text-align: right;
            margin-bottom: 20px;
        }
        .print-button button {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button button:hover {
            background: #ff5252;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>

    <div class="header">
        <h1>{{ $company['name'] }}</h1>
        <p>{{ $company['address'] }}</p>
        <p>Telp: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
        <p>{{ $company['website'] }}</p>
    </div>

    <div class="invoice-title">
        INVOICE PENGIRIMAN
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div class="company-info" style="width: 48%;">
            <h3>Dari (Penjual):</h3>
            <p><strong>{{ $company['name'] }}</strong></p>
            <p>{{ $company['address'] }}</p>
            <p>Telp: {{ $company['phone'] }}</p>
        </div>

        <div class="customer-info" style="width: 48%;">
            <h3>Kepada (Pembeli):</h3>
            <p><strong>{{ $order->customer_name }}</strong></p>
            <p>{{ $order->address }}</p>
            <p>Telp: {{ $order->customer_phone }}</p>
            <p>Email: {{ $order->customer_email }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td>Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px;">
        <p><strong>Status Pembayaran:</strong> 
            <span class="status-badge">{{ strtoupper($order->payment_status) }}</span>
        </p>
        <p><strong>Status Order:</strong> 
            <span class="status-badge" style="background: 
                @if($order->status == 'completed') #28a745
                @elseif($order->status == 'processing') #ffc107
                @elseif($order->status == 'pending') #dc3545
                @else #6c757d @endif">
                {{ strtoupper($order->status) }}
            </span>
        </p>
        <p><strong>Tanggal Order:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="signature">
        <div>
            <p>Hormat Kami,</p>
            <div class="line">({{ $company['name'] }})</div>
        </div>
        <div>
            <p>Penerima,</p>
            <div class="line">({{ $order->customer_name }})</div>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja di {{ $company['name'] }}!</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan kecuali ada kerusakan</p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>