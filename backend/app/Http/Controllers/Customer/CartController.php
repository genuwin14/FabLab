<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

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

        $product = Product::findOrFail($productId);

        // Check if stock is available
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock! Only {$product->stock} {$product->unit} available."
            ], 400);
        }

        $cart = session()->get('cart', []);

        // Check if item already exists in cart, update quantity if so
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $quantity;

            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot add more. Stock limit reached!"
                ], 400);
            }

            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            // New item to cart
            $cart[$productId] = [
                "product_id" => $product->product_id,
                "name" => $product->name,
                "quantity" => (int) $quantity,
                "price" => $product->price,
                "image" => $product->image,
                "unit" => $product->unit,
                "sku" => $product->sku
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $this->getCartCount()
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
}
