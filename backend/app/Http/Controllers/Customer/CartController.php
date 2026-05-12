<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CustomDesign;

class CartController extends Controller
{
    /**
     * View the Cart Page
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('customer.cart.index', compact('cart'));
    }

    /**
     * Add an item to the cart
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);
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

        $cart = session()->get('cart', []);
        $price = $product->price;
        $designId = $request->input('custom_design_id');

        // Handle Customization
        if ($recipe) {
            $recipeData = json_decode($recipe, true);
            $price = $this->calculateCustomPrice($product->price, $recipeData);

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
        }

        // Generate unique key for cart (allows multiple different designs of same product)
        $cartKey = $designId ? $productId . '_custom_' . $designId : $productId;

        // Check if item already exists in cart, update quantity if so
        if (isset($cart[$cartKey])) {
            $newQuantity = $cart[$cartKey]['quantity'] + $quantity;

            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot add more. Stock limit reached!"
                ], 400);
            }

            $cart[$cartKey]['quantity'] = $newQuantity;
        } else {
            // New item to cart
            $cart[$cartKey] = [
                "product_id" => $product->product_id,
                "custom_design_id" => $designId,
                "name" => $product->name . ($designId ? ' (Customized)' : ''),
                "quantity" => (int) $quantity,
                "price" => $price,
                "image" => $snapshot ?: $product->image,
                "unit" => $product->unit,
                "sku" => $product->sku
            ];
        }

        session()->put('cart', $cart);

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
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        if ($productId && $quantity) {
            $cart = session()->get('cart', []);

            if (isset($cart[$productId])) {
                $product = Product::find($productId);

                if (!$product || $product->stock < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock!'
                    ], 400);
                }

                $cart[$productId]["quantity"] = (int) $quantity;
                session()->put('cart', $cart);

                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated.',
                    'cart_count' => $this->getCartCount()
                ]);
            }
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
        $productId = $request->input('product_id');

        if ($productId) {
            $cart = session()->get('cart', []);
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                session()->put('cart', $cart);
            }
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
        $cart = session()->get('cart', []);

        if (empty($selectedItems)) {
            return redirect()->back()->with('error', 'Please select at least one item to checkout.');
        }

        // Filter cart to only selected items
        $checkoutItems = [];
        foreach ($selectedItems as $id) {
            if (isset($cart[$id])) {
                $checkoutItems[$id] = $cart[$id];
            }
        }

        if (empty($checkoutItems)) {
            return redirect()->back()->with('error', 'Selected items are no longer available in cart.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $total = 0;
            foreach ($checkoutItems as $item) {
                $total += $item['price'] * $item['quantity'];

                // Verify stock again just in case
                $product = Product::find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: " . $item['name']);
                }
            }

            // Create Order
            $order = \App\Models\Order::create([
                'order_number' => 'ORDR-' . strtoupper(uniqid()),
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $total,
            ]);

            // Create Order Items
            foreach ($checkoutItems as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item['product_id'],
                    'custom_design_id' => $item['custom_design_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Decrease Stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            \Illuminate\Support\Facades\DB::commit();

            // Notify staff & admins about the new order
            $order->loadMissing('user');
            \App\Support\Notifier::staffAndAdmins(new \App\Notifications\NewOrderPlaced($order));

            // Remove only checked out items from cart
            foreach ($selectedItems as $id) {
                if (isset($cart[$id])) {
                    unset($cart[$id]);
                }
            }
            session()->put('cart', $cart);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully!'
                ]);
            }

            return redirect()->route('customer.orders.index')->with('success', 'Order placed successfully!');

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
        return response()->json([
            'count' => $this->getCartCount()
        ]);
    }

    /**
     * Helper to calculate cart count
     */
    private function getCartCount()
    {
        $cart = session()->get('cart', []);
        $totalItems = 0;
        foreach ($cart as $item) {
            $totalItems += $item['quantity'];
        }
        return $totalItems;
    }

    /**
     * Helper to calculate customization price server-side
     */
    private function calculateCustomPrice($basePrice, $recipe)
    {
        $extra = 0;
        $elements = $recipe['elements'] ?? [];

        $extra += count($elements['text'] ?? []) * 50;
        $extra += count($elements['shapes'] ?? []) * 30;
        $extra += count($elements['logos'] ?? []) * 150;

        if (isset($recipe['features']['led_lighting']) && $recipe['features']['led_lighting']) {
            $extra += 500;
        }

        return $basePrice + $extra;
    }
}
