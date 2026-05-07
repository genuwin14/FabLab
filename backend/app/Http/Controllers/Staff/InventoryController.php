<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\Texture;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type', '');
        $stockStatus = $request->query('stock_status', '');

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

        $allLowStockItems = $lowStockProducts->concat($lowStockMaterials)->concat($lowStockTextures);

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

        return view('staff.inventory.index', compact(
            'lowStockProducts',
            'lowStockMaterials',
            'lowStockTextures',
            'allLowStockItems',
            'groupedSuggestions',
            'suppliers',
            'search',
            'type',
            'stockStatus'
        ));
    }
}
