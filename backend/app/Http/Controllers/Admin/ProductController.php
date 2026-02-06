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
        $products = Product::with(['category', 'suppliers'])->latest()->get();
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('admin.product.products', compact('products', 'categories', 'suppliers'));
    }

    // Phase 1: Create supplier-agnostic product
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

        $product = Product::create($data);

        // Redirect to supplier assignment page
        return redirect()->route('admin.products.suppliers.assign', $product->product_id)
            ->with('success', 'Product created successfully. Now assign suppliers.');
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

    // Phase 2: Supplier Assignment Page
    public function assignSuppliers($id)
    {
        $product = Product::with('suppliers')->findOrFail($id);
        $suppliers = Supplier::all();

        return view('admin.product.assign-suppliers', compact('product', 'suppliers'));
    }

    // Phase 2: Save Supplier Assignments
    public function storeSuppliers(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'exists:suppliers,supplier_id',
            'cost.*' => 'nullable|numeric|min:0',
            'min_order_qty.*' => 'nullable|integer|min:0',
            'lead_time_days.*' => 'nullable|integer|min:0',
            'is_default' => 'nullable|exists:suppliers,supplier_id',
        ]);

        // Detach all existing suppliers
        $product->suppliers()->detach();

        // Attach new suppliers with pivot data
        if ($request->has('suppliers') && is_array($request->suppliers)) {
            foreach ($request->suppliers as $supplierId) {
                $product->suppliers()->attach($supplierId, [
                    'cost' => $request->input("cost.{$supplierId}", 0),
                    'min_order_qty' => $request->input("min_order_qty.{$supplierId}", 0),
                    'lead_time_days' => $request->input("lead_time_days.{$supplierId}", 0),
                    'is_default' => $request->is_default == $supplierId,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Suppliers assigned successfully.');
    }
}
