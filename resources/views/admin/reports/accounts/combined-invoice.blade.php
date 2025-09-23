<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #00BDE0;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #00BDE0;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .invoice-info div {
            flex: 1;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #00BDE0;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .user-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .user-header {
            background-color: #00BDE0;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: bold;
        }
        
        .user-stats {
            font-size: 14px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .orders-table th,
        .orders-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .orders-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .orders-table td {
            font-size: 14px;
        }
        
        .user-total {
            text-align: right;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        
        .grand-total {
            background-color: #00BDE0;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .no-orders {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 10px;
            }
            .user-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Bookstore</div>
        <div class="invoice-title">Combined Account Invoice</div>
    </div>
    
    <div class="invoice-info">
        <div>
            <strong>Invoice Date:</strong><br>
            {{ now()->format('F d, Y') }}
        </div>
        <div>
            <strong>Period:</strong><br>
            @if($dateFrom && $dateTo)
                {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @elseif($dateFrom)
                From {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
            @elseif($dateTo)
                Until {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @else
                All Time
            @endif
        </div>
        <div>
            <strong>Generated:</strong><br>
            {{ now()->format('M d, Y H:i:s') }}
        </div>
    </div>
    
    <div class="summary-stats">
        <div class="stat-item">
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">₹{{ number_format($totalAmount, 2) }}</div>
            <div class="stat-label">Total Amount</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">₹{{ $totalOrders > 0 ? number_format($totalAmount / $totalOrders, 2) : '0.00' }}</div>
            <div class="stat-label">Avg Order Value</div>
        </div>
    </div>
    
    @foreach($users as $user)
        <div class="user-section">
            @php
                $userOrders = $user->orders;
                $userOrdersCount = $userOrders->count();
                $userTotal = $userOrders->sum('total_amount');
            @endphp
            
            <div class="user-header">
                <div>
                    <div class="user-name">{{ $user->name }}</div>
                    <div>{{ $user->email }}</div>
                </div>
                <div class="user-stats">
                    <div>Orders: {{ $userOrdersCount }}</div>
                    <div>Total: ₹{{ number_format($userTotal, 2) }}</div>
                </div>
            </div>
            
            @if($userOrders->count() > 0)
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Books</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userOrders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>{{ $order->orderItems->count() }}</td>
                                <td>
                                    @foreach($order->orderItems as $item)
                                        <div style="margin-bottom: 2px;">
                                            {{ $item->book->title }} ({{ $item->quantity }}x)
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <span style="padding: 2px 8px; border-radius: 3px; font-size: 11px; text-transform: uppercase; 
                                        @if($order->status === 'completed') background-color: #d4edda; color: #155724;
                                        @elseif($order->status === 'pending') background-color: #fff3cd; color: #856404;
                                        @elseif($order->status === 'processing') background-color: #cce5ff; color: #004085;
                                        @elseif($order->status === 'shipped') background-color: #e2e3e5; color: #383d41;
                                        @else background-color: #f8d7da; color: #721c24;
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: bold;">₹{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="user-total">
                    User Total: ₹{{ number_format($userTotal, 2) }}
                </div>
            @else
                <div class="no-orders">
                    No orders found for this user in the selected period.
                </div>
            @endif
        </div>
    @endforeach
    
    <div class="grand-total">
        GRAND TOTAL: ₹{{ number_format($totalAmount, 2) }}
    </div>
    
    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }} | Bookstore Admin Panel</p>
        <p>For any queries, please contact the administrator.</p>
    </div>
</body>
</html>
