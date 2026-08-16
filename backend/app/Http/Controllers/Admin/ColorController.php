<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

/**
 * Admin CRUD for the plain finishes a customer can pick in the 3D customizer,
 * the alternative to an image texture.
 */
class ColorController extends Controller
{
    /** '#RRGGBB' only — the customizer feeds this straight to CSS and THREE.Color. */
    private const HEX_RULE = ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];

    private const MESSAGES = [
        'hex_code.regex' => 'Enter a colour as a 6-digit hex code, e.g. #1B2A4A.',
    ];

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

        return view('admin.colors.index', compact('colors', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => self::HEX_RULE,
            'description' => 'nullable|string|max:255',
            'price_modifier' => 'nullable|numeric|min:0|max:999999.99',
        ], self::MESSAGES);

        $data['hex_code'] = strtolower($data['hex_code']);
        $data['price_modifier'] = $data['price_modifier'] ?? 0;

        Color::create($data);

        return redirect()->route('admin.colors.index')->with('success', 'Color added successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => self::HEX_RULE,
            'description' => 'nullable|string|max:255',
            'price_modifier' => 'nullable|numeric|min:0|max:999999.99',
        ], self::MESSAGES);

        $data['hex_code'] = strtolower($data['hex_code']);
        $data['price_modifier'] = $data['price_modifier'] ?? 0;

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
