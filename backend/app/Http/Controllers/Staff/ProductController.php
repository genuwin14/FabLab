<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status');

        $query = Product::with(['category', 'suppliers', 'rawMaterials']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'in_stock') {
            $query->whereColumn('stock', '>', 'low_stock_threshold');
        } elseif ($stockStatus === 'low_stock') {
            $query->where('stock', '>', 0)
                  ->whereColumn('stock', '<=', 'low_stock_threshold');
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->latest()->paginate($perPage)->withQueryString();
        $categories = Category::all();

        return view('staff.product.products', compact(
            'products', 'categories',
            'perPage', 'search', 'categoryId', 'stockStatus'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $id . ',product_id',
            'category_id' => 'required|exists:categories,category_id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'brand' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $product = Product::findOrFail($id);

        $data = $request->except('image_file');
        $data['is_customizable'] = $request->has('is_customizable');

        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $data['image'] = 'data:' . $image->getClientMimeType() . ';base64,' . $base64Image;
        }

        $product->update($data);

        return redirect()->route('staff.products.index')->with('success', 'Product updated successfully.');
    }
}
