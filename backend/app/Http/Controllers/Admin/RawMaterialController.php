<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RecordsMaterialUsage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\RawMaterial;
use App\Models\Supplier;

class RawMaterialController extends Controller
{
    use RecordsMaterialUsage;

    protected function usageRoutePrefix(): string
    {
        return 'admin';
    }

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

        // Usage Log tab. Rendered alongside the table rather than fetched on
        // demand, so a deep link like ?tab=log lands on a populated pane.
        $movements = $this->usageLog($request);
        $materialOptions = RawMaterial::orderBy('name')->get(['raw_material_id', 'name']);
        $activeTab = $request->query('tab') === 'log' ? 'log' : 'materials';

        return view('admin.raw-materials.index', compact(
            'rawMaterials', 'suppliers', 'perPage', 'search',
            'movements', 'materialOptions', 'activeTab'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'unit' => ['required', Rule::in(\App\Enums\MaterialUnit::values())],
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        // Listed explicitly rather than except('image_file'): the four units_*
        // counters are the ledger's to write, and mass assignment would let a
        // crafted request seed them behind its back. A new material starts at
        // its opening stock with every counter at zero.
        $data = $request->only([
            'name', 'supplier_id', 'cost_per_unit', 'stock_quantity',
            'low_stock_threshold', 'department', 'unit', 'description',
        ]);

        // Images go to the public disk; the row keeps the path.
        if ($request->hasFile('image_file')) {
            $data['image_path'] = (new RawMaterial)->storeImage($request->file('image_file'));
        }

        RawMaterial::create($data);

        return redirect()->route('admin.raw-materials.index')->with('success', 'Raw material added successfully.');
    }

    public function update(Request $request, $id)
    {
        $rawMaterial = RawMaterial::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
            'unit' => ['required', Rule::in(\App\Enums\MaterialUnit::allowedFor($rawMaterial->unit))],
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        // Stock and the four report counters are deliberately absent: they move
        // only through Record Usage, which writes a ledger row for each change.
        // Typing a new figure here was the old way and left the counters and the
        // shelf disagreeing with nothing to show why.
        $data = $request->only([
            'name', 'supplier_id', 'cost_per_unit',
            'low_stock_threshold', 'department', 'unit', 'description',
        ]);

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
