<?php

namespace App\Http\Controllers\Staff;

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
        return 'staff';
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

        return view('staff.raw-materials.index', compact(
            'rawMaterials', 'suppliers', 'perPage', 'search',
            'movements', 'materialOptions', 'activeTab'
        ));
    }

    public function update(Request $request, $id)
    {
        $rawMaterial = RawMaterial::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost_per_unit' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'unit' => ['required', Rule::in(\App\Enums\MaterialUnit::allowedFor($rawMaterial->unit))],
            'description' => 'nullable|string',
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        // Stock moves only through Record Usage — see the note on the admin
        // controller. update($request->all()) used to let this form write
        // stock_quantity straight over the top of it.
        $rawMaterial->update($request->only([
            'name', 'supplier_id', 'cost_per_unit',
            'low_stock_threshold', 'unit', 'description',
        ]));

        return redirect()->route('staff.raw-materials.index')->with('success', 'Raw material updated successfully.');
    }
}
