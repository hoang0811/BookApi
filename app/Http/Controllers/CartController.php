<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.book')->where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        return response()->json([
            'cart' => $cart,
        ]);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $book = Book::find($request->book_id);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cartItem = $cart->items()->where('book_id', $request->book_id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;

            if ($newQuantity > $book->quantity) {
                return response()->json([
                    'message' => 'Requested quantity exceeds available stock.',
                    'available_stock' => $book->quantity
                ], 400);
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->price = $book->discount_price ?? $book->original_price;
            $cartItem->save();
        } else {
            $newQuantity = min($request->quantity, $book->quantity);
            $cart->items()->create([
                'book_id' => $request->book_id,
                'quantity' => $newQuantity,
                'price' => $book->discount_price ?? $book->original_price,
            ]);
        }

        $this->calculateTotals($cart);

        return new CartResource($cart);
    }
    public function increaseItem(Request $request, $itemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        $cartItem = $cart->items()->where('id', $itemId)->first();
    
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }
    
        $book = Book::find($cartItem->book_id);
        $newQuantity = $cartItem->quantity + 1;
    
        // Kiểm tra nếu số lượng yêu cầu vượt quá số lượng còn lại trong kho
        if ($newQuantity > $book->quantity) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock.',
                'available_stock' => $book->quantity
            ], 400);
        }
    
        // Cập nhật số lượng
        $cartItem->quantity = $newQuantity;
        $cartItem->save();
    
        // Tính toán lại tổng giỏ hàng
        $this->calculateTotals($cart);
    
        return new CartResource($cart);
    }
    


    public function decreaseItem(Request $request, $itemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        $cartItem = $cart->items()->where('id', $itemId)->first();
    
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }
    
        if ($cartItem->quantity > 1) {
            // Giảm số lượng sản phẩm
            $cartItem->quantity--;
            $cartItem->save();
        } else {
            // Xóa sản phẩm khỏi giỏ nếu số lượng là 1
            $cartItem->delete();
        }
    
        // Tính toán lại tổng giỏ hàng
        $this->calculateTotals($cart);
    
        return new CartResource($cart);
    }
    

    public function removeItem(Request $request, $itemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        $cartItem = $cart->items()->where('id', $itemId)->first();
    
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }
    
        // Xóa sản phẩm khỏi giỏ hàng
        $cartItem->delete();
    
        // Tính toán lại tổng giỏ hàng
        $this->calculateTotals($cart);
    
        return response()->json(['message' => 'Item removed from cart.']);
    }
    

    public function clearCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }
    
        $cart->items()->delete();
        $this->calculateTotals($cart);
    
        return response()->json(['message' => 'Cart cleared successfully.']);
    }
    


    protected function calculateTotals(Cart $cart)
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $subtotal += $item->quantity * $item->price;
        }

        $cart->subtotal = $subtotal;
        $cart->save();
    }
}
