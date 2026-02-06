<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class CustomizeController extends Controller
{
    public function index(Request $request)
    {
        $productId = $request->query('product_id');
        $product = null;

        if ($productId) {
            $product = Product::find($productId);
        }

        return view('customer.prod-customize.customize-product', compact('product'));
    }
}
