<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-info { margin-bottom: 20px; }
        .order-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; color: #666; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company['name'] }}</h1>
        <p>{{ $company['address'] }} | {{ $company['phone'] }} | {{ $company['email'] }}</p>
    </div>

    <div class="company-info">
        <h2>INVOICE</h2>
        <p><strong>Invoice No:</strong> {{ $order->order_number }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
        <p><strong>Status:</strong> 
            <span class="status-{{ $order->status }}">
                {{ strtoupper($order->status) }}
            </span>
        </p>
    </div>

    <div class="order-info">
        <h3>Customer Information:</h3>
        <p><strong>Name:</strong> {{ $order->customer_name }}</p>
        <p><strong>Email:</strong> {{ $order->customer_email }}</p>
        <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
        <p><strong>Address:</strong> {{ $order->address }}</p>
    </div>

    <h3>Order Details:</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($order->status == 'pending')
    <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
        <p><strong>Payment Instructions:</strong></p>
        <p>Transfer total amount to:</p>
        <p><strong>BCA</strong> - 1234567890 a.n. SweetCake</p>
        <p><strong>Mandiri</strong> - 9876543210 a.n. SweetCake</p>
        <p>After payment, please upload proof via your order page.</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your order!</p>
        <p>For questions, contact {{ $company['email'] }}</p>
    </div>
</body>
</html>
