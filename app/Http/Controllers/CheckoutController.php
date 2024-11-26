<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


class CheckoutController extends Controller
{
    public function getCheckoutData(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.book')->where('user_id', $user->id)->first();
    
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }
    
        // Lấy tất cả địa chỉ của người dùng
        $addresses = Address::where('user_id', $user->id)->get();
    
        // Lấy địa chỉ mặc định của người dùng
        $defaultAddress = Address::where('user_id', $user->id)
                                 ->where('is_default', 1)
                                 ->first();
    
        // Tính phí vận chuyển cho giỏ hàng với địa chỉ mặc định
        $shippingFee = $this->getCartShippingFee($cart, $defaultAddress);
    
        return response()->json([
            'cart' => $cart,
            'addresses' => $addresses,
            'default_address' => $defaultAddress,
            'shipping_fee' => $shippingFee,
        ]);
    }
    
    public function getAvailableServices($fromDistrict, $toDistrict)
    {

    $response = Http::withHeaders([
        'token' => '6b1037e4-a742-11ef-b2c1-a2ca9b658e40',
        ])->withOptions([
        'verify' => false,  // Tắt xác minh SSL
    ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services', [
        'shop_id' => 5469429,
        'from_district' => (int) $fromDistrict,
        'to_district' => (int) $toDistrict,
    ]);

    if ($response->successful()) {
        return $response->json();
    }
    return null;

}

    // Hàm tính phí vận chuyển
    public function calculateShippingFee($serviceId, $insuranceValue, $coupon, $fromDistrictId, $toDistrictId, $toWardCode, $weight, $length, $width, $height)
    {
        $response = Http::withHeaders([
            'token' => '6b1037e4-a742-11ef-b2c1-a2ca9b658e40',
            'shop_id' =>5469429,
            ])->withOptions([
                'verify' => false,
        ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
            'service_id' => $serviceId,
            'insurance_value' => $insuranceValue,
            'coupon' => $coupon,
            'from_district_id' => $fromDistrictId,
            'to_district_id' => (int) $toDistrictId,
            'to_ward_code' => $toWardCode,
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // Hàm tính phí vận chuyển giỏ hàng
    public function getCartShippingFee($cart, $address)
    {
        // Lấy thông tin địa chỉ
        $fromDistrictId = 1450;  // Quận của người gửi (giả sử)
        $toDistrictId = $address->district_id;  // Quận của người nhận
        $toWardCode = $address->ward_id;  // Phường/Xã người nhận

        // Tính tổng trọng lượng giỏ hàng
        $totalWeight = 0;
        $totalValue = 0;
        $totalLength = 0;
        $totalWidth = 0;
        $totalHeight = 0;

        foreach ($cart->items as $item) {
            $totalWeight += $item->book->weight * $item->quantity;  // Tính trọng lượng tổng
            $totalValue += $item->price * $item->quantity;  // Tính giá trị tổng giỏ hàng
        }
        foreach ($cart->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $totalLength = max($totalLength, $item->book->length); // Chiều dài lớn nhất
                $totalWidth += $item->book->width; // Cộng dồn chiều rộng
                $totalHeight = max($totalHeight, $item->book->height); // Chiều cao lớn nhất
            }
        }

        // Lấy gói dịch vụ khả dụng
        $availableServices = $this->getAvailableServices($fromDistrictId, $toDistrictId);

        if (!$availableServices || empty($availableServices['data'])) {
            return 'Không có gói dịch vụ khả dụng';
        }

        // Chọn một gói dịch vụ (giả sử lấy service_id đầu tiên)
        $serviceId = $availableServices['data'][0]['service_id'];

        // Tính phí vận chuyển
        $shippingFeeResponse = $this->calculateShippingFee(
            $serviceId,
            $totalValue,
            null,
            $fromDistrictId,
            $toDistrictId,
            $toWardCode,
            $totalWeight,
            $totalLength,  // Tổng chiều dài
            $totalWidth,   // Tổng chiều rộng
            $totalHeight   // Tổng chiều cao
        );
       
        if ($shippingFeeResponse) {
            return $shippingFeeResponse['data']['total'];  // Trả về tổng phí vận chuyển
        }

        return 'Không thể tính phí vận chuyển';
    }

    // Hàm lấy phí vận chuyển cho giỏ hàng
    public function getShippingFeeForCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.book')->where('user_id', $user->id)->first();

        $addressId = $request->address_id;
        $address = $addressId ? Address::find($addressId) : Address::where('user_id', $cart->user_id)->where('is_default', 1)->first();
        if (!$address) {
            return response()->json(['error' => 'Địa chỉ không hợp lệ.'], 404);
        }
    
        $shippingFee = $this->getCartShippingFee($cart, $address);
        
        if ($shippingFee === 'Không có gói dịch vụ khả dụng') {
            return response()->json(['error' => 'Không có gói dịch vụ khả dụng.'], 400);
        }
        
        return response()->json(['shipping_fee' => $shippingFee]);
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

        if ($discountValue > 0) {
            $discount = Discount::where('code', $request->code)->first();
            return response()->json([
                'discount' => $discountValue,
                'discount_id' => $discount->id,
            ]);
        }

        return response()->json([
            'discount' => 0,
            'discount_id' => null,
            'message' => 'Invalid or expired discount code.',
        ]);
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
            return round($subtotal * $discount->discount_value / 100, 2);
        } else {
            return min($discount->discount_value, $subtotal);
        }
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();
    
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }
    
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|string|in:cod,momo,vnpay',
        ]);
    
        // Lấy địa chỉ dựa vào address_id
        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->first();
    
        if (!$address) {
            return response()->json(['message' => 'Invalid address.'], 400);
        }
    
        $shippingFee = $request->shipping_fee ?? 0;
        $discount = $request->discount ?? 0;
        $totalAmount = $cart->subtotal + $shippingFee - $discount;
    
        if ($totalAmount < 0) {
            return response()->json(['message' => 'Total amount cannot be negative.'], 400);
        }
    
        // Lưu thông tin đơn hàng
        $order = Order::create([
            'user_id' => $user->id,
            'name' => $address->name,
            'phone' => $address->phone,
            'province' => $address->province_id,
            'district' => $address->district_id,
            'ward' => $address->ward_id,
            'street' => $address->street,
            'order_date' => now(),
            'total_amount' => $totalAmount,
            'total_discount' => $discount,
            'shipping_fee' => $shippingFee,
            'discount_id' => $request->discount_id ?? null,
            'mavd' => "",
            'order_status' => 'ordered',
        ]);
    
        // Lưu chi tiết đơn hàng
        foreach ($cart->items as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'book_id' => $item->book_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);
        }
    
        // Xử lý giao dịch theo phương thức thanh toán
        if ($request->payment_method === 'cod') {
            Transaction::create([
                'order_id' => $order->id,
                'transaction_date' => now(),
                'amount' => $totalAmount,
                'payment_method' => 'cod',
                'transaction_status' => 'pending',
            ]);
        } elseif ($request->payment_method === 'momo') {
            $paymentResponse = $this->processMoMoPayment($order);
            if ($paymentResponse['status'] === 'success') {
                Transaction::create([
                    'order_id' => $order->id,
                    'transaction_date' => now(),
                    'amount' => $totalAmount,
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
                    'amount' => $totalAmount,
                    'payment_method' => 'vnpay',
                    'transaction_status' => 'completed',
                ]);
            } else {
                return response()->json(['message' => 'VNPay payment failed.'], 400);
            }
        }
    
        // Giảm số lần sử dụng mã giảm giá (nếu có)
        if ($request->discount_id) {
            $discount = Discount::find($request->discount_id);
            if ($discount && $discount->usage_limit > 0) {
                $discount->decrement('usage_limit');
            }
        }
    
        // Xóa giỏ hàng sau khi đặt hàng thành công
        $cart->items()->delete();
        $cart->delete();
    
        return response()->json([
            'message' => 'Checkout completed successfully.',
            'order_id' => $order->id,
        ], 201);
    }
    


    function processMoMoPayment() {}
    function processVNPayPayment() {}
}
