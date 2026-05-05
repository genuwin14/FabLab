<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\RawMaterial;

class InventoryController extends Controller
{
    public function index()
    {
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

        $allLowStockItems = $lowStockProducts->concat($lowStockMaterials);

        $groupedSuggestions = $allLowStockItems->groupBy(function ($item) {
            $supplier = $item->type === 'Product' ? $item->suppliers->first() : $item->supplier;
            return $supplier ? $supplier->supplier_id : 'no_supplier';
        });

        $supplierIds = $groupedSuggestions->keys()->filter(fn($id) => $id !== 'no_supplier');
        $suppliers = Supplier::whereIn('supplier_id', $supplierIds)->get()->keyBy('supplier_id');

        return view('staff.inventory.index', compact('lowStockProducts', 'lowStockMaterials', 'allLowStockItems', 'groupedSuggestions', 'suppliers'));
    }
}
