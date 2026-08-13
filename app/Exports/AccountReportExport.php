<?php

namespace App\Exports;

use App\Models\Book;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Account report with one dynamic column per book purchased in the filtered set.
 *
 * Deliberately NOT ShouldAutoSize: auto-sizing measures every cell, and this
 * sheet can run to 100+ columns across thousands of rows.
 */
class AccountReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithColumnWidths, WithStrictNullComparison
{
    /** Columns that always precede the per-book columns. */
    private const LEADING_COLUMNS = 8;

    protected Collection $orders;

    /** @var array<int, string> book id => column title, ordered by title */
    protected array $bookColumns;

    /** Quantities keyed by order id, then book id. */
    protected Collection $quantities;

    public function __construct(Builder $query)
    {
        // Everything is resolved once here — headings() and map() must agree on
        // column order, so neither may compute it lazily.
        $this->orders = $query->latest()->get();

        $orderIds = $this->orders->pluck('id');

        $bookIds = OrderItem::whereIn('order_id', $orderIds)
            ->distinct()
            ->pluck('book_id')
            ->filter();

        $this->bookColumns = Book::whereIn('id', $bookIds)
            ->orderBy('title')
            ->orderBy('id')
            ->pluck('title', 'id')
            ->all();

        // order_items keeps no title snapshot, so books removed from inventory
        // still need a column rather than having their quantities disappear.
        foreach ($bookIds->diff(array_keys($this->bookColumns)) as $missingBookId) {
            $this->bookColumns[$missingBookId] = 'Book #' . $missingBookId;
        }

        // Quantity per (order, book) in a single aggregate query. Summed so an
        // order listing the same book on two line items reports the combined qty.
        $this->quantities = OrderItem::whereIn('order_id', $orderIds)
            ->selectRaw('order_id, book_id, SUM(quantity) as qty')
            ->groupBy('order_id', 'book_id')
            ->get()
            ->groupBy('order_id')
            ->map(fn ($rows) => $rows->pluck('qty', 'book_id'));
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Mobile',
            'Country',
            'State',
            'District',
            'Taluka',
            'City',
            ...array_values($this->bookColumns),
            'Total Amount',
            'Shipping Cost',
            'Maruti Shipping Rate',
            'Total Amount Excluding Shipping',
            'Invoice Number',
            'Payment Date',
            'Razorpay Payment ID',
            'Razorpay Order ID',
        ];
    }

    public function map($order): array
    {
        $user = $order->user;
        $shippingAddress = $order->shipping_address;
        $totalExcludingShipping = $order->total_amount - $order->shipping_cost - ($order->maruti_shipping_rate ?? 0);
        $orderQuantities = $this->quantities->get($order->id, collect());

        return [
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? ($shippingAddress['phone'] ?? 'N/A'),
            $shippingAddress['country'] ?? 'India',
            $user->state->name ?? ($shippingAddress['state'] ?? 'N/A'),
            $user->district->name ?? ($shippingAddress['district'] ?? 'N/A'),
            $user->taluka->name ?? ($shippingAddress['taluka'] ?? 'N/A'),
            $shippingAddress['city'] ?? 'N/A',
            // Iterating the same keys as headings() is what guarantees the
            // quantities land under the right book column.
            ...array_map(
                fn ($bookId) => (int) ($orderQuantities[$bookId] ?? 0),
                array_keys($this->bookColumns)
            ),
            (float) $order->total_amount,
            (float) $order->shipping_cost,
            (float) ($order->maruti_shipping_rate ?? 0),
            (float) $totalExcludingShipping,
            'IPDC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            $order->created_at->ist()->format('Y-m-d H:i:s'),
            $order->razorpay_payment_id ?? 'N/A',
            $order->razorpay_order_id ?? 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];

        // The four money columns sit immediately after the last book column.
        for ($offset = 1; $offset <= 4; $offset++) {
            $formats[$this->columnLetter(self::LEADING_COLUMNS + count($this->bookColumns) + $offset)]
                = OrdersExport::MONEY_FORMAT;
        }

        return $formats;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 24, // Name
            'B' => 30, // Email
            'C' => 16, // Mobile
            'D' => 12, // Country
            'E' => 18, // State
            'F' => 18, // District
            'G' => 18, // Taluka
            'H' => 18, // City
        ];

        $bookCount = count($this->bookColumns);

        for ($i = 1; $i <= $bookCount; $i++) {
            $widths[$this->columnLetter(self::LEADING_COLUMNS + $i)] = 14;
        }

        $trailing = [22, 16, 22, 30, 18, 20, 24, 24];

        foreach ($trailing as $i => $width) {
            $widths[$this->columnLetter(self::LEADING_COLUMNS + $bookCount + $i + 1)] = $width;
        }

        return $widths;
    }

    private function columnLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }
}
