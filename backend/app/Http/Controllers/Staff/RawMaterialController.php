<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Supplier;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = RawMaterial::with('supplier')->latest()->paginate(10);
        $suppliers = Supplier::all();
        return view('staff.raw-materials.index', compact('rawMaterials', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $rawMaterial = RawMaterial::findOrFail($id);
        $rawMaterial->update($request->all());

        return redirect()->route('staff.raw-materials.index')->with('success', 'Raw material updated successfully.');
    }
}
