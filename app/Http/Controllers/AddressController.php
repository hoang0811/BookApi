<?php
namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $addresses = Address::where('user_id', $userId)->get();
        return AddressResource::collection($addresses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'required|exists:wards,id',
            'province_id' => 'required|exists:provinces,id',
            'street' => 'required|string|max:255',
            'address_type' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address = Address::create(array_merge($request->all(), ['user_id' => $request->user()->id]));
        return new AddressResource($address);
    }

    public function show($id)
    {
        $address = Address::findOrFail($id);
        return new AddressResource($address);
    }

    public function update(Request $request, $id)
    {
        $address = Address::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email',
            'district_id' => 'sometimes|required|exists:districts,id',
            'ward_id' => 'sometimes|required|exists:wards,id',
            'province_id' => 'sometimes|required|exists:provinces,id',
            'street' => 'sometimes|required|string|max:255',
            'address_type' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address->update($request->all());
        return new AddressResource($address);
    }

    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Địa chỉ đã được xóa thành công'], 200);
    }
}
