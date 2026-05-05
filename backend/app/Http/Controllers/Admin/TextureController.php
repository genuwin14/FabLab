<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Texture;
use App\Models\Supplier;

class TextureController extends Controller
{
    public function index()
    {
        $textures = Texture::with('supplier')->latest()->paginate(12);
        $suppliers = Supplier::all();
        return view('admin.textures.index', compact('textures', 'suppliers'));
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
            'unit' => 'nullable|string|max:50',
            'price_modifier' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except('image_file');

        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $data['image_path'] = 'data:' . $image->getClientMimeType() . ';base64,' . $base64Image;
        }

        Texture::create($data);

        return redirect()->route('admin.textures.index')->with('success', 'Texture added successfully.');
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
            $image = $request->file('image_file');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $data['image_path'] = 'data:' . $image->getClientMimeType() . ';base64,' . $base64Image;
        }

        $texture->update($data);

        return redirect()->route('admin.textures.index')->with('success', 'Texture updated successfully.');
    }

    public function destroy($id)
    {
        $texture = Texture::findOrFail($id);
        $texture->delete();

        return redirect()->route('admin.textures.index')->with('success', 'Texture deleted successfully.');
    }
}
