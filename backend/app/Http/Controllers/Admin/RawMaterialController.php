<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Supplier;

class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $search = trim((string) $request->query('search', ''));

        $query = RawMaterial::with('supplier');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($s) use ($search) {
                      $s->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $rawMaterials = $query->latest()->paginate($perPage)->withQueryString();
        $suppliers = Supplier::all();
        return view('admin.raw-materials.index', compact('rawMaterials', 'suppliers', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'units_on_display' => 'nullable|numeric|min:0',
            'units_sponsored' => 'nullable|numeric|min:0',
            'units_damaged' => 'nullable|numeric|min:0',
            'units_consumed' => 'nullable|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image_file');

        // Images go to the public disk; the row keeps the path.
        if ($request->hasFile('image_file')) {
            $data['image_path'] = (new RawMaterial)->storeImage($request->file('image_file'));
        }

        RawMaterial::create($data);

        return redirect()->route('admin.raw-materials.index')->with('success', 'Raw material added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'units_on_display' => 'nullable|numeric|min:0',
            'units_sponsored' => 'nullable|numeric|min:0',
            'units_damaged' => 'nullable|numeric|min:0',
            'units_consumed' => 'nullable|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $rawMaterial = RawMaterial::findOrFail($id);
        $data = $request->except('image_file');

        if ($request->hasFile('image_file')) {
            $data['image_path'] = $rawMaterial->storeImage($request->file('image_file'));
        }

        $rawMaterial->update($data);

        return redirect()->route('admin.raw-materials.index')->with('success', 'Raw material updated successfully.');
    }

    public function destroy($id)
    {
        $rawMaterial = RawMaterial::findOrFail($id);

        // purchase_order_items.raw_material_id cascades, so deleting a material
        // that has been ordered would rewrite the purchase history.
        $lineCount = \App\Models\PurchaseOrderItem::where('raw_material_id', $id)->count();

        if ($lineCount > 0) {
            return back()->with('error', "\"{$rawMaterial->name}\" appears on {$lineCount} purchase order line(s). Deleting it would remove them from that history.");
        }

        $rawMaterial->delete();

        return redirect()->route('admin.raw-materials.index')->with('success', 'Raw material deleted successfully.');
    }
}
