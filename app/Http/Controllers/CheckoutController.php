<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\Address;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
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

        $addresses = Address::where('user_id', $user->id)->get();

        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

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
            'shop_id' => 5469429,
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
            'length' => (float)$length,
            'width' => $width,
            'height' => (float)$height,
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
            'order_date' => Carbon::now('Asia/Ho_Chi_Minh'),
            'total_amount' => $totalAmount,
            'total_discount' => $discount,
            'shipping_fee' => $shippingFee,
            'discount_id' => $request->discount_id ?? null,
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

        // Tạo giao dịch
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'transaction_date' => now(),
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);
        if ($request->discount_id) {
            $discount = Discount::find($request->discount_id);
            if ($discount && $discount->usage_limit > 0) {
                $discount->decrement('usage_limit');
            }
        }

         $cart->items()->delete();
         $cart->delete();

        // Xử lý theo phương thức thanh toán
        if ($request->payment_method === 'cod') {
            return $this->handleCOD($order, $transaction);
        } elseif ($request->payment_method === 'momo') {
            $momoUrl = $this->createMomo($order, $totalAmount);
            return response()->json(['momoUrl' => $momoUrl], 200);
        } elseif ($request->payment_method === 'vnpay') {
            $vnpUrl = $this->createVNPayUrl($order, $totalAmount);
            return response()->json(['vnpUrl' => $vnpUrl], 200);
        }
    }

    private function handleCOD($order, $transaction)
    {
        return response()->json([
            'message' => 'Order placed successfully with COD payment.',
            'order_id' => $order->id,
            'transaction_id' => $transaction->id,
        ], 201);
    }

    private function createVNPayUrl($order, $totalAmount)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://127.0.0.1:8000/api/vnpay/callback";
        $vnp_TmnCode = "AHA4FH13";
        $vnp_HashSecret = '2SMK29RKUNOOTWN9Z8HJT9P658QRS13T';

        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = 'Noi dung thanh toan';
        $vnp_OrderType = 'Thanh toán qua VNPay';
        $vnp_Amount = $totalAmount * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );


        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        if (isset($request->redirect)) {
            return redirect($vnp_Url);
        }
        Log::info('VNPay URL created', ['url' => $vnp_Url]);
        return $vnp_Url; // Return the complete VNPay URL
    }

    public function vnpayCallback(Request $request)
    {
        $inputData = array();
        $returnData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = "2SMK29RKUNOOTWN9Z8HJT9P658QRS13T";
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnp_Amount = $inputData['vnp_Amount'] / 100; // Số tiền thanh toán VNPAY phản hồi
        $orderId = $inputData['vnp_TxnRef'];

        try {
            // Check checksum of the response
            if ($secureHash == $vnp_SecureHash) {
                $order = Order::find($orderId); // Find the order by orderId
                if ($order) {
                    // Validate the amount
                    if ($order->total_amount == $vnp_Amount) {
                        // Check if the order's associated transaction is pending
                        $transaction = Transaction::where('order_id', $orderId)->first();
                        if ($transaction && $transaction->transaction_status == 'pending') {
                            // Handle VNPay response
                            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                                // Payment successful
                                $transaction->transaction_status = 'paid'; // Mark the transaction as successful
                            } else {
                                // Payment failed or error
                                $transaction->transaction_status = 'failed'; // Mark the transaction as failed
                            }
                            $transaction->save();

                            // Return success response to VNPay
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            // Transaction already confirmed
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Transaction already confirmed';
                        }
                    } else {
                        // Invalid amount
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                    }
                } else {
                    // Order not found
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                // Invalid signature
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (Exception $e) {
            // Unknown error
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';
        }

        return response()->json($returnData);
    }



    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    private function createMomo($order, $totalAmount)
    {
        $endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
    
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán đơn hàng qua MoMo";
        $amount = $totalAmount;
        $orderId = $order->id . '_' . time(); // Mã đơn hàng duy nhất
        $redirectUrl = "http://127.0.0.1:8000/api/orders";
        $ipnUrl = "http://127.0.0.1:8000/api/orders";
        $extraData = "";
    
        $requestId = time();
        $requestType = "captureWallet"; // Hoặc "payWithATM" nếu bạn dùng ATM nội địa
    
        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        
        Log::info('Raw hash string: ' . $rawHash);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "MoMo Test",
            'storeId' => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];
        
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);
        
        if (isset($jsonResult['payUrl'])) {
            return response()->json(['payUrl' => $jsonResult['payUrl']], 200);
        } else {
            Log::error('Momo payment error', [
                'response' => $jsonResult,
                'message' => isset($jsonResult['message']) ? $jsonResult['message'] : 'No message provided'
            ]);
        }
        
    }
    
}
