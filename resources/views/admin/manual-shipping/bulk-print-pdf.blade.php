<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Shipping Labels</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            /* DejaVu Sans is bundled with dompdf and has the ₹ glyph; Arial maps
               to core Helvetica, which renders ₹ as "?" */
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Two orders per page layout */
        .order-pair {
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }
        .page-break {
            page-break-before: always;
        }

        .order-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 5mm;
        }

        .order-column:first-child {
            padding-left: 0;
            border-right: 2px dashed #999;
        }

        .order-column:last-child {
            padding-right: 0;
        }

        /* Label section - top half.
           min-height, not height: a fixed height makes long addresses overflow
           the border and collide with the invoice below. dompdf also ignores
           box-sizing, so this is content-box — the box renders at
           118 + 10 (padding) + ~1.6 (border) + 4 (margin) ≈ 133.6mm, and grows
           taller when the address needs it. */
        .label-section {
            min-height: 118mm;
            border: 3px solid black;
            padding: 5mm;
            margin-bottom: 4mm;
        }

        /* Invoice section - bottom half. Worst case is a tall label (~147mm)
           plus this (~101mm), which still clears the 277mm printable height. */
        .invoice-section {
            min-height: 90mm;
            border: 2px solid #666;
            padding: 5mm;
        }

        .label-title {
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
            margin: 0 0 3mm 0;
        }

        .invoice-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin: 0 0 3mm 0;
        }

        .barcode-container {
            text-align: center;
            margin-bottom: 5mm;
        }

        /* The barcode must have an explicit width: at its natural size the PNG is
           ~444px ≈ 117mm, which is wider than the 78mm column and pushes the
           auto-layout table past the page edge, cropping the right column. */
        .barcode-container img,
        .barcode-container svg {
            width: 70mm;
            height: 14mm;
            max-width: 100%;
        }

        .info-box {
            border: 1px solid #333;
            padding: 3mm;
            margin-bottom: 3mm;
        }

        .info-box h3 {
            font-weight: bold;
            font-size: 0.75rem;
            margin: 0 0 2mm 0;
        }

        .info-box p {
            font-size: 0.65rem;
            line-height: 1.2;
            margin: 0 0 1mm 0;
        }

        .compact-table {
            width: 100%;
            font-size: 0.6rem;
            border-collapse: collapse;
        }

        .compact-table th,
        .compact-table td {
            padding: 1mm;
            border-bottom: 1px solid #ddd;
        }

        .compact-table th {
            text-align: left;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-sm {
            font-size: 0.65rem;
        }

        .text-xs {
            font-size: 0.55rem;
        }
    </style>
</head>

<body>
    @php
        use Picqer\Barcode\BarcodeGeneratorPNG;
        $pngGenerator = new BarcodeGeneratorPNG();

        // Group orders in pairs
        $orderPairs = $orders->chunk(2);
        
    @endphp

    @foreach($orderPairs as $pairIndex => $pair)
    <div class="order-pair {{ $pairIndex ? 'page-break' : '' }}">
        @foreach($pair as $order)
                @php
                    $isManualOrBulk = $order->requires_manual_shipping || $order->is_bulk_purchased;
                    $trackingNumber = $isManualOrBulk ? ($order->manual_tracking_id ?: $order->tracking_number) : $order->tracking_number;
                    // Fallback to order number if tracking is missing to prevent barcode errors
                    $trackingNumber = $trackingNumber ?: $order->order_number;
                    $trackingLabel = 'Tracking Number';
                @endphp
                <div class="order-column">
                    <!-- LABEL SECTION (Top Half) -->
                    <div class="label-section">
                        <h1 class="label-title">SHIPPING LABEL</h1>

                        <!-- Tracking Number Barcode -->
                        <div class="barcode-container">
                            <?php
                                $barcodeBase64 = base64_encode(
                                    $pngGenerator->getBarcode(
                                        $trackingNumber,
                                        $pngGenerator::TYPE_CODE_128,
                                        2,
                                        50
                                    )
                                );
                            ?>
                            <img src="data:image/png;base64,{{ $barcodeBase64 }}" alt="Barcode">
                            <p class="text-xs" style="color: #666; margin-top: 1mm;">{{ $trackingLabel }}: {{ $trackingNumber }}</p>
                        </div>

                        <!-- FROM Box -->
                        <div class="info-box">
                            <h3>FROM:</h3>
                            <p class="font-bold">{{ \App\Models\Setting::get('company_name', 'IPDC') }}</p>
                            <p>{{ \App\Models\Setting::get('company_address', '') }}</p>
                            <p>{{ \App\Models\Setting::get('company_place', '') }}</p>
                            <p>Phone: {{ \App\Models\Setting::get('company_phone', '') }}</p>
                        </div>

                        <!-- TO Box -->
                        <div class="info-box">
                            <h3>TO:</h3>
                            <p class="font-bold" style="font-size: 0.75rem;">{{ $order->user->name }}</p>
                            <p>{{ $order->shipping_address['address_line_1'] ?? '' }}</p>
                            @if(isset($order->shipping_address['address_line_2']) && $order->shipping_address['address_line_2'])
                                <p>{{ $order->shipping_address['address_line_2'] }}</p>
                            @endif
                            <p class="font-bold">{{ $order->shipping_address['city'] ?? '' }},
                                {{ $order->shipping_address['state'] ?? '' }}
                            </p>
                            <p style="font-size: 0.85rem; font-weight: bold;">PIN:
                                {{ $order->shipping_address['postal_code'] ?? '' }}
                            </p>
                            <p>Phone: {{ $order->shipping_address['phone'] ?? '' }}</p>
                        </div>

                        <!-- Order Summary -->
                        <div style="border-top: 2px solid #333; padding-top: 2mm; margin-top: 2mm;">
                            <table style="width: 100%; text-align: center; font-size: 0.6rem;">
                                <tr>
                                    <td>
                                        <p style="color: #666; margin: 0;">Order #</p>
                                        <p class="font-bold" style="margin: 0;">{{ $order->order_number }}</p>
                                    </td>
                                    <td>
                                        <p style="color: #666; margin: 0;">Date</p>
                                        <p class="font-bold" style="margin: 0;">{{ $order->created_at->ist()->format('d M, Y') }}</p>
                                    </td>
                                    <td>
                                        <p style="color: #666; margin: 0;">Items</p>
                                        <p class="font-bold" style="margin: 0;">{{ $order->orderItems->sum('quantity') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- INVOICE SECTION (Bottom Half) -->
                    <div class="invoice-section">
                        <h2 class="invoice-title">INVOICE</h2>

                        <!-- Header Info -->
                        <table style="width: 100%; margin-bottom: 3mm; font-size: 0.6rem;">
                            <tr>
                                <td style="width: 50%;">
                                    <p class="font-bold" style="margin: 0;">
                                        {{ \App\Models\Setting::get('company_name', 'IPDC') }}
                                    </p>
                                    <p style="margin: 0;">{{ \App\Models\Setting::get('company_place', '') }}</p>
                                </td>
                                <td class="text-right">
                                    <p style="margin: 0;"><strong>Invoice #:</strong> {{ $order->order_number }}</p>
                                    <p style="margin: 0;"><strong>Date:</strong> {{ $order->created_at->ist()->format('d M, Y') }}</p>
                                    <p style="margin: 0;"><strong>{{ $trackingLabel }}:</strong> {{ $trackingNumber }}</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Bill To -->
                        <div style="margin-bottom: 3mm;">
                            <p class="text-xs" style="margin: 0; color: #666;"><strong>Bill To:</strong></p>
                            <p class="text-sm font-bold" style="margin: 0;">{{ $order->user->name }}</p>
                            <p class="text-xs" style="margin: 0;">{{ $order->shipping_address['city'] ?? '' }},
                                {{ $order->shipping_address['state'] ?? '' }} -
                                {{ $order->shipping_address['postal_code'] ?? '' }}
                            </p>
                        </div>

                        <!-- Items Table -->
                        <table class="compact-table" style="margin-bottom: 3mm;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td><strong>{{ $item->book->title }}</strong></td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                                        <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Totals -->
                        <table style="width: 100%; font-size: 0.6rem; margin-bottom: 2mm;">
                            <tr>
                                <td style="width: 60%;"></td>
                                <td style="width: 40%;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-right">₹{{ number_format($order->subtotal, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Shipping:</td>
                                            <td class="text-right">₹{{ number_format($order->shipping_cost, 2) }}</td>
                                        </tr>
                                        <tr style="border-top: 1px solid #333;">
                                            <td class="font-bold">Total:</td>
                                            <td class="text-right font-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Payment Info -->
                        <p class="text-xs" style="color: #666; margin: 0;"><strong>Payment:</strong>
                            {{ ucfirst($order->payment_method) }} - {{ ucfirst($order->payment_status) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>

</html>