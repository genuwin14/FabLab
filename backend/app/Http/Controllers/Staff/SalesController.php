<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\SalesReport;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request, SalesReport $report)
    {
        return view('staff.sales.sales', $report->build($request));
    }
}
