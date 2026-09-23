<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The order list as a file: a PDF to print or file, a Word document to
 * edit, or a CSV to open in Excel. It takes whatever the list's filters are
 * showing — every page of it, not just the one on screen — through the same
 * OrderList query, so the file and the screen hold the same orders.
 */
class OrdersExport
{
    public const FORMATS = ['pdf', 'docx', 'csv'];

    public function __construct(private OrderList $list) {}

    public function download(Request $request, string $format): Response
    {
        abort_unless(in_array($format, self::FORMATS, true), 404);

        $report = $this->build($request);
        $name = 'orders-' . $report['generatedAt']->format('Y-m-d');

        return match ($format) {
            'pdf' => Pdf::loadView('admin.reports.pdf.orders', $report)
                ->setPaper('a4', 'landscape')
                ->setOption('isPhpEnabled', true)
                ->download("{$name}.pdf"),
            'docx' => response()
                ->download((new OrdersDocxGenerator($report))->save(), "{$name}.docx")
                ->deleteFileAfterSend(true),
            'csv' => $this->csv($report, "{$name}.csv"),
        };
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, filterLabel: string, count: int, cancelledCount: int, openValue: float, generatedAt: \Illuminate\Support\Carbon}
     */
    public function build(Request $request): array
    {
        $filters = $this->list->filters($request);

        $orders = $this->list->query($filters)
            ->with([
                'user',
                // A product retired since is still what the customer bought.
                'orderItems.product' => fn ($query) => $query->withTrashed(),
                'orderItems.productVariant.color',
            ])
            ->get();

        return [
            'rows' => $orders->map(fn (Order $order) => $this->row($order))->all(),
            'filterLabel' => $this->list->describe($filters),
            'count' => $orders->count(),
            'cancelledCount' => $orders->where('status', 'cancelled')->count(),
            // Nobody pays for a cancelled order, so it stays out of the value.
            'openValue' => (float) $orders->where('status', '!=', 'cancelled')->sum('total_amount'),
            'generatedAt' => now(),
        ];
    }

    /** One order, in the shape all three formats print. */
    private function row(Order $order): array
    {
        return [
            'number' => $order->order_number,
            'placed' => $order->created_at,
            'customer' => $order->user->fullname ?? 'Guest',
            'ordered_for' => $order->ordered_for,
            'channel' => $order->channel_label,
            'items' => $order->orderItems->map(fn (OrderItem $item) => $this->line($item))->all(),
            // The number the order is known by at the counter or in procurement.
            'reference' => (string) ($order->isPurchaseRequest() ? $order->pr_number : $order->payment_reference),
            'status' => Order::statusLabel($order->status) . ($order->isAwaitingPayment() ? ' (awaiting payment)' : ''),
            'total' => (float) $order->total_amount,
        ];
    }

    /** "Cotton Tee (Navy Blue · L, customized) × 3". */
    private function line(OrderItem $item): string
    {
        $details = array_filter([
            $item->productVariant?->label,
            $item->custom_design_id ? 'customized' : null,
        ]);

        return ($item->product?->name ?? 'Removed product')
            . ($details ? ' (' . implode(', ', $details) . ')' : '')
            . ' × ' . $item->quantity;
    }

    private function csv(array $report, string $filename): Response
    {
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');

            // Without the byte-order mark Excel reads the file as ANSI, and
            // an ñ or the peso sign in a name comes out as mojibake.
            fwrite($out, "\xEF\xBB\xBF");

            $this->csvRow($out, ['Order No.', 'Date', 'Customer', 'Ordered For', 'Paid Through', 'Items', 'Receipt / PR No.', 'Status', 'Total (PHP)']);

            foreach ($report['rows'] as $row) {
                $this->csvRow($out, [
                    $row['number'],
                    $row['placed']->format('Y-m-d'),
                    $row['customer'],
                    $row['ordered_for'],
                    $row['channel'],
                    implode('; ', $row['items']),
                    $row['reference'],
                    $row['status'],
                    number_format($row['total'], 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Excel runs a cell that starts with = + - or @ as a formula, and the
     * customer name and the office are typed by customers. A leading
     * apostrophe makes Excel show such a cell as the text it is.
     *
     * @param  resource  $out
     */
    private function csvRow($out, array $cells): void
    {
        $cells = array_map(
            fn ($cell) => preg_match('/^[=+\-@\t\r]/', (string) $cell) ? "'" . $cell : (string) $cell,
            $cells
        );

        fputcsv($out, $cells, ',', '"', '');
    }
}
