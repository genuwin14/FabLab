<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = Supplier::latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate($perPage)->withQueryString();

        return view('admin.suppliers.suppliers', compact('suppliers', 'search', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $id . ',supplier_id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Both purchase_orders.supplier_id and raw_materials.supplier_id cascade
        // on delete, so removing a supplier in use would erase its procurement
        // history and every material bought from it.
        $blockers = [];

        $poCount = \App\Models\PurchaseOrder::where('supplier_id', $id)->count();
        if ($poCount > 0) {
            $blockers[] = "{$poCount} purchase order(s)";
        }

        $materialCount = \App\Models\RawMaterial::where('supplier_id', $id)->count();
        if ($materialCount > 0) {
            $blockers[] = "{$materialCount} raw material(s)";
        }

        if ($blockers !== []) {
            return back()->with('error', "\"{$supplier->name}\" is still referenced by " . implode(' and ', $blockers) . '. Reassign those first — deleting the supplier would delete them too.');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
