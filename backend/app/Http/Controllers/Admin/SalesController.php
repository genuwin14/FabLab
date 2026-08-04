<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\SalesDocxGenerator;
use App\Services\SalesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request, SalesReport $report)
    {
        return view('admin.sales.sales', $report->build($request));
    }

    /** Streams the sales report so it can be checked before downloading. */
    public function preview(Request $request, SalesReport $report)
    {
        return $this->pdf($request, $report)->stream('sales-report-preview.pdf');
    }

    public function exportPdf(Request $request, SalesReport $report)
    {
        return $this->pdf($request, $report)
            ->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportDocx(Request $request, SalesReport $report)
    {
        $data = $report->build($request);

        $generator = new SalesDocxGenerator($data, $report->rangeLabel($data), now());
        $tempPath = $generator->save();

        return response()
            ->download($tempPath, 'sales-report-' . now()->format('Y-m-d') . '.docx')
            ->deleteFileAfterSend(true);
    }

    private function pdf(Request $request, SalesReport $report)
    {
        $data = $report->build($request);

        return Pdf::loadView('admin.reports.pdf.sales', [
            'report' => $data,
            'rangeLabel' => $report->rangeLabel($data),
            'asOfDate' => now(),
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true);
    }
}
