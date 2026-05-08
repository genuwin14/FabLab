<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $query = Equipment::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('property_no', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $equipment = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.equipment.index', compact('equipment', 'perPage', 'search', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'property_no' => 'nullable|string|max:255',
            'date_acquired' => 'nullable|date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Equipment::create($data);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment added successfully.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'property_no' => 'nullable|string|max:255',
            'date_acquired' => 'nullable|date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $equipment = Equipment::findOrFail($id);
        $equipment->update($data);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment updated successfully.');
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment deleted successfully.');
    }
}
