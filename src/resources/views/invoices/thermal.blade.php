<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        /* Style untuk printer thermal ukuran 58mm / 80mm */
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            width: 80mm; /* Lebar thermal 80mm */
            margin: 0 auto;
            padding: 5px;
            font-size: 10px;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
        }
        .info {
            margin-bottom: 8px;
            font-size: 9px;
        }
        .info div {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9px;
        }
        th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
        }
        td {
            padding: 3px 0;
        }
        .total {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 8px;
        }
        .barcode {
            text-align: center;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            letter-spacing: 2px;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            background: #000;
            color: white;
            font-weight: bold;
            font-size: 8px;
            margin: 3px 0;
        }
        .small {
            font-size: 8px;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company['name'] }}</h2>
        <p>{{ $company['address'] }}</p>
        <p>Telp: {{ $company['phone'] }}</p>
    </div>

    <div class="info">
        <div><strong>No. Order:</strong> {{ $order->order_number }}</div>
        <div><strong>Tanggal:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
        <div><strong>Customer:</strong> {{ $order->customer_name }}</div>
        <div><strong>Alamat:</strong> {{ $order->address }}</div>
        <div><strong>Telp:</strong> {{ $order->customer_phone }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ Str::limit($item->product_name, 15) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->product_price, 0) }}</td>
                <td>{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">Subtotal:</td>
                <td style="text-align: right;">Rp {{ number_format($order->total, 0) }}</td>
            </tr>
            <tr>
                <td style="text-align: left;">Ongkir:</td>
                <td style="text-align: right;">Rp 0</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="text-align: left;">TOTAL:</td>
                <td style="text-align: right;">Rp {{ number_format($order->total, 0) }}</td>
            </tr>
        </table>
    </div>

    <div style="margin: 8px 0;">
        <div><strong>Status:</strong> 
            @if($order->status == 'completed')
                <span style="color: green;">SELESAI</span>
            @elseif($order->status == 'processing')
                <span style="color: orange;">DIPROSES</span>
            @else
                <span style="color: red;">{{ strtoupper($order->status) }}</span>
            @endif
        </div>
        <div><strong>Pembayaran:</strong> {{ strtoupper($order->payment_status) }}</div>
    </div>

    <!-- Barcode sederhana (Order Number) -->
    <div class="barcode">
        {{ $order->order_number }}
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja!</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p class="small">{{ $company['website'] }}</p>
    </div>
</body>
</html>