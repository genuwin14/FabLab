<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'supplier'])->latest()->get();
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('admin.product.products', compact('products', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'category_id' => 'required|exists:categories,category_id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'brand' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,supplier_id',
            'cost' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|max:2048', // 2MB Max
            // is_customizable handled as checkbox
        ]);

        $data = $request->except('image_file');
        $data['is_customizable'] = $request->has('is_customizable');

        // Set default status to 'active' if not provided
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        // Handle Image Upload -> Base64
        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $data['image'] = 'data:' . $image->getClientMimeType() . ';base64,' . $base64Image;
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
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
            'supplier_id' => 'nullable|exists:suppliers,supplier_id',
            'cost' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $product = Product::findOrFail($id);

        $data = $request->except('image_file');
        $data['is_customizable'] = $request->has('is_customizable');

        // Set default status to 'active' if not provided
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $data['image'] = 'data:' . $image->getClientMimeType() . ';base64,' . $base64Image;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
