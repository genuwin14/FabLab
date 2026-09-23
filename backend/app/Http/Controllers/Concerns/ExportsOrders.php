<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Reports\OrdersExport;
use Illuminate\Http\Request;

/**
 * The Export button on the admin and staff order lists. The two lists show
 * the same orders under the same filters, so they share one export.
 */
trait ExportsOrders
{
    public function export(Request $request, string $format, OrdersExport $export)
    {
        return $export->download($request, $format);
    }
}
