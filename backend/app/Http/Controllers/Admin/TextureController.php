<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Texture;
use App\Models\Supplier;

class TextureController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);
        if (!in_array($perPage, [12, 24, 48, 96])) {
            $perPage = 12;
        }

        $search = trim((string) $request->query('search', ''));

        $query = Texture::with('supplier');

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

        $textures = $query->latest()->paginate($perPage)->withQueryString();
        $suppliers = Supplier::all();
        return view('admin.textures.index', compact('textures', 'suppliers', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'supplier_id' => 'nullable|exists:suppliers,supplier_id',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit' => ['nullable', Rule::in(\App\Enums\MaterialUnit::values())],
            'price_modifier' => 'nullable|numeric|min:0',
            'units_on_display' => 'nullable|numeric|min:0',
            'units_sponsored' => 'nullable|numeric|min:0',
            'units_damaged' => 'nullable|numeric|min:0',
            'units_consumed' => 'nullable|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        $data = $this->withoutEmptyUnit($request->except('image_file'));

        // Images go to the public disk; the row keeps the path.
        if ($request->hasFile('image_file')) {
            $data['image_path'] = (new Texture)->storeImage($request->file('image_file'));
        }

        Texture::create($data);

        return redirect()->route('admin.textures.index')->with('success', 'Texture added successfully.');
    }

    public function update(Request $request, $id)
    {
        $texture = Texture::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'supplier_id' => 'nullable|exists:suppliers,supplier_id',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit' => ['nullable', Rule::in(\App\Enums\MaterialUnit::allowedFor($texture->unit))],
            'price_modifier' => 'nullable|numeric|min:0',
            'units_on_display' => 'nullable|numeric|min:0',
            'units_sponsored' => 'nullable|numeric|min:0',
            'units_damaged' => 'nullable|numeric|min:0',
            'units_consumed' => 'nullable|numeric|min:0',
            'department' => 'nullable|in:' . implode(',', \App\Enums\Department::values()),
        ], [
            'unit.in' => 'Pick a unit from the list.',
        ]);

        $data = $this->withoutEmptyUnit($request->except('image_file'));

        if ($request->hasFile('image_file')) {
            $data['image_path'] = $texture->storeImage($request->file('image_file'));
        }

        $texture->update($data);

        return redirect()->route('admin.textures.index')->with('success', 'Texture updated successfully.');
    }

    public function destroy($id)
    {
        $texture = Texture::findOrFail($id);

        // purchase_order_items.texture_id cascades on delete.
        $lineCount = \App\Models\PurchaseOrderItem::where('texture_id', $id)->count();

        if ($lineCount > 0) {
            return back()->with('error', "\"{$texture->name}\" appears on {$lineCount} purchase order line(s). Deleting it would remove them from that history.");
        }

        $texture->delete();

        return redirect()->route('admin.textures.index')->with('success', 'Texture deleted successfully.');
    }

    /**
     * `textures.unit` is NOT NULL with a 'pcs' default, and Laravel turns an
     * empty form field into null — which would fail the insert rather than
     * fall back to the default. Dropping the key lets the column decide.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutEmptyUnit(array $data): array
    {
        if (array_key_exists('unit', $data) && ($data['unit'] === null || $data['unit'] === '')) {
            unset($data['unit']);
        }

        return $data;
    }
}
