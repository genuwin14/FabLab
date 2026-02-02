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

        $products = $query->latest()->paginate(12);
        $categories = \App\Models\Category::all();

        return view('customer.shop.shop', compact('products', 'categories'));
    }
}
