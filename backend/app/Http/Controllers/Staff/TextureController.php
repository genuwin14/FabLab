<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        return view('staff.textures.index', compact('textures', 'suppliers', 'perPage', 'search'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'supplier_id' => 'nullable|exists:suppliers,supplier_id',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'price_modifier' => 'nullable|numeric|min:0',
        ]);

        $texture = Texture::findOrFail($id);
        $data = $request->except('image_file');

        if ($request->hasFile('image_file')) {
            $data['image_path'] = $texture->storeImage($request->file('image_file'));
        }

        $texture->update($data);

        return redirect()->route('staff.textures.index')->with('success', 'Texture updated successfully.');
    }
}
