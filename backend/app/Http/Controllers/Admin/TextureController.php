<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Texture;

class TextureController extends Controller
{
    public function index()
    {
        $textures = Texture::latest()->paginate(12);
        return view('admin.textures.index', compact('textures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048', // 2MB Max
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
