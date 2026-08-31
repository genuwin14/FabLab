<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\Texture;

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
        $suppliers = Supplier::all();
        $rawMaterials = RawMaterial::all();
        return view('admin.product.products', compact(
            'products', 'categories', 'suppliers', 'rawMaterials',
            'perPage', 'search', 'categoryId', 'stockStatus'
        ));
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
            'unit' => ['required', Rule::in(\App\Enums\MaterialUnit::values())],
            'brand' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'units_on_display' => 'nullable|integer|min:0',
            'units_sponsored' => 'nullable|integer|min:0',
            'units_damaged' => 'nullable|integer|min:0',
            'units_consumed' => 'nullable|integer|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|max:2048', // 2MB Max
            // is_customizable handled as checkbox
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        $data = $request->except('image_file');
        $data['is_customizable'] = $request->has('is_customizable');

        // Set default status to 'active' if not provided
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        // Images go to the public disk; the row keeps the path.
        if ($request->hasFile('image_file')) {
            $data['image'] = (new Product)->storeImage($request->file('image_file'));
        }

        $product = Product::create($data);

        // Redirect to supplier assignment page
        return redirect()->route('admin.products.suppliers.assign', $product->product_id)
            ->with('success', 'Product created successfully. Now assign suppliers.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $id . ',product_id',
            'category_id' => 'required|exists:categories,category_id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => ['required', Rule::in(\App\Enums\MaterialUnit::allowedFor($product->unit))],
            'brand' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'units_on_display' => 'nullable|integer|min:0',
            'units_sponsored' => 'nullable|integer|min:0',
            'units_damaged' => 'nullable|integer|min:0',
            'units_consumed' => 'nullable|integer|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|max:2048',
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        $data = $request->except('image_file');
        $data['is_customizable'] = $request->has('is_customizable');

        // Set default status to 'active' if not provided
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        // Replacing an image also clears the file it was pointing at.
        if ($request->hasFile('image_file')) {
            $data['image'] = $product->storeImage($request->file('image_file'));
        }

        $product->update($data);

        // Sync BOM
        if ($request->has('materials')) {
            $materialsData = [];
            foreach ($request->materials as $m) {
                if (isset($m['raw_material_id']) && isset($m['quantity_required'])) {
                    $materialsData[$m['raw_material_id']] = [
                        'quantity_required' => $m['quantity_required'],
                        // An unticked checkbox posts nothing, so absence is the
                        // "part of the blank" answer rather than a missing one.
                        'requires_design' => ! empty($m['requires_design']),
                    ];
                }
            }
            $product->rawMaterials()->sync($materialsData);
        } else {
            $product->rawMaterials()->detach();
        }

        return redirect()->route('admin.products.index')->with('success', 'Product and BOM updated successfully.');
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

    // Phase 3: Texture Assignment Page
    public function assignTextures($id)
    {
        $product = Product::with('textures')->findOrFail($id);
        $textures = Texture::orderBy('name')->get();

        return view('admin.product.assign-textures', compact('product', 'textures'));
    }

    // Phase 3: Save Texture Assignments
    public function storeTextures(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'textures' => 'nullable|array',
            'textures.*' => 'exists:textures,texture_id',
        ]);

        $product->textures()->sync($request->input('textures', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'Textures assigned successfully.');
    }

    public function assignColors($id)
    {
        $product = Product::with('colors')->findOrFail($id);
        $colors = \App\Models\Color::orderBy('name')->get();

        return view('admin.product.assign-colors', compact('product', 'colors'));
    }

    public function storeColors(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'colors' => 'nullable|array',
            'colors.*' => 'exists:colors,color_id',
        ]);

        $product->colors()->sync($request->input('colors', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'Colors assigned successfully.');
    }
}
