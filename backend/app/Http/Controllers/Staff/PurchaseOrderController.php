<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $date = $request->input('date', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = PurchaseOrder::with(['supplier', 'creator'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($date !== '') {
            switch ($date) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
            }
        }

        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        $statusCounts = PurchaseOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $suppliers = Supplier::all();
        $products = Product::all();
        $rawMaterials = RawMaterial::all();
        $textures = Texture::all();

        $selectedSupplierId = $request->query('supplier_id');
        $prefillItems = [];

        if ($selectedSupplierId) {
            $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
                ->whereHas('suppliers', function ($q) use ($selectedSupplierId) {
                    $q->where('suppliers.supplier_id', $selectedSupplierId)
                        ->where('product_suppliers.is_default', true);
                })
                ->with([
                    'suppliers' => function ($q) use ($selectedSupplierId) {
                        $q->where('suppliers.supplier_id', $selectedSupplierId);
                    }
                ])
                ->get();

            foreach ($lowStockProducts as $product) {
                $supplierPivot = $product->suppliers->first()->pivot;
                $needed = max(0, $product->low_stock_threshold - $product->stock);
                if ($needed == 0) continue;

                $qty = $needed;
                if (isset($supplierPivot->min_order_qty) && $qty < $supplierPivot->min_order_qty) {
                    $qty = $supplierPivot->min_order_qty;
                }

                $prefillItems[] = [
                    'type' => 'product',
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'cost' => $supplierPivot->cost,
                    'unit' => $product->unit
                ];
            }

            $lowStockMaterials = RawMaterial::where('supplier_id', $selectedSupplierId)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->get();

            foreach ($lowStockMaterials as $material) {
                $needed = max(0, $material->low_stock_threshold - $material->stock_quantity);
                if ($needed == 0) continue;

                $prefillItems[] = [
                    'type' => 'material',
                    'id' => $material->raw_material_id,
                    'name' => $material->name,
                    'sku' => 'MAT-' . $material->raw_material_id,
                    'quantity' => $needed,
                    'cost' => $material->cost_per_unit,
                    'unit' => $material->unit
                ];
            }

            $lowStockTextures = Texture::where('supplier_id', $selectedSupplierId)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->get();

            foreach ($lowStockTextures as $texture) {
                $needed = max(0, $texture->low_stock_threshold - $texture->stock_quantity);
                if ($needed == 0) continue;

                $prefillItems[] = [
                    'type' => 'texture',
                    'id' => $texture->texture_id,
                    'name' => $texture->name,
                    'sku' => 'TEX-' . $texture->texture_id,
                    'quantity' => $needed,
                    'cost' => $texture->cost_per_unit,
                    'unit' => $texture->unit
                ];
            }
        }

        $supplierItems = [];
        foreach ($suppliers as $s) {
            $items = [];
            $supplierProducts = Product::whereHas('suppliers', function($q) use ($s) {
                $q->where('product_suppliers.supplier_id', $s->supplier_id);
            })->with(['suppliers' => function($q) use ($s) {
                $q->where('suppliers.supplier_id', $s->supplier_id);
            }])->get();

            foreach($supplierProducts as $p) {
                $cost = $p->price;
                $supplierInfo = $p->suppliers->first();
                if ($supplierInfo && $supplierInfo->pivot) {
                    $cost = $supplierInfo->pivot->cost ?? $p->price;
                }
                $items[] = [
                    'type' => 'product',
                    'id' => $p->product_id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'cost' => $cost
                ];
            }

            $supplierMaterials = RawMaterial::where('supplier_id', $s->supplier_id)->get();
            foreach($supplierMaterials as $m) {
                $items[] = [
                    'type' => 'material',
                    'id' => $m->raw_material_id,
                    'name' => $m->name,
                    'sku' => 'MAT-' . $m->raw_material_id,
                    'cost' => $m->cost_per_unit
                ];
            }

            $supplierTextures = Texture::where('supplier_id', $s->supplier_id)->get();
            foreach($supplierTextures as $t) {
                $items[] = [
                    'type' => 'texture',
                    'id' => $t->texture_id,
                    'name' => $t->name,
                    'sku' => 'TEX-' . $t->texture_id,
                    'cost' => $t->cost_per_unit
                ];
            }

            $supplierItems[$s->supplier_id] = $items;
        }

        return view('staff.purchase.index', compact(
            'purchaseOrders',
            'suppliers',
            'products',
            'rawMaterials',
            'textures',
            'selectedSupplierId',
            'prefillItems',
            'supplierItems',
            'search',
            'status',
            'date',
            'perPage',
            'statusCounts'
        ));
    }

    public function create(Request $request)
    {
        return redirect()->route('staff.purchase.index', $request->query());
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,product_id',
            'items.*.raw_material_id' => 'nullable|exists:raw_materials,raw_material_id',
            'items.*.texture_id' => 'nullable|exists:textures,texture_id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        $totalCost = 0;
        foreach ($request->items as $item) {
            $totalCost += $item['quantity'] * $item['cost'];
        }

        $dateCode = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $poNumber = "PO-{$dateCode}-{$random}";

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $request->supplier_id,
            'status' => 'draft',
            'expected_delivery_date' => $request->expected_delivery_date,
            'total_cost' => $totalCost,
            'created_by' => Auth::id(),
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->purchase_order_id,
                'product_id' => $item['product_id'] ?? null,
                'raw_material_id' => $item['raw_material_id'] ?? null,
                'texture_id' => $item['texture_id'] ?? null,
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }

        return redirect()->route('staff.purchase.index')->with('success', 'Purchase Order created successfully (Draft).');
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product', 'items.rawMaterial', 'items.texture', 'creator'])->findOrFail($id);
        return view('staff.purchase.show', compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,confirmed,delivered,cancelled'
        ]);

        $po = PurchaseOrder::with('items')->findOrFail($id);
        $oldStatus = $po->status;
        $newStatus = $request->status;

        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            foreach ($po->items as $item) {
                if ($item->product_id) {
                    $item->product->increment('stock', $item->quantity);
                } elseif ($item->raw_material_id) {
                    $item->rawMaterial->increment('stock_quantity', $item->quantity);
                } elseif ($item->texture_id) {
                    $item->texture->increment('stock_quantity', $item->quantity);
                }
            }
        }

        if ($oldStatus === 'delivered' && $newStatus !== 'delivered') {
            foreach ($po->items as $item) {
                if ($item->product_id) {
                    $item->product->decrement('stock', $item->quantity);
                } elseif ($item->raw_material_id) {
                    $item->rawMaterial->decrement('stock_quantity', $item->quantity);
                } elseif ($item->texture_id) {
                    $item->texture->decrement('stock_quantity', $item->quantity);
                }
            }
        }

        $po->update(['status' => $newStatus]);

        return back()->with('success', "Order status updated to " . ucfirst($newStatus));
    }
}
