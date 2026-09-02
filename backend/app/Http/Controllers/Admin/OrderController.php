<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Order;

class OrderController extends Controller
{
    use \App\Http\Controllers\Concerns\ShowsOrderMaterials;

    public function index(Request $request)
    {
        // Cast rather than lean on input()'s default: submitting the filter form
        // with a field cleared sends `status=`, which ConvertEmptyStringsToNull
        // turns into null. The key is present, so the default never applies, and
        // an un-cast null would sail past the !== '' guard into a
        // `where status is null` that matches no order at all.
        $search = (string) $request->input('search', '');
        $status = (string) $request->input('status', '');
        $date = (string) $request->input('date', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = Order::with(['user', 'orderItems'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('fullname', 'like', "%{$search}%");
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

        $orders = $query->paginate($perPage)->withQueryString();

        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // The Design Inspection popout resolves a recipe's texture out of this.
        $customizerTextures = \App\Models\Texture::customizerPayload();

        return view('admin.order.order', compact('orders', 'search', 'status', 'date', 'perPage', 'statusCounts', 'customizerTextures'));
    }
    /**
     * Approve or reject an order awaiting review.
     *
     * Only a pending order can be reviewed: re-running this on an approved
     * order would deduct its materials a second time.
     */
    public function review(Request $request, $id, OrderStockService $stock)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled',
            'reason' => 'nullable|string|required_if:status,cancelled',
            // What the reviewer corrected the ink estimate to, keyed on the
            // material. The service ignores anything the order doesn't already
            // draw, so this only ever narrows or adjusts an existing line.
            'material_quantities' => 'nullable|array',
            'material_quantities.*' => 'nullable|numeric|min:0|max:99999999.99'
        ]);

        $order = Order::with(['user', 'orderItems.product.rawMaterials', 'orderItems.customDesign'])->findOrFail($id);

        if ($order->status !== 'pending') {
            return back()->with('error', "Order {$order->order_number} is already {$this->label($order->status)} and can no longer be reviewed.");
        }

        // Approving commits the shop to producing this, so refuse to do it on
        // materials that aren't there — otherwise stock silently goes negative.
        $overrides = $request->status === 'approved'
            ? array_filter((array) $request->input('material_quantities', []), fn ($value) => $value !== null && $value !== '')
            : [];

        if ($request->status === 'approved') {
            $shortages = $stock->shortages($order, $overrides);

            if ($shortages !== []) {
                return back()->with('error', 'Not enough stock to approve this order: ' . implode('; ', $shortages) . '.');
            }
        }

        DB::transaction(function () use ($order, $request, $stock, $overrides) {
            $order->update([
                'status' => $request->status,
                'reason' => $request->reason,
            ]);

            if ($request->status === 'approved') {
                // Reserved, not consumed: the shop has committed the material
                // but nobody has made anything yet. Staff starting production
                // turns each reservation into consumption.
                $stock->reserve($order, $overrides);
            } else {
                // Checkout took the product stock; a rejected order never got
                // as far as consuming materials.
                $stock->returnProducts($order);
            }
        });

        if ($request->status === 'approved') {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderReceipt($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send transaction slip email: ' . $e->getMessage());
            }
        }

        $this->notifyCustomer($order, 'pending', $request->status);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Cancel an order that is already past review, returning everything it
     * took: finished stock, raw materials and textures.
     */
    public function cancel(Request $request, $id, OrderStockService $stock)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $order = Order::with(['user', 'orderItems.product.rawMaterials', 'orderItems.customDesign'])->findOrFail($id);

        $cancellable = ['approved', 'processing', 'ready_for_pickup', 'for_delivery'];

        if (! in_array($order->status, $cancellable, true)) {
            $message = match ($order->status) {
                'pending' => "Order {$order->order_number} hasn't been reviewed yet — reject it from the review screen instead.",
                'awaiting_pr' => "Order {$order->order_number} is still waiting on its PR number — close it instead, which returns the stock.",
                default => "Order {$order->order_number} is {$this->label($order->status)} and can no longer be cancelled.",
            };

            return back()->with('error', $message);
        }

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $request, $stock) {
            $order->update([
                'status' => 'cancelled',
                'reason' => $request->reason,
            ]);

            $stock->returnProducts($order);
            $stock->restore($order);
        });

        $this->notifyCustomer($order, $oldStatus, 'cancelled');

        return back()->with('success', "Order {$order->order_number} cancelled and stock returned.");
    }

    /**
     * The two procurement documents a Purchase Request order needs, and what
     * each one releases. Uploading is the act that moves the order, so these
     * stay with the admin alongside review — staff never approve anything.
     */
    private const DOCUMENTS = [
        'noa' => [
            'column' => 'noa_path',
            'label' => 'Notice of Award',
            'from' => 'approved',
            'to' => 'processing',
            'unlocks' => 'production',
        ],
        'po' => [
            'column' => 'po_path',
            'label' => 'Purchase Order',
            'from' => 'processing',
            'to' => 'for_delivery',
            'unlocks' => 'delivery',
        ],
    ];

    /**
     * Attach a Notice of Award or Purchase Order and advance the order.
     */
    public function uploadDocument(Request $request, $id, string $type, OrderStockService $stock)
    {
        abort_unless(isset(self::DOCUMENTS[$type]), 404);

        $spec = self::DOCUMENTS[$type];

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $order = Order::with('user')->findOrFail($id);

        if (! $order->isPurchaseRequest()) {
            return back()->with('error', "Order {$order->order_number} was paid at the cashier, so it has no {$spec['label']} step.");
        }

        if ($order->status !== $spec['from']) {
            return back()->with('error', sprintf(
                'The %s can only be uploaded while an order is %s. Order %s is %s.',
                $spec['label'],
                $this->label($spec['from']),
                $order->order_number,
                $this->label($order->status)
            ));
        }

        // Procurement paperwork is not for the whole internet, so it goes to
        // the private disk and is served through an authenticated route —
        // unlike profile photos, which live on the public one.
        $previous = $order->{$spec['column']};
        $path = $request->file('document')->store("orders/{$order->order_id}", 'local');

        $oldStatus = $order->status;

        $order->update([
            $spec['column'] => $path,
            'status' => $spec['to'],
        ]);

        // A Purchase Request order reaches production through this upload
        // rather than through the staff button, so its reservations have to be
        // converted here too. Without this, PR orders would never consume
        // anything and the materials report would only ever see cash orders.
        if ($spec['to'] === 'processing') {
            $stock->startProduction($order);
        }

        if ($previous) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($previous);
        }

        $this->notifyCustomer($order, $oldStatus, $spec['to']);

        return back()->with('success', "{$spec['label']} uploaded — order {$order->order_number} released for {$spec['unlocks']}.");
    }

    /**
     * Stream a stored NOA or PO back. The admin middleware is the gate; the
     * file never sits on a publicly reachable path.
     */
    public function document($id, string $type)
    {
        abort_unless(isset(self::DOCUMENTS[$type]), 404);

        $order = Order::findOrFail($id);
        $path = $order->{self::DOCUMENTS[$type]['column']};

        abort_if(! $path || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
    }

    /**
     * Close an order that is still waiting on its PR number, before the
     * deadline does it automatically.
     */
    public function closePurchaseRequest(Request $request, $id, \App\Services\PurchaseRequestService $purchaseRequests)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);

        if (! $purchaseRequests->close($order, $request->reason)) {
            return back()->with('error', "Order {$order->order_number} is {$this->label($order->status)}, not waiting on a PR number.");
        }

        return back()->with('success', "Order {$order->order_number} closed and stock returned.");
    }

    private function notifyCustomer(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus !== $newStatus) {
            \App\Support\Notifier::customer($order->user, new \App\Notifications\OrderStatusChanged($order, $oldStatus, $newStatus));
        }
    }

    private function label(string $status): string
    {
        return str_replace('_', ' ', $status);
    }
}
