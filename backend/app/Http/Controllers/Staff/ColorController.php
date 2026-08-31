<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

/**
 * Staff view of the plain finishes the 3D customizer offers.
 *
 * Read and edit only, mirroring Staff\TextureController: staff can correct a
 * name, description or surcharge, but adding a colour or retiring one changes
 * what every product is able to offer, so those stay with an admin. There is
 * deliberately no store() or destroy() here and no staff route that reaches
 * them.
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

        $materials = RawMaterial::orderBy('name')->get(['raw_material_id', 'name', 'unit']);

        return view('staff.colors.index', compact('colors', 'perPage', 'search', 'materials'));
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $data = $request->validate(Color::VALIDATION_RULES, Color::VALIDATION_MESSAGES);

        $data['hex_code'] = strtolower($data['hex_code']);
        $data['price_modifier'] = $data['price_modifier'] ?? 0;
        $data = Color::normaliseMaterial($data);

        $color->update($data);

        return redirect()->route('staff.colors.index')->with('success', 'Color updated successfully.');
    }
}
