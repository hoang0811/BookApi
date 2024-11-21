<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)->orderBy('order_date', 'desc')->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found.'], 404);
        }

        return response()->json($orders);
    }
    

    public function show($id)
    {
        $order = Order::with('orderDetails', 'orderDetails.book')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json($order);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:ordered,delivered,canceled,rejected,returned',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->order_status = $request->order_status;
        $order->save();

        return response()->json(['message' => 'Order status updated successfully.', 'order' => $order]);
    }
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->order_status === 'delivered' || $order->order_status === 'returned') {
            return response()->json(['message' => 'Cannot cancel delivered or returned orders.'], 400);
        }

        $order->order_status = 'canceled';
        $order->canceled_at = now();
        $order->save();

        return response()->json(['message' => 'Order has been canceled successfully.']);
    }
    public function refundOrder(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->order_status !== 'delivered') {
            return response()->json(['message' => 'Refund can only be processed for delivered orders.'], 400);
        }

        $refundAmount = $order->total_amount - $order->total_discount;

        Transaction::create([
            'order_id' => $order->id,
            'transaction_date' => now(),
            'amount' => $refundAmount,
            'payment_method' => 'cod',
            'transaction_status' => 'refunded',
            'refund_amount' => $refundAmount,
        ]);

        $order->order_status = 'returned';
        $order->save();

        return response()->json(['message' => 'Refund processed successfully.', 'order' => $order]);
    }
    public function createForAdmin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'district' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'district' => $request->district,
            'province' => $request->province,
            'ward' => $request->ward,
            'street' => $request->street,
            'order_date' => now(),
            'total_amount' => 0,
            'total_discount' => 0,
            'order_status' => 'ordered',
        ]);

        $totalAmount = 0;

        foreach ($request->items as $item) {
            $book = Book::find($item['book_id']);
            $totalAmount += $book->price * $item['quantity'];

            OrderDetail::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $item['quantity'],
                'price' => $book->price,
            ]);
        }

        $order->total_amount = $totalAmount;
        $order->save();

        return response()->json(['message' => 'Order created successfully for the user.', 'order' => $order]);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'district' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'payment_method' => 'required|string|in:cod,momo,vnpay',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'district' => $request->district,
            'province' => $request->province,
            'ward' => $request->ward,
            'street' => $request->street,
            'order_date' => now(),
            'total_amount' => $cart->subtotal,
            'total_discount' => $request->discount ?? 0,
            'shipping_fre' => $request ->shipping_fee,
            'discount_id' => $request->discount_id ?? null,
            'mavd'=> "",
            'order_status' => 'ordered',
        ]);

        foreach ($cart->items as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'book_id' => $item->book_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'option' => $item->option ?? null,
            ]);
        }
        if ($request->payment_method === 'cod') {
            Transaction::create([
                'order_id' => $order->id,
                'transaction_date' => now(),
                'amount' => $cart->subtotal - ($request->discount ?? 0),
                'payment_method' => 'cod',
                'transaction_status' => 'pending',
            ]);
        } elseif ($request->payment_method === 'momo') {

            $paymentResponse = $this->processMoMoPayment($order);
            
            if ($paymentResponse['status'] === 'success') {
                Transaction::create([
                    'order_id' => $order->id,
                    'transaction_date' => now(),
                    'amount' => $cart->subtotal - ($request->discount ?? 0),
                    'payment_method' => 'momo',
                    'transaction_status' => 'completed',
                ]);
            } else {
                return response()->json(['message' => 'MoMo payment failed.'], 400);
            }
        } elseif ($request->payment_method === 'vnpay') {

            $paymentResponse = $this->processVNPayPayment($order);
            
            if ($paymentResponse['status'] === 'success') {
                Transaction::create([
                    'order_id' => $order->id,
                    'transaction_date' => now(),
                    'amount' => $cart->subtotal - ($request->discount ?? 0),
                    'payment_method' => 'vnpay',
                    'transaction_status' => 'completed',
                ]);
            } else {
                return response()->json(['message' => 'VNPay payment failed.'], 400);
            }
        }
        
        
        if ($request->discount_id) {
            $discount = Discount::find($request->discount_id);
            if ($discount && $discount->usage_limit > 0) {
                $discount->decrement('usage_limit');
            }
        }


        $cart->items()->delete();
        $cart->delete();

        return response()->json([
            'message' => 'Checkout completed successfully with COD.',
            'order_id' => $order->id,
        ], 201);
    }
    function tinhtien ()
    {
        
    }

    function processMoMoPayment()
    {
    }
    function processVNPayPayment()
    {

    }

}
