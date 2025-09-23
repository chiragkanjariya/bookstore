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
            padding: 0;
            color: #333;
            line-height: 1.6;
            font-weight: 500;
        }
        
        .purchase-history-list-area {
            font-weight: 500;
        }
        
        .container {
            background: #fff;
            padding: 1.8rem;
            width: 96%;
            padding-left: 2%;
            padding-right: 2%;
            margin: 0 auto;
        }
        
        .strong {
            font-weight: bold;
        }
        
        .text-end {
            text-align: right;
        }
        
        .gry-color {
            color: #6c757d;
        }
        
        .small {
            font-size: 0.875rem;
        }
        
        .w-50 {
            width: 50%;
        }
        
        .bg-secondary {
            background-color: #6c757d;
            height: 1px;
            border: none;
        }
        
        .border-bottom {
            border-bottom: 1px solid #dee2e6;
        }
        
        .w-100 {
            width: 100%;
        }
        
        .padding {
            padding: 10px;
        }
        
        .text-left {
            text-align: left;
        }
        
        .mt-5 {
            margin-top: 3rem;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        table {
            border-collapse: collapse;
        }
        
        table.padding th,
        table.padding td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        
        thead tr {
            background: #eceff4;
        }
        
        tfoot tr {
            border-top: 2px solid #6c757d;
        }
        
        tfoot tr:last-child {
            border-bottom: 2px solid #6c757d;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 10px;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    
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
