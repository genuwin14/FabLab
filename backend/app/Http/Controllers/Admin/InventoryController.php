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
    public function index()
    {
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

        return view('admin.inventory.index', compact(
            'lowStockProducts',
            'lowStockMaterials',
            'lowStockTextures',
            'allLowStockItems',
            'groupedSuggestions',
            'suppliers'
        ));
    }
}
