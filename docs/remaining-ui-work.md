# Remaining Work - Order Details Page and Printing

## Order Details Page Updates

The order details page (`resources/views/admin/orders/show.blade.php`) needs to be updated to replace Shiprocket references with Maruti courier information.

### Changes Needed:

1. **Lines 57-62**: Replace Shiprocket Order ID section with Maruti Document Reference
2. **Lines 125-203**: Replace entire Shiprocket shipping section with Maruti shipping section
3. **Update tracking links**: Change from Shiprocket tracking to Maruti tracking (if available)

### Suggested Implementation:

Replace the Shiprocket section (lines 125-203) with:

```blade
<!-- Maruti Courier Section -->
@if($order->courier_provider == 'shree_maruti')
<div class="mt-6 pt-6 border-t">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Shree Maruti Courier Details</h3>
    <div class="bg-blue-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if($order->courier_document_ref)
            <div>
                <p class="text-gray-600 font-medium">Document Reference</p>
                <p class="text-gray-900 font-mono">{{ $order->courier_document_ref }}</p>
            </div>
            @endif
            @if($order->tracking_number || $order->courier_awb_number)
            <div>
                <p class="text-gray-600 font-medium">AWB / Tracking Number</p>
                <p class="text-gray-900 font-mono">{{ $order->tracking_number ?? $order->courier_awb_number }}</p>
            </div>
            @endif
            <div>
                <p class="text-gray-600 font-medium">Courier Provider</p>
                <p class="text-gray-900">Shree Maruti Courier</p>
            </div>
            @if($order->shipped_at)
            <div>
                <p class="text-gray-600 font-medium">Shipped Date</p>
                <p class="text-gray-900">{{ $order->shipped_at->format('M d, Y h:i A') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@elseif($order->shiprocket_order_id)
<!-- Keep existing Shiprocket section for legacy orders -->
@else
<div class="mt-6 pt-6 border-t">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h3>
    @if($order->is_bulk_purchased)
    <div class="bg-green-50 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            <div>
                <p class="text-green-800 font-medium">Bulk Purchase Order - Free Shipping</p>
                <p class="text-green-700 text-sm">This order qualifies for bulk purchase with free shipping.</p>
            </div>
        </div>
    </div>
    @elseif($order->requires_manual_shipping)
    <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
            <div>
                <p class="text-blue-800 font-medium">Manual Shipping Required</p>
                <p class="text-blue-700 text-sm">This order requires manual shipping arrangement.</p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
            <div>
                <p class="text-yellow-800 font-medium">Pending Shipment</p>
                <p class="text-yellow-700 text-sm">This order is {{ $order->status }} and will be shipped via bulk action.</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
```

## Printing Layout - 2 Orders Per Page

The printing layout needs to be updated in the Manual Shipping Controller to show 2 orders per page.

### File to Update:
`app/Http/Controllers/Admin/ManualShippingController.php`

### Method to Update:
`bulkPrintPdf()`

### Implementation Approach:

1. Group orders in pairs
2. Create a new print template that displays 2 orders side-by-side
3. Each order takes up half the page width
4. Top half: Shipping label (1/4 page)
5. Bottom half: Invoice (1/4 page)

### Suggested CSS for Print Template:

```css
@media print {
    @page {
        size: A4;
        margin: 10mm;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    .order-pair {
        display: flex;
        width: 100%;
        height: 100vh;
    }
    
    .order-column {
        width: 50%;
        padding: 5mm;
    }
    
    .label-section {
        height: 50%;
        border: 1px solid #000;
        padding: 5mm;
    }
    
    .invoice-section {
        height: 50%;
        border: 1px solid #000;
        padding: 5mm;
        margin-top: 5mm;
    }
}
```

### Template Structure:

```blade
@foreach($orderPairs as $pair)
<div class="order-pair {{ !$loop->last ? 'page-break' : '' }}">
    <!-- Left Column - Order 1 -->
    <div class="order-column">
        <div class="label-section">
            <!-- Shipping Label for Order 1 -->
        </div>
        <div class="invoice-section">
            <!-- Invoice for Order 1 -->
        </div>
    </div>
    
    <!-- Right Column - Order 2 (if exists) -->
    @if(isset($pair[1]))
    <div class="order-column">
        <div class="label-section">
            <!-- Shipping Label for Order 2 -->
        </div>
        <div class="invoice-section">
            <!-- Invoice for Order 2 -->
        </div>
    </div>
    @endif
</div>
@endforeach
```

## Priority

The core functionality is complete. These remaining items are UI enhancements:

1. **High Priority**: Order details page Maruti updates (affects admin visibility)
2. **Medium Priority**: Printing layout (nice-to-have for efficiency)

Both can be implemented independently without affecting the core order flow functionality.
