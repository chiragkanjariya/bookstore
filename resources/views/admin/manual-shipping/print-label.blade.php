@extends('layouts.admin')

@section('title', 'Print Shipping Label & Invoice')

<!-- Add JsBarcode library for barcode generation -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

@section('content')
    <style>
        @media print {

            /* Force landscape orientation */
            @page {
                size: landscape;
                margin: 0.5cm;
            }

            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            /* Ensure split layout prints correctly */
            .split-container {
                display: flex !important;
                page-break-inside: avoid;
            }
        }

        /* Split page layout */
        .split-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            gap: 0;
        }

        .split-half {
            flex: 1;
            padding: 1rem;
            overflow: hidden;
        }

        .split-divider {
            width: 2px;
            background: linear-gradient(to bottom, #000 0%, #000 50%, transparent 50%, transparent 100%);
            background-size: 2px 20px;
            flex-shrink: 0;
        }

        /* Compact styling for split layout */
        .compact-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .compact-section {
            margin-bottom: 0.75rem;
        }

        .compact-text {
            font-size: 0.75rem;
            line-height: 1.2;
        }

        .compact-table {
            font-size: 0.7rem;
        }

        .compact-table th,
        .compact-table td {
            padding: 0.25rem;
        }
    </style>

    <div class="container mx-auto px-6 py-8 no-print">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Print Shipping Label & Invoice</h1>
            <div class="flex space-x-3">
                <a href="{{ route('admin.manual-shipping.index') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Print Label & Invoice
                </button>
            </div>
        </div>
    </div>

    <div class="print-area">
        <div class="split-container">
            <!-- Left Half: Shipping Label -->
            <div class="split-half">
                <div class="border-4 border-black p-4">
                    <h1 class="text-3xl font-bold text-center mb-4">SHIPPING LABEL</h1>

                    <!-- AWB Barcode -->
                    <div class="text-center mb-4">
                        <svg id="awb-barcode"></svg>
                        <p class="text-xs text-gray-600 mt-1">AWB: {{ $order->awb_number }}</p>
                    </div>

                    <!-- From/To Info -->
                    <div class="grid grid-cols-1 gap-3 mb-4">
                        <div class="border-2 border-gray-800 p-3">
                            <h3 class="font-bold text-sm mb-1">FROM:</h3>
                            <p class="font-semibold text-sm">{{ \App\Models\Setting::get('company_name', 'IPDC') }}</p>
                            <p class="compact-text">{{ \App\Models\Setting::get('company_address', '') }}</p>
                            <p class="compact-text">{{ \App\Models\Setting::get('company_place', '') }}</p>
                            <p class="compact-text">Phone: {{ \App\Models\Setting::get('company_phone', '') }}</p>
                        </div>
                        <div class="border-2 border-gray-800 p-3">
                            <h3 class="font-bold text-sm mb-1">TO:</h3>
                            <p class="font-semibold text-base">{{ $order->user->name }}</p>
                            <p class="compact-text">{{ $order->shipping_address['address_line_1'] ?? '' }}</p>
                            @if(isset($order->shipping_address['address_line_2']) && $order->shipping_address['address_line_2'])
                                <p class="compact-text">{{ $order->shipping_address['address_line_2'] }}</p>
                            @endif
                            <p class="compact-text font-semibold">{{ $order->shipping_address['city'] ?? '' }},
                                {{ $order->shipping_address['state'] ?? '' }}
                            </p>
                            <p class="text-xl font-bold mt-1">PIN: {{ $order->shipping_address['postal_code'] ?? '' }}</p>
                            <p class="compact-text mt-1">Phone: {{ $order->shipping_address['phone'] ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="border-t-2 border-gray-800 pt-2">
                        <div class="grid grid-cols-3 gap-2 text-center compact-text">
                            <div>
                                <p class="text-gray-600">Order #</p>
                                <p class="font-bold">{{ $order->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Date</p>
                                <p class="font-bold">{{ $order->created_at->format('d M, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Items</p>
                                <p class="font-bold">{{ $order->orderItems->sum('quantity') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Divider -->
            <div class="split-divider"></div>

            <!-- Right Half: Invoice -->
            <div class="split-half">
                <div class="border-2 border-gray-300 p-4">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h1 class="compact-title font-bold text-gray-900">INVOICE</h1>
                            <p class="compact-text text-gray-600">{{ \App\Models\Setting::get('company_name', 'IPDC') }}
                            </p>
                        </div>
                        <div class="text-right compact-text">
                            <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
                            <p><strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
                            <p><strong>AWB:</strong> {{ $order->awb_number }}</p>
                        </div>
                    </div>

                    <!-- Company & Customer Info -->
                    <div class="grid grid-cols-2 gap-3 mb-3 compact-section">
                        <div>
                            <h3 class="font-semibold text-xs mb-1">From:</h3>
                            <p class="compact-text">{{ \App\Models\Setting::get('company_name', 'IPDC') }}</p>
                            <p class="compact-text">{{ \App\Models\Setting::get('company_place', '') }}</p>
                            <p class="compact-text">{{ \App\Models\Setting::get('company_phone', '') }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xs mb-1">Bill To:</h3>
                            <p class="compact-text font-semibold">{{ $order->user->name }}</p>
                            <p class="compact-text">{{ $order->shipping_address['city'] ?? '' }},
                                {{ $order->shipping_address['state'] ?? '' }}
                            </p>
                            <p class="compact-text">{{ $order->shipping_address['postal_code'] ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="w-full mb-3 compact-table">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="text-left py-1">Item</th>
                                <th class="text-center py-1">Qty</th>
                                <th class="text-right py-1">Price</th>
                                <th class="text-right py-1">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr class="border-b border-gray-200">
                                    <td class="py-1">
                                        <strong>{{ $item->book->title }}</strong>
                                    </td>
                                    <td class="text-center py-1">{{ $item->quantity }}</td>
                                    <td class="text-right py-1">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-right py-1">₹{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="flex justify-end mb-2">
                        <div class="w-48 compact-text">
                            <div class="flex justify-between py-0.5">
                                <span>Subtotal:</span>
                                <span>₹{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-0.5">
                                <span>Shipping:</span>
                                <span>₹{{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-0.5 border-t border-gray-300 pt-1 font-bold">
                                <span>Total:</span>
                                <span>₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="compact-text text-gray-600">
                        <p><strong>Payment:</strong> {{ ucfirst($order->payment_method) }} -
                            {{ ucfirst($order->payment_status) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize Barcode -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generate barcode for AWB number
            JsBarcode("#awb-barcode", "{{ $order->awb_number }}", {
                format: "CODE128",
                width: 1.5,
                height: 60,
                displayValue: true,
                fontSize: 14,
                margin: 5
            });
        });
    </script>
@endsection