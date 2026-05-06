<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\Texture;

class InventoryController extends Controller
{
    /**
     * Display low stock products/materials/textures and reorder suggestions.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type', '');
        $stockStatus = $request->query('stock_status', '');

        // 1. Get low stock Products
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->with([
                'suppliers' => function ($query) {
                    $query->wherePivot('is_default', true);
                },
                'category'
            ])
            ->get()
            ->map(function($item) {
                $item->type = 'Product';
                $item->display_stock = $item->stock;
                $item->display_threshold = $item->low_stock_threshold;
                return $item;
            });

        // 2. Get low stock Raw Materials
        $lowStockMaterials = RawMaterial::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->with('supplier')
            ->get()
            ->map(function($item) {
                $item->type = 'Raw Material';
                $item->display_stock = $item->stock_quantity;
                $item->display_threshold = $item->low_stock_threshold;
                $item->setRelation('suppliers', collect([$item->supplier]));
                return $item;
            });

        // 3. Get low stock Textures
        $lowStockTextures = Texture::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->with('supplier')
            ->get()
            ->map(function($item) {
                $item->type = 'Texture';
                $item->display_stock = $item->stock_quantity;
                $item->display_threshold = $item->low_stock_threshold;
                $item->setRelation('suppliers', collect([$item->supplier]));
                return $item;
            });

        // Combine them
        $allLowStockItems = $lowStockProducts->concat($lowStockMaterials)->concat($lowStockTextures);

        // Apply filters
        if ($type !== '') {
            $allLowStockItems = $allLowStockItems->where('type', $type)->values();
        }

        if ($stockStatus === 'out_of_stock') {
            $allLowStockItems = $allLowStockItems->filter(fn($item) => (float) $item->display_stock == 0)->values();
        } elseif ($stockStatus === 'low_stock') {
            $allLowStockItems = $allLowStockItems->filter(fn($item) => (float) $item->display_stock > 0)->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $allLowStockItems = $allLowStockItems->filter(function ($item) use ($needle) {
                $name = mb_strtolower((string) ($item->name ?? ''));
                $sku = mb_strtolower((string) ($item->sku ?? ''));
                return str_contains($name, $needle) || str_contains($sku, $needle);
            })->values();
        }

        // Group by Default Supplier for easier PO creation
        $groupedSuggestions = $allLowStockItems->groupBy(function ($item) {
            if ($item->type === 'Product') {
                $supplier = $item->suppliers->first();
            } else {
                $supplier = $item->supplier;
            }
            return $supplier ? $supplier->supplier_id : 'no_supplier';
        });

        $supplierIds = $groupedSuggestions->keys()->filter(fn($id) => $id !== 'no_supplier');
        $suppliers = Supplier::whereIn('supplier_id', $supplierIds)->get()->keyBy('supplier_id');
        $allSuppliers = Supplier::orderBy('name')->get();

        return view('admin.inventory.index', compact(
            'lowStockProducts',
            'lowStockMaterials',
            'lowStockTextures',
            'allLowStockItems',
            'groupedSuggestions',
            'suppliers',
            'allSuppliers',
            'search',
            'type',
            'stockStatus'
        ));
    }

    /**
     * Assign a default supplier to a low-stock item directly from the
     * inventory monitoring page.
     */
    public function assignSupplier(Request $request)
    {
        $data = $request->validate([
            'item_type' => 'required|in:Product,Raw Material,Texture',
            'item_id' => 'required|integer',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'cost' => 'required_if:item_type,Product|nullable|numeric|min:0',
        ]);

        if ($data['item_type'] === 'Product') {
            $product = Product::findOrFail($data['item_id']);

            // Clear any existing default flag for this product, then attach
            // (or update) the chosen supplier as default. The pivot's `cost`
            // column is NOT NULL, so it must be supplied when creating a
            // new link — the form validates this is present for Products.
            $product->suppliers()->newPivotStatement()
                ->where('product_id', $product->product_id)
                ->update(['is_default' => false]);

            $existing = $product->suppliers()
                ->wherePivot('supplier_id', $data['supplier_id'])
                ->first();

            if ($existing) {
                $product->suppliers()->updateExistingPivot($data['supplier_id'], [
                    'is_default' => true,
                    'cost' => $data['cost'],
                ]);
            } else {
                $product->suppliers()->attach($data['supplier_id'], [
                    'cost' => $data['cost'],
                    'is_default' => true,
                ]);
            }
        } elseif ($data['item_type'] === 'Raw Material') {
            RawMaterial::where('raw_material_id', $data['item_id'])
                ->update(['supplier_id' => $data['supplier_id']]);
        } else { // Texture
            Texture::where('texture_id', $data['item_id'])
                ->update(['supplier_id' => $data['supplier_id']]);
        }

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Default supplier assigned successfully.');
    }
}
