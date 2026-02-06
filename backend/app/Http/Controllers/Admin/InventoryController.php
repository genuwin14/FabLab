<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;

class InventoryController extends Controller
{
    /**
     * Display low stock products and reorder suggestions.
     */
    public function index()
    {
        // Get products with stock <= low_stock_threshold
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->with([
                'suppliers' => function ($query) {
                    // We want to see if there is a default supplier
                    $query->wherePivot('is_default', true);
                },
                'category'
            ])
            ->get();

        // Group by Default Supplier for easier PO creation
        $groupedSuggestions = $lowStockProducts->groupBy(function ($item) {
            $defaultSupplier = $item->suppliers->first();
            return $defaultSupplier ? $defaultSupplier->supplier_id : 'no_supplier';
        });

        // We also need full supplier details for the keys
        $supplierIds = $groupedSuggestions->keys()->filter(fn($id) => $id !== 'no_supplier');
        $suppliers = Supplier::whereIn('supplier_id', $supplierIds)->get()->keyBy('supplier_id');

        return view('admin.inventory.index', compact('lowStockProducts', 'groupedSuggestions', 'suppliers'));
    }
}
