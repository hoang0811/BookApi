<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Resources\AddressResource;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // Lấy danh sách địa chỉ của người dùng
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $addresses = Address::where('user_id', $userId)->get();

        return AddressResource::collection($addresses);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'province_id' => 'required|integer',
            'street' => 'required|string|max:255',
            'address_type' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new address entry
        $address = Address::create(array_merge($request->all(), ['user_id' => $request->user()->id]));

        return new AddressResource($address);
    }

    // Xem thông tin địa chỉ cụ thể
    public function show($id)
    {
        $address = Address::findOrFail($id);
        return new AddressResource($address);
    }

    // Cập nhật thông tin địa chỉ
    public function update(Request $request, $id)
    {
        $address = Address::findOrFail($id);

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'province_id' => 'required|integer',
            'street' => 'sometimes|required|string|max:255',
            'address_type' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the address entry
        $address->update($request->all());

        return new AddressResource($address);
    }

    // Xóa địa chỉ
    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Địa chỉ đã được xóa thành công'], 200);
    }
}
