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
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        return response()->json([
            'cart' => new CartResource($cart),
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
    

    protected function calculateTotals(Cart $cart)
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $subtotal += $item->quantity * $item->price;
        }

        $cart->subtotal = $subtotal;
        $cart->save();
    }

    public function applyDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        $discountValue = $this->calculateDiscountValue($request->code, $cart->subtotal);

        return response()->json(['discount' => $discountValue]);
    }

    protected function calculateDiscountValue($code, $subtotal)
    {
        $discount = Discount::where('code', $code)->first();

        if (!$discount || !$discount->is_active || now()->greaterThan($discount->end_date)) {
            return 0;
        }

        if ($subtotal < $discount->cart_value) {
            return 0;
        }

        if ($discount->usage_limit <= 0) {
            return 0;
        }

        if ($discount->discount_type === 'percent') {
            $discountValue = round($subtotal * $discount->discount_value / 100, 2);
        } else {
            $discountValue = min($discount->discount_value, $subtotal);
        }

        return $discountValue;
    }

    protected function calculateShippingFee(Cart $cart)
    {
        return 30000;
    }
}

