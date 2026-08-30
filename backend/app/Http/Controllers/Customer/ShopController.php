<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with('category')
            ->whereIn('status', ['active', 'functional'])
            ->where('price', '>', 0);

        // Filter by Category
        if ($request->has('category') && $request->category != 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Search by Keyword
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 24 rather than 12: the catalogue zooms out to an 8-wide grid on
        // scroll, and a page of 12 is only a row and a half at that size.
        $products = $query->latest()->paginate(24);
        $categories = \App\Models\Category::all();

        return view('customer.shop.shop', compact('products', 'categories'));
    }
}
