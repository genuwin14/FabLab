<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class CustomizeController extends Controller
{
    public function index(Request $request)
    {
        $productId = $request->query('product_id');
        $designId = $request->query('design_id');
        $product = null;
        $design = null;
        $initialShape = 't-shirt'; // Default

        if ($designId) {
            $design = \App\Models\CustomDesign::find($designId);
            if ($design) {
                $productId = $design->product_id;
                $initialShape = $design->recipe['base_style'] ?? 't-shirt';
            }
        }

        if ($productId) {
            $product = Product::find($productId);
            if ($product && !$design) { // If not loading a specific design, auto-detect shape
                if (str_contains(strtolower($product->name), 'mug')) {
                    $initialShape = 'mug';
                } elseif (str_contains(strtolower($product->name), 't-shirt')) {
                    $initialShape = 't-shirt';
                }
            }
        }

        return view('customer.prod-customize.customize-product', compact('product', 'initialShape', 'design'));
    }

    public function save(Request $request)
    {
        $productId = $request->input('product_id');
        $designId = $request->input('design_id');
        $recipe = $request->input('custom_recipe');
        $snapshot = $request->input('custom_snapshot');

        if (!$recipe) {
            return response()->json(['success' => false, 'message' => 'Design data is missing.'], 400);
        }

        $recipeData = json_decode($recipe, true);

        // Update existing if designId is provided, otherwise create new
        $design = \App\Models\CustomDesign::updateOrCreate(
            [
                'custom_design_id' => $designId,
                'user_id' => auth()->id()
            ],
            [
                'product_id' => $productId,
                'recipe' => $recipeData,
                'snapshot' => $snapshot
            ]
        );

        $isUpdate = (bool) $designId && $design->wasRecentlyCreated === false;

        return response()->json([
            'success' => true,
            'message' => $isUpdate ? 'Design updated successfully!' : 'Design saved to your personal collection!',
            'design_id' => $design->custom_design_id
        ]);
    }

    public function myDesigns()
    {
        $designs = auth()->user()->customDesigns()->with('product')->latest()->get();
        return view('customer.prod-customize.my-designs', compact('designs'));
    }

    public function destroy($id)
    {
        $design = \App\Models\CustomDesign::where('custom_design_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$design) {
            return response()->json(['success' => false, 'message' => 'Design not found or unauthorized.'], 404);
        }

        $design->delete();

        return response()->json(['success' => true, 'message' => 'Design deleted successfully!']);
    }
}
