<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

/**
 * Admin CRUD for the plain finishes a customer can pick in the 3D customizer,
 * the alternative to an image texture.
 */
class ColorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);
        if (!in_array($perPage, [12, 24, 48, 96])) {
            $perPage = 12;
        }

        $search = trim((string) $request->query('search', ''));

        $query = Color::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hex_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $colors = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // What a colour can be linked to consume. Retired materials are left
        // out: they can't be deducted, so offering one would build a link that
        // silently does nothing.
        $materials = RawMaterial::orderBy('name')->get(['raw_material_id', 'name', 'unit']);

        return view('admin.colors.index', compact('colors', 'perPage', 'search', 'materials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(Color::VALIDATION_RULES, Color::VALIDATION_MESSAGES);

        $data['hex_code'] = strtolower($data['hex_code']);
        $data['price_modifier'] = $data['price_modifier'] ?? 0;
        $data = Color::normaliseMaterial($data);

        Color::create($data);

        return redirect()->route('admin.colors.index')->with('success', 'Color added successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $data = $request->validate(Color::VALIDATION_RULES, Color::VALIDATION_MESSAGES);

        $data['hex_code'] = strtolower($data['hex_code']);
        $data['price_modifier'] = $data['price_modifier'] ?? 0;
        $data = Color::normaliseMaterial($data);

        $color->update($data);

        return redirect()->route('admin.colors.index')->with('success', 'Color updated successfully.');
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);

        // Soft delete: designs already saved in this colour still resolve it,
        // it just stops being offered on new ones.
        $color->delete();

        return redirect()->route('admin.colors.index')
            ->with('success', "\"{$color->name}\" retired. Designs already saved in it keep their finish.");
    }
}
