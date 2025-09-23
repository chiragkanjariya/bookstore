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
@php
    $count = 0;
    $total = $users->count();
@endphp

@foreach($users as $user)
    @php
        $count++;
        $userOrders = $user->orders;
        if($dateFrom) {
            $userOrders = $userOrders->where('created_at', '>=', $dateFrom);
        }
        if($dateTo) {
            $userOrders = $userOrders->where('created_at', '<=', $dateTo);
        }
        $userOrdersCount = $userOrders->count();
    @endphp
    
    @foreach($userOrders as $order)
        <div class="purchase-history-list-area" style="font-weight:500;">
            <div class="container" style="background: #fff;padding: 1.8rem;width:96%;padding-left:2%;padding-right:2%;">
                <div class="row">
                    <div class="col">
                        <div style="margin-left:auto;margin-right:auto;">
                            <div style="margin-bottom: 60px;">
                                <table style="width:100%;">
                                    <tr>
                                        <td>
                                            <div style="height: 50px; line-height: 50px; font-size: 24px; font-weight: bold; color: #00BDE0;">
                                                BOOKSTORE
                                            </div>
                                        </td>
                                        <td style="font-size: 24px; font-weight: 600; text-align:right;" class="text-end strong">
                                            TAX INVOICE
                                        </td>
                                    </tr>
                                </table><br>
                                <table style="width:100%;">
                                    <tr>
                                        <td style="font-size: 1.2rem;" class="strong">BOOKSTORE</td>
                                        <td style="font-size: 1.0rem;" class="text-end strong">{{ ucfirst($user->name) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="gry-color small w-50">{{ config('app.company_address', '123 Business Street, City, State 12345') }}</td>
                                        <td class="gry-color text-end small w-50">
                                            {{ $user->address ?? 'Address not available' }}<br />
                                            Phone: {{ $user->phone ?? 'N/A' }}<br />
                                            Email: {{ $user->email }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color"><br /></td>
                                    </tr>
                                </table>
                                <table style="width:100%;">
                                    <tr>
                                        <td class="strong small gry-color">GSTIN :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ config('app.gst_number', '27AAACT2727Q1ZZ') }}</td>
                                        <td class="strong small text-end small w-50 gry-color"></td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color">PAN No. :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ config('app.pan_number', 'AAACT2727Q') }}</td>
                                        <td class="strong small text-end small w-50 gry-color"></td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color">CIN : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ config('app.cin_number', 'U80904MH2021NPL364738') }}</td>
                                        <td class="strong small text-end small w-50 gry-color"></td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color">Order No : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ORD-{{ $order->id }}</td>
                                        <td class="strong small text-end small w-50 gry-color">Invoice No : &nbsp;&nbsp;INV-{{ $order->id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color">Order Date : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $order->created_at->format('d-M-Y') }}</td>
                                        <td class="strong small text-end small w-50 gry-color">Payment Date : &nbsp;&nbsp;{{ $order->created_at->format('d-M-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="strong small gry-color">Place of : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $user->address ? explode(',', $user->address)[0] ?? 'N/A' : 'N/A' }}</td>
                                        <td class="strong small text-end small w-50 gry-color"></td>
                                    </tr>
                                </table>
                            </div>

                            <hr class="bg-secondary">
                            <div style="">
                                <table class="padding text-left small border-bottom w-100" style="border-collapse: collapse;">
                                    <thead>
                                        <tr class="gry-color" style="background: #eceff4;padding:10px;">
                                            <th width="10%">#</th>
                                            <th width="15%">HSN/SAC</th>
                                            <th width="60%" style="padding-left : 10px;">Book Name</th>
                                            <th width="15%" class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                            $cgst = 0.00;
                                            $sgst = 0.00;
                                            $igst = 0.00;
                                            $sub_total = 0.00;
                                            $grand_total = 0.00;
                                        @endphp
                                        
                                        @foreach($order->orderItems as $orderDetail)
                                            @php
                                                $item_total = $orderDetail->price * $orderDetail->quantity;
                                                $item_cgst = $item_total * 0.09; // 9% CGST
                                                $item_sgst = $item_total * 0.09; // 9% SGST
                                                $item_igst = 0.00; // No IGST for local orders
                                                
                                                $cgst += $item_cgst;
                                                $sgst += $item_sgst;
                                                $igst += $item_igst;
                                                $sub_total += $item_total;
                                                $grand_total += $item_total + $item_cgst + $item_sgst + $item_igst;
                                            @endphp
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>999293</td>
                                                <td class="gry-color" style="padding: 30px;">{{ $orderDetail->book->title }}</td>
                                                <td style="text-align:center" class="text-end">{{ number_format($item_total, 2) }}/-</td>
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top: 2px solid #6c757d;">
                                            <td class="gry-color"></td>
                                            <td class="gry-color"></td>
                                            <td class="gry-color text-end"> <strong>Sub Total:</strong> </td>
                                            <td style="text-align:center" class="text-end"><strong>{{ number_format($sub_total, 2) }}/-</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="gry-color"></td>
                                            <td class="gry-color"></td>
                                            <td class="gry-color text-end"> <strong>ADD CGST :</strong> </td>
                                            <td style="text-align:center" class="text-end"><strong>{{ number_format($cgst, 2) }}/-</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="gry-color"></td>
                                            <td class="gry-color"></td>
                                            <td class="gry-color text-end"> <strong>ADD SGST :</strong> </td>
                                            <td style="text-align:center" class="text-end"><strong>{{ number_format($sgst, 2) }}/-</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="gry-color"></td>
                                            <td class="gry-color"></td>
                                            <td class="gry-color text-end"> <strong>ADD IGST :</strong> </td>
                                            <td style="text-align:center" class="text-end"><strong>{{ number_format($igst, 2) }}/-</strong></td>
                                        </tr>
                                        <tr style="border-top: 2px solid #6c757d; border-bottom: 2px solid #6c757d;">
                                            <td></td>
                                            <td></td>
                                            <td class="gry-color text-end strong"><strong>Final Amount :</strong></td>
                                            <td style="text-align:center" class="text-end"><strong>{{ number_format($grand_total, 2) }}/-</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-5" style="font-size:0.875em;">
                                <p class="mb-4">Whether tax is payable on reverse charge basis - NO</p>
                                <span># This fee receipt is valid subject to cheque realisation or online payment confirmation.</span><br>
                                <span># Subject to Local Jurisdiction where the receipt is generated.</span><br>
                                <span># Fees once paid are neither refundable nor transferable.</span><br>
                                <span># This is computer generated receipt and does not require any stamp or signature.</span>
                            </div>
                            <hr class="bg-secondary">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($count != $total)
            <div class="page-break"></div>
        @endif
    @endforeach
@endforeach
</body>
</html>
