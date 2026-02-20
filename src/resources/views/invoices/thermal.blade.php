<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            width: 80mm;
            margin: 0 auto;
            padding: 8mm;
            background: white;
            color: #1e293b;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 5px;
            letter-spacing: -0.5px;
            color: #0f172a;
        }

        .header .brand {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0;
        }

        .divider {
            border-top: 2px dashed #cbd5e1;
            margin: 15px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 8px;
        }

        .bill-to {
            background: #f8fafc;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .bill-to .name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #0f172a;
        }

        .bill-to .detail {
            font-size: 10px;
            color: #475569;
            margin: 3px 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .invoice-number {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .invoice-dates {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }

        .invoice-dates div {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        th {
            text-align: left;
            color: #475569;
            font-weight: 600;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .product-name {
            font-weight: 600;
            color: #0f172a;
        }

        .product-desc {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        .amount {
            font-weight: 600;
            text-align: right;
        }

        .qr-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin: 15px 0;
        }

        .qr-code {
            width: 60px;
            height: 60px;
            background: #0f172a;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 8px;
            text-align: center;
        }

        .qr-text {
            font-size: 10px;
            color: #475569;
        }

        .qr-text strong {
            color: #0f172a;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }

        .totals {
            margin: 15px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #475569;
            margin: 5px 0;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: 14px;
            color: #0f172a;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
        }

        .balance-due {
            text-align: center;
            margin: 15px 0;
            padding: 12px;
            background: #0f172a;
            border-radius: 12px;
            color: white;
        }

        .balance-due .label {
            font-size: 10px;
            opacity: 0.8;
        }

        .balance-due .amount {
            font-size: 20px;
            font-weight: 800;
            color: white;
            text-align: center;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f172a;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: #1e293b;
        }

        @media print {
            .print-btn {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        🖨️ Print Invoice
    </button>

    <div class="header">
        <h1>SweetCake</h1>
        <div class="brand">Fresh Baked Daily</div>
        <div class="brand">Unit 123, Baking District</div>
        <div class="brand">Near City Park</div>
        <div class="brand">Jakarta, 12345</div>
        <div class="brand">sweetcake.com</div>
    </div>

    <div class="divider"></div>

    <div class="section-title">BILL TO</div>
    <div class="bill-to">
        <div class="name">{{ $order->customer_name }}</div>
        <div class="detail">📞 {{ $order->customer_phone }}</div>
        <div class="detail">✉️ {{ $order->customer_email }}</div>
        <div class="detail">📍 {{ $order->address }}</div>
    </div>

    <div class="invoice-header">
        <div class="invoice-number">#{{ $order->order_number }}</div>
        <div class="invoice-dates">
            <div>DATE: {{ $order->created_at->format('M d, Y') }}</div>
            <div>DUE: On Receipt</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th>RATE</th>
                <th>QTY</th>
                <th class="amount">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <div class="product-name">{{ $item->product_name }}</div>
                    @if($item->product_name == 'Chocolate Fudge Cake')
                        <div class="product-desc">Rich chocolate cake with fudge icing</div>
                    @elseif($item->product_name == 'Red Velvet Cake')
                        <div class="product-desc">Classic red velvet with cream cheese</div>
                    @elseif($item->product_name == 'Chocolate Chip Cookies')
                        <div class="product-desc">Classic cookies with chocolate chips (250g)</div>
                    @elseif($item->product_name == 'Butter Cookies')
                        <div class="product-desc">Melt-in-your-mouth butter cookies (250g)</div>
                    @else
                        <div class="product-desc">{{ Str::limit($item->product_name, 30) }}</div>
                    @endif
                </td>
                <td>Rp {{ number_format($item->product_price, 0) }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="amount">Rp {{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="qr-section">
        <div class="qr-code">
            <div>SCAN<br>TO PAY</div>
        </div>
        <div class="qr-text">
            Scan this code<br>to pay online
            <strong>sweetcake.com/pay</strong>
        </div>
    </div>

    <div class="totals">
        <div class="total-row">
            <span>SUBTOTAL</span>
            <span>Rp {{ number_format($order->total, 0) }}</span>
        </div>
        <div class="total-row">
            <span>TAX (10%)</span>
            <span>Rp {{ number_format($order->total * 0.1, 0) }}</span>
        </div>
        <div class="grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total * 1.1, 0) }}</span>
        </div>
    </div>

    <div class="balance-due">
        <div class="label">BALANCE DUE</div>
        <div class="amount">Rp {{ number_format($order->total * 1.1, 0) }}</div>
    </div>

    <div class="footer">
        <div>Thank you for your order!</div>
        <div>For questions, contact us at info@sweetcake.com</div>
        <div style="margin-top: 5px;">{{ $order->order_number }} • {{ $order->created_at->format('Y-m-d') }}</div>
    </div>
</body>
</html>