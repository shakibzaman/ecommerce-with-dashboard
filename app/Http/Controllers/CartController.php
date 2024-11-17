<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        // Validate the request data
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);

        // Retrieve the product by ID
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Add product details to cart session
        $cart = session()->get('cart', []);

        $product_id = $request->product_id;

        if (isset($cart[$product_id])) {
            // If product exists in the cart, update its quantity
            $cart[$product_id]['quantity'] += $request->quantity;
        } else {
            // If product does not exist in the cart, add it
            $cart[$product_id] = [
                'id' => $request->product_id,
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $product->discount > 0 ? $product->discount : 0,
                'quantity' => $request->quantity,
                'image' => $product->image
            ];
        }

        // Save the updated cart to the session
        session()->put('cart', $cart);

        // Return a JSON response
        return response()->json(['message' => 'Product added to cart', 'cart' => $cart], 200);
    }
    public function quickOrder()
    {
        $carts = session()->get('cart', []);
        $totalPrice = 0;
        foreach ($carts as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }
        return view('homepage.cart.order-page', compact('carts', 'totalPrice'));
    }
    public function showCart()
    {
        $carts = session()->get('cart', []);
        return view('homepage.cart.list', compact('carts'));
    }
    public function clearCart()
    {
        session()->forget('cart');

        return redirect()->back()->with('message', 'Cart has been cleared!');
    }
    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $carts = session()->get('cart', []);
        $itemId = $request->item_id;

        if (isset($carts[$itemId])) {
            $carts[$itemId]['quantity'] = $request->quantity;
            session()->put('cart', $carts);
            return response()->json(['message' => 'Cart updated successfully']);
        }

        return response()->json(['message' => 'Item not found in cart'], 404);
    }
    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
        ]);

        // Logic to remove item from cart
        $cart = session()->get('cart', []);
        if (isset($cart[$request->item_id])) {
            unset($cart[$request->item_id]);
            session()->put('cart', $cart);
            // Prepare the updated cart HTML to be sent back as part of the response
            $updatedCartView = view('homepage.cart.modal.quick-order', ['carts' => $cart])->render();
            return response()->json(['message' => 'Item removed from cart.', 'cart' => $updatedCartView]);
        }

        return response()->json(['error' => 'Item not found in cart.'], 404);
    }
    public function getModalContent()
    {
        // Fetch the updated cart data from the session
        $carts = session()->get('cart', []);

        // Total calculations
        $totalQuantity = 0;
        $totalPrice = 0;
        foreach ($carts as $item) {
            $totalQuantity += $item['quantity'];
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Render the modal content view and pass the cart data
        $modalContent = view('homepage.cart.modal.quick-order', compact('carts', 'totalQuantity', 'totalPrice'))->render();

        return response()->json(['modalContent' => $modalContent]);
    }
    public function getCartData()
    {
        $carts = session()->get('cart', []); // Assuming you store cart in session
        $totalQuantity = 0;
        $totalPrice = 0;
        foreach ($carts as $item) {
            $totalQuantity += $item['quantity'];
            $totalPrice += $item['price'] * $item['quantity'];
        }

        return response()->json([
            'totalItems' => $totalQuantity,
            'totalPrice' => $totalPrice,
        ]);
    }
}
