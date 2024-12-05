<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Admin: Get all orders.
     */
    public function adminIndex()
    {
        $orders = Order::with(['user', 'orderDetails'])->orderBy('created_at', 'desc')->get();
        return response()->json($orders, 200);
    }

    /**
     * User: Get their own orders.
     */
    public function userIndex(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('orderDetails')
            ->orderBy('created_at', 'desc')->get();

        return response()->json($orders, 200);
    }

    /**
     * Admin: Get detailed order by ID.
     */
    public function adminShow($id)
    {
        $order = Order::with(['user', 'orderDetails.book', 'transaction'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * User: Get their own order details.
     */
    public function userShow($id, Request $request)
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['orderDetails.book', 'transaction'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or unauthorized.'], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * User: Update order status to "canceled".
     */
    public function userUpdateStatus(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or unauthorized.'], 404);
        }

        if ($order->order_status !== 'ordered') {
            return response()->json(['message' => 'You can only cancel orders with status "ordered".'], 403);
        }

        $order->order_status = 'canceled';
        $order->canceled_date = Carbon::now();
        $order->save();

        return response()->json(['message' => 'Order status updated to "canceled".'], 200);
    }

    /**
     * Admin: Update any order status.
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:ordered,delivered,shipping,canceled,rejected,returned',
        ]);

        $order = Order::with('orderDetails.book')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($request->order_status === 'shipping') {
            $ghnResponse = $this->createGHNOrder($order);
            return $ghnResponse;
            if (!$ghnResponse['success']) {
                return response()->json(['message' => 'Failed to create GHN order', 'error' => $ghnResponse['error']], 500);
            }

            // Cập nhật mã GHN vào đơn hàng
            $order->update([
                'order_status' => 'shipping',
                'ghn' => $ghnResponse['data']['order_code'], // Mã vận đơn từ GHN
            ]);

            return response()->json(['message' => 'Order status updated to shipping and GHN order created.'], 200);
        }

        // Cập nhật trạng thái thông thường
        $order->update(['order_status' => $request->order_status]);

        return response()->json(['message' => 'Order status updated successfully.'], 200);
    }
    public function createGHNOrder($order)
    {
        // Thông tin từ bảng `orders`
        $fromAddress = [
            'from_name' => 'Nha Sach',
            'from_phone' => '0346024594',
            'from_address' => '180 Cao Lỗ, Phường 4, Quận 8, Hồ Chí Minh, VietNam',
            'from_ward_name' => 'Phường 4',
            'from_district_name' => 'Quận 8',
            'from_province_name' => 'HCM',
        ];

        $toAddress = [
            'to_name' => $order->name,
            'to_phone' => $order->phone,
            'to_address' => $order->street,
            'to_ward_code' => $order->ward,
            'to_district_id' => (int) $order->district,
        ];

        $items = [];
        $totalWeight = 0;
        $totalLength = 0;
        $totalWidth = 0;
        $totalHeight = 0;

        foreach ($order->orderDetails as $detail) {
            $items[] = [
                'name' => $detail->book->title,
                'code' => $detail->book->isbn,
                'quantity' => $detail->quantity,
                'price' =>  (int)$detail->price,
                'length' =>  (int)$detail->book->length,
                'width' =>  (int)$detail->book->width,
                'height' =>  (int)$detail->book->height,
                'weight' =>  (int)$detail->book->weight,
            ];
            $totalWeight += $detail->book->weight * $detail->quantity;
            $totalLength = max($totalLength, $detail->book->length); // Lấy chiều dài lớn nhất.
            $totalWidth += $detail->book->width * $detail->quantity; // Cộng dồn chiều rộng.
            $totalHeight = max($totalHeight, $detail->book->height); // Lấy chiều cao lớn nhất.

        }
        $total = 0;
        if($order->transaction->payment_method=='cod')
        {
            $total = $order->total_amount;
        }else
        {
            $total = 0;
        }
        // Dữ liệu yêu cầu GHN
        $payload = array_merge($fromAddress, $toAddress, [
            'shop_id' => 5469429,
            'payment_type_id' => 2, // Người mua trả phí vận chuyển
            'required_note' => 'KHONGCHOXEMHANG',
            'cod_amount' => (int) $order->total_amount,
            'content' => 'Thông tin đơn hàng #' . $order->id,
            'weight' => (int) $totalWeight,
            'length' => (int) $totalLength,
            'width' => (int) $totalWidth,
            'height' => (int) $totalHeight,
            'return_phone' => '0346024594',
            'return_address' => '180 Cao Lỗ, Phường 4, Quận 8, Hồ Chí Minh, VietNam',
            'insurance_value' =>  (int)$order->total_amount,
            'service_type_id' => 2, // Giao hàng TMĐT
            'service_id' => 53320, // Thay bằng ID dịch vụ từ API Service
            'pick_shift' => [2], // Ca lấy hàng
            'coupon' => null, // Mã giảm giá, nếu có
            'items' => $items,
        ]);
        // Gửi yêu cầu đến GHN
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => '6b1037e4-a742-11ef-b2c1-a2ca9b658e40',
            'ShopId' => 5469429,
        ])->withOptions([
            'verify' => false,
            ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create', $payload);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data']];
        }

        return ['success' => false, 'error' => $response->json()];
    }
    
    /**
     * Admin: Delete an order.
     */
    public function adminDestroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.'], 200);
    }
    public function statistics()
    {
        $totalOrders = Order::count(); // Tổng số đơn hàng
        $totalRevenue = Order::where('order_status', 'delivered')->sum('total_amount'); // Tổng doanh thu
        $pendingOrders = Order::where('order_status', 'ordered')->count(); // Đơn hàng đang chờ
        $canceledOrders = Order::where('order_status', 'canceled')->count(); // Đơn hàng bị hủy
        $returnedOrders = Order::where('order_status', 'returned')->count(); // Đơn hàng trả lại

        $ordersByMonth = Order::selectRaw('MONTH(order_date) as month, YEAR(order_date) as year, COUNT(*) as count')
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'pendingOrders' => $pendingOrders,
            'canceledOrders' => $canceledOrders,
            'returnedOrders' => $returnedOrders,
            'ordersByMonth' => $ordersByMonth,
        ]);
    }
}
