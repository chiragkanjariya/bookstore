<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ManualOrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    public function __construct(protected Builder $query)
    {
    }

    public function query()
    {
        // FromQuery reads in chunks, so the order has to be deterministic or a
        // row can repeat or vanish between chunks. Matches the on-screen list.
        return $this->query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer',
            'Email',
            'Phone',
            'Shipping Address',
            'Postal Code',
            'City',
            'State',
            'Total Amount',
            'Order Date',
            'Shipping Status',
            'Courier',
            'Tracking ID',
            'Shipment Created At',
            'Label Printed At',
        ];
    }

    /**
     * @param Order $order
     */
    public function map($order): array
    {
        $shippingAddress = $order->shipping_address;

        return [
            $order->order_number,
            $order->user->name ?? '',
            $order->user->email ?? '',
            $shippingAddress['phone'] ?? '',
            $shippingAddress['address_line_1'] ?? '',
            $shippingAddress['postal_code'] ?? '',
            $shippingAddress['city'] ?? '',
            $shippingAddress['state'] ?? '',
            (float) $order->total_amount,
            $order->created_at->ist()->format('Y-m-d H:i:s'),
            $order->shipping_partner_status_label,
            $order->manual_courier_name ?? '',
            $order->manual_tracking_id ?? '',
            $order->shipment_created_at ? $order->shipment_created_at->ist()->format('Y-m-d H:i:s') : '',
            $order->label_printed_at ? $order->label_printed_at->ist()->format('Y-m-d H:i:s') : '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => OrdersExport::MONEY_FORMAT,
        ];
    }
}
