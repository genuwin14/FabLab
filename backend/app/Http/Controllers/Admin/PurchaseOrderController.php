<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'creator'])
            ->latest()
            ->paginate(15);
        return view('admin.purchase.index', compact('purchaseOrders'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::all();
        $products = Product::all();

        // Pre-fill data if coming from Reorder Suggestions
        $selectedSupplierId = $request->query('supplier_id');
        $prefillItems = [];

        if ($selectedSupplierId) {
            // Find products with this default supplier that are low stock
            // This logic allows "Quick Create" from the dashboard
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

                // Logic: Quantity = Max(MOQ, Threshold - Stock + Buffer?)
                // User requirement: (low_stock_threshold - current_stock)
                // We will ensure at least 1, or MOQ if higher.
                $needed = max(0, $product->low_stock_threshold - $product->stock);
                if ($needed == 0)
                    continue; // Should not happen if query is correct, but safety.

                $qty = $needed;
                if (isset($supplierPivot->min_order_qty) && $qty < $supplierPivot->min_order_qty) {
                    $qty = $supplierPivot->min_order_qty;
                }

                $prefillItems[] = [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'cost' => $supplierPivot->cost,
                    'unit' => $product->unit
                ];
            }
        }

        return view('admin.purchase.create', compact('suppliers', 'products', 'selectedSupplierId', 'prefillItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'expected_delivery_date' => 'nullable|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost' => 'required|numeric|min:0',
        ]);

        $totalCost = 0;
        foreach ($request->products as $item) {
            $totalCost += $item['quantity'] * $item['cost'];
        }

        // Generate PO Number (e.g., PO-YYYYMMDD-XXXX)
        $dateCode = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $poNumber = "PO-{$dateCode}-{$random}";

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $request->supplier_id,
            'status' => 'draft', // Default status
            'expected_delivery_date' => $request->expected_delivery_date,
            'total_cost' => $totalCost,
            'created_by' => Auth::id(), // Ensure user is logged in
        ]);

        foreach ($request->products as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }

        return redirect()->route('admin.purchase.index')->with('success', 'Purchase Order created successfully (Draft).');
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product', 'creator'])->findOrFail($id);
        return view('admin.purchase.show', compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,confirmed,delivered,cancelled'
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $oldStatus = $po->status;
        $newStatus = $request->status;

        // If status changing to 'delivered' (from something else), update product stock
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            foreach ($po->items as $item) {
                // Increment product stock
                $item->product->increment('stock', $item->quantity);

                // TODO: Log stock movement here if table exists
            }
        }

        // If reverting FROM delivered (e.g. cancelled after delivery?), revert stock?
        // Usually you can't cancel a delivered order easily, but for safety:
        if ($oldStatus === 'delivered' && $newStatus !== 'delivered') {
            foreach ($po->items as $item) {
                $item->product->decrement('stock', $item->quantity);
            }
        }

        $po->update(['status' => $newStatus]);

        return back()->with('success', "Order status updated to " . ucfirst($newStatus));
    }
}
