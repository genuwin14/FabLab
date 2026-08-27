<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Builds the transaction slip PDF on paper cut to the slip itself.
 *
 * DomPDF cannot size a page to its content, so the page is 80mm thermal-roll
 * wide and its height is computed from what the slip will render. The numbers
 * below were measured against the template by binary-searching the shortest
 * page that still fits on one page; anything that changes the template's
 * spacing needs them re-measured.
 */
class TransactionSlip
{
    /** 80mm roll, in points. */
    private const WIDTH = 226.77;

    /** Everything except the item rows: header, customer, totals, barcode. */
    private const BASE_HEIGHT = 270.5;

    /** Cell padding an item row adds on top of its text lines. */
    private const ROW_PADDING = 4.0;

    /** One line of item text. */
    private const LINE_HEIGHT = 8.76;

    /** Characters the product column fits before wrapping. */
    private const PRODUCT_CHARS = 25;

    /** Slack so font rounding can never spill the last line onto a second page. */
    private const SAFETY_PAD = 2.0;

    /**
     * Statuses a slip exists for. An order only earns one once it is approved,
     * because the slip is what the customer hands the cashier.
     */
    public const PRINTABLE_STATUSES = ["approved", "processing", "ready_for_pickup", "for_delivery", "completed"];

    public static function pdf($order)
    {
        return Pdf::loadView('emails.orders.transaction-slip', ['order' => $order])
            ->setPaper([0, 0, self::WIDTH, self::height($order)]);
    }

    private static function height($order): float
    {
        $rows = 0;
        $lines = 0;

        foreach ($order->orderItems as $item) {
            $rows++;
            // Mirrors the greedy wrap DomPDF applies to the product column.
            $lines += substr_count(
                wordwrap((string) $item->product->name, self::PRODUCT_CHARS, "\n", true),
                "\n"
            ) + 1;
        }

        return ceil(
            self::BASE_HEIGHT
            + $rows * self::ROW_PADDING
            + $lines * self::LINE_HEIGHT
            + self::SAFETY_PAD
        );
    }
}
