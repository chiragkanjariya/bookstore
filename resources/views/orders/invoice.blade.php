<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-title {
            font-size: 20px;
            margin: 10px 0;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info div {
            width: 48%;
        }
        .invoice-info h3 {
            margin-bottom: 10px;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            border-top: 2px solid #007bff;
            padding-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print Invoice
        </button>
        <a href="{{ route('orders.show', $order) }}" style="background-color: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-left: 10px;">
            Back to Order
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-name">BookStore</div>
        <div class="invoice-title">INVOICE</div>
        <div>Invoice #{{ $order->order_number }}</div>
    </div>

    <!-- Invoice Information -->
    <div class="invoice-info">
        <div>
            <h3>Bill To:</h3>
            <strong>{{ $order->shipping_address['name'] }}</strong><br>
            {{ $order->shipping_address['phone'] }}<br>
            {{ $order->shipping_address['address_line_1'] }}<br>
            @if(!empty($order->shipping_address['address_line_2']))
            {{ $order->shipping_address['address_line_2'] }}<br>
            @endif
            {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }} {{ $order->shipping_address['postal_code'] }}<br>
            {{ $order->shipping_address['country'] }}
        </div>
        <div>
            <h3>Invoice Details:</h3>
            <strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
            <strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}<br>
            <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}<br>
            @if($order->razorpay_payment_id)
            <strong>Payment ID:</strong> {{ $order->razorpay_payment_id }}<br>
            @endif
        </div>
    </div>

    <!-- Order Items -->
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Author</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>
                    <strong>{{ $item->book->title }}</strong><br>
                    <small>{{ $item->book->category->name }}</small>
                </td>
                <td>{{ $item->book->author }}</td>
                <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₹{{ number_format($order->subtotal, 2) }}</span>
        </div>
        
        @if($order->shipping_cost > 0)
        <div class="total-row">
            <span>Shipping:</span>
            <span>₹{{ number_format($order->shipping_cost, 2) }}</span>
        </div>
        @endif
        
        @if($order->tax_amount > 0)
        <div class="total-row">
            <span>Tax (18% GST):</span>
            <span>₹{{ number_format($order->tax_amount, 2) }}</span>
        </div>
        @endif
        
        <div class="total-row final">
            <span>Total Amount:</span>
            <span>₹{{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    @if($order->notes)
    <!-- Order Notes -->
    <div style="margin-top: 30px;">
        <h3>Order Notes:</h3>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p>For any queries, please contact us at support@bookstore.com</p>
    </div>
</body>
</html>
