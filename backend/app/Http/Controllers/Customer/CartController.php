<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\CustomDesign;

/**
 * The cart is stored per user in `cart_items`, so it survives signing out,
 * session expiry, and moving between devices. Lines are addressed by a key of
 * product id, plus design id when the line carries a customization.
 */
class CartController extends Controller
{
    /**
     * View the Cart Page
     */
    public function index()
    {
        $this->absorbSessionCart();

        return view('customer.cart.index', ['cart' => $this->cartForView()]);
    }

    /**
     * Add an item to the cart
     */
    public function add(Request $request)
    {
        $this->absorbSessionCart();

        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));
        $recipe = $request->input('custom_recipe');
        $snapshot = $request->input('custom_snapshot');

        $product = Product::findOrFail($productId);

        // Check if stock is available
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock! Only {$product->stock} {$product->unit} available."
            ], 400);
        }

        $price = $product->price;
        $designId = $request->input('custom_design_id');
        $design = null;

        // Handle Customization
        if ($recipe) {
            $recipeData = json_decode($recipe, true);

            // Save/Update Design (Normalized)
            $design = CustomDesign::updateOrCreate(
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
            $designId = $design->custom_design_id;

            // Price the saved design through the model so the cart, My Designs
            // and the studio's live quote all agree — element fees plus the
            // selected texture's price modifier.
            $design->setRelation('product', $product);
            $price = $design->calculated_price;
        }

        $line = CartItem::where('user_id', auth()->id())
            ->where('product_id', $product->product_id)
            ->where('custom_design_id', $designId)
            ->first();

        if ($line) {
            $newQuantity = $line->quantity + $quantity;

            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot add more. Stock limit reached!"
                ], 400);
            }

            $line->update(['quantity' => $newQuantity, 'price' => $price]);
        } else {
            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $product->product_id,
                'custom_design_id' => $designId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        $message = 'Product added to cart successfully!';
        if ($recipe) {
            if ($design->wasRecentlyCreated) {
                $message = 'Design saved and added to cart!';
                \App\Support\Notifier::staffAndAdmins(new \App\Notifications\CustomDesignSubmitted($design));
            } elseif ($design->wasChanged()) {
                $message = 'Design updated and added to cart!';
            } else {
                $message = 'Product added to cart!'; // Already saved design
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_count' => $this->getCartCount(),
            'design_id' => $designId
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request)
    {
        $key = (string) $request->input('product_id');
        $quantity = (int) $request->input('quantity');

        $line = $this->lineForKey($key);

        if ($line && $quantity > 0) {
            $product = $line->product;

            if (! $product || $product->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock!'
                ], 400);
            }

            $line->update(['quantity' => $quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_count' => $this->getCartCount()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update cart.'
        ], 400);
    }

    /**
     * Remove an item from the cart
     */
    public function remove(Request $request)
    {
        $line = $this->lineForKey((string) $request->input('product_id'));

        if ($line) {
            $line->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_count' => $this->getCartCount()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to remove item.'
        ], 400);
    }

    /**
     * Checkout and create order
     */
    public function checkout(Request $request)
    {
        $selectedItems = $request->input('selected_items', []);

        if (empty($selectedItems)) {
            return redirect()->back()->with('error', 'Please select at least one item to checkout.');
        }

        $request->validate([
            'payment_method' => 'nullable|in:' . implode(',', \App\Models\Order::METHODS),
        ]);

        $method = $request->input('payment_method', \App\Models\Order::METHOD_CASH);

        $checkoutLines = $this->linesForKeys($selectedItems);

        if ($checkoutLines->isEmpty()) {
            return redirect()->back()->with('error', 'Selected items are no longer available in cart.');
        }

        // A Purchase Request order can't be reviewed until procurement issues a
        // PR number, so it parks short of pending and starts its clock.
        $isPurchaseRequest = $method === \App\Models\Order::METHOD_PR;
        $status = $isPurchaseRequest ? 'awaiting_pr' : 'pending';
        $prDeadline = $isPurchaseRequest
            ? now()->addDays((int) config('fablab.pr_deadline_days'))->endOfDay()
            : null;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $total = 0;
            foreach ($checkoutLines as $line) {
                $total += $line->price * $line->quantity;

                // Verify stock again just in case
                $product = $line->product;
                if (! $product || $product->stock < $line->quantity) {
                    throw new \Exception("Insufficient stock for product: " . ($product->name ?? 'unknown'));
                }
            }

            // Create Order. Two customers checking out in the same moment can
            // read the same daily sequence, so let the unique index settle it
            // and take the next number on the retry.
            $order = null;

            for ($attempt = 1; $attempt <= 5; $attempt++) {
                try {
                    $order = \App\Models\Order::create([
                        'order_number' => \App\Models\Order::nextOrderNumber(),
                        'user_id' => auth()->id(),
                        'status' => $status,
                        'payment_method' => $method,
                        'pr_deadline' => $prDeadline,
                        'total_amount' => $total,
                    ]);

                    break;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    if ($attempt === 5) {
                        throw $e;
                    }
                }
            }

            // Create Order Items
            foreach ($checkoutLines as $line) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $line->product_id,
                    'custom_design_id' => $line->custom_design_id,
                    'quantity' => $line->quantity,
                    'price' => $line->price
                ]);

                // Decrease Stock
                $line->product->decrement('stock', $line->quantity);
            }

            // Only the checked-out lines leave the cart; the rest stay put.
            CartItem::whereIn('cart_item_id', $checkoutLines->pluck('cart_item_id'))->delete();

            \Illuminate\Support\Facades\DB::commit();

            $order->loadMissing('user', 'orderItems.product');

            // A PR order isn't reviewable yet, so there is nothing for staff to
            // act on. They hear about it when the PR number lands.
            if (! $isPurchaseRequest) {
                \App\Support\Notifier::staffAndAdmins(new \App\Notifications\NewOrderPlaced($order));
            }

            // The customer's confirmation goes out either way — a PR order
            // needs its filing instructions in writing precisely because
            // nothing else will email them until an admin reviews it.
            \App\Support\Notifier::customer($order->user, new \App\Notifications\OrderPlaced($order));

            $message = $isPurchaseRequest
                ? sprintf(
                    'Order placed. File your Purchase Request with %s and enter the PR number here by %s — the order is held until then.',
                    config('fablab.procurement_email'),
                    $order->pr_deadline->format('j M Y')
                )
                : 'Order placed successfully!';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'awaiting_pr' => $isPurchaseRequest,
                ]);
            }

            return redirect()->route('customer.orders.index')->with('success', $message);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the total items in the cart
     */
    public function count()
    {
        $this->absorbSessionCart();

        return response()->json([
            'count' => $this->getCartCount()
        ]);
    }

    /**
     * The shape the cart view renders: keyed by line key, with the product's
     * current name, image and SKU resolved at read time.
     *
     * @return array<string, array<string, mixed>>
     */
    private function cartForView(): array
    {
        return CartItem::with(['product', 'customDesign'])
            ->where('user_id', auth()->id())
            ->get()
            ->reject(fn (CartItem $line) => $line->product === null)
            ->mapWithKeys(function (CartItem $line) {
                $product = $line->product;
                $snapshot = $line->customDesign?->snapshot;

                return [$line->lineKey() => [
                    'product_id' => $product->product_id,
                    'custom_design_id' => $line->custom_design_id,
                    'name' => $product->name . ($line->custom_design_id ? ' (Customized)' : ''),
                    'quantity' => $line->quantity,
                    'price' => $line->price,
                    'image' => $snapshot ?: $product->image_url,
                    'unit' => $product->unit,
                    'sku' => $product->sku,
                ]];
            })
            ->all();
    }

    private function lineForKey(string $key): ?CartItem
    {
        if ($key === '') {
            return null;
        }

        [$productId, $designId] = CartItem::parseKey($key);

        return CartItem::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->where('custom_design_id', $designId)
            ->first();
    }

    /** @param array<int, string> $keys */
    private function linesForKeys(array $keys): \Illuminate\Support\Collection
    {
        return collect($keys)
            ->map(fn ($key) => $this->lineForKey((string) $key))
            ->filter()
            ->values();
    }

    /**
     * Helper to calculate cart count
     */
    private function getCartCount()
    {
        return (int) CartItem::where('user_id', auth()->id())->sum('quantity');
    }

    /**
     * Carts that were sitting in the session when this became a table are
     * moved across on the customer's next visit rather than being dropped.
     */
    private function absorbSessionCart(): void
    {
        $sessionCart = session()->get('cart', []);

        if (empty($sessionCart) || ! auth()->check()) {
            return;
        }

        foreach ($sessionCart as $item) {
            $productId = $item['product_id'] ?? null;
            if (! $productId || ! Product::find($productId)) {
                continue;
            }

            $designId = $item['custom_design_id'] ?? null;

            $line = CartItem::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->where('custom_design_id', $designId)
                ->first();

            if ($line) {
                $line->increment('quantity', (int) ($item['quantity'] ?? 1));
                continue;
            }

            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'custom_design_id' => $designId,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
            ]);
        }

        session()->forget('cart');
    }
}
