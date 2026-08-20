<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    /**
     * Rupee display format. Values are written as raw floats so the cells stay
     * numeric and can be summed in the spreadsheet.
     */
    public const MONEY_FORMAT = '"₹"#,##0.00';

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
            'Shipping Status',
            'Payment Status',
            'Subtotal',
            'Shipping Cost',
            'Maruti Shipping Rate',
            'Total Amount',
            'Tracking Number',
            'Courier Provider',
            'Order Date',
            'Shipment Created At',
            'Label Printed At',
            'Items Count',
        ];
    }

    /**
     * @param Order $order
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->user->name ?? '',
            $order->user->email ?? '',
            $order->shipping_partner_status_label,
            ucfirst($order->payment_status),
            (float) $order->subtotal,
            (float) $order->shipping_cost,
            (float) ($order->maruti_shipping_rate ?? 0),
            (float) $order->total_amount,
            $order->tracking_number ?? $order->courier_awb_number ?? 'N/A',
            $order->courier_provider ? ucfirst(str_replace('_', ' ', $order->courier_provider)) : 'N/A',
            $order->created_at->ist()->format('Y-m-d H:i:s'),
            $order->shipment_created_at ? $order->shipment_created_at->ist()->format('Y-m-d H:i:s') : '',
            $order->label_printed_at ? $order->label_printed_at->ist()->format('Y-m-d H:i:s') : '',
            $order->orderItems->count(),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => self::MONEY_FORMAT,
            'G' => self::MONEY_FORMAT,
            'H' => self::MONEY_FORMAT,
            'I' => self::MONEY_FORMAT,
        ];
    }
}
