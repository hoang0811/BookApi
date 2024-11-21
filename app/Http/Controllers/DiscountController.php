<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * Display a listing of the discounts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $discounts = Discount::all();
        return new DiscountResource(true, 'Data retrieved successfully', $discounts);

    }

    /**
     * Store a newly created discount in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code'           => 'required|string|unique:discounts|max:50',
            'discount_type'  => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric',
            'cart_value'     => 'required|numeric',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'usage_limit'    => 'required|integer',
            'is_active'      => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create a new discount
        $discount = Discount::create($request->all());

        return new DiscountResource(true, 'Discount created successfully.', $discount);
    }

    /**
     * Display the specified discount.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $discount = Discount::findOrFail($id);
        return new DiscountResource(true, 'Discount found.', $discount);
    }

    /**
     * Update the specified discount in storage.
     'aram \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'code'           => 'required|string|max:50|unique:discounts,code,' . $discount->id,
            'discount_type'  => 'required|string',
            'discount_value' => 'required|numeric',
            'cart_value'     => 'required|numeric',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'usage_limit'    => 'required|integer',
            'is_active'      => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update the discount
        $discount->update($request->all());

        return new DiscountResource(true, 'Discount updated successfully.', $discount);
    }

    /**
     * Remove the specified discount from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return response()->json(['success' => true, 'message' => 'Discount deleted successfully.']);
    }
}
