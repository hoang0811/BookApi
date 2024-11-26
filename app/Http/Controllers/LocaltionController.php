<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Request;

class LocaltionController extends Controller
{
    public function getProvince()
    {
        $provinces = Province::all(); // Fetch all provinces
        return response()->json($provinces); // Return as JSON response
    }

    public function getDistrict(Request $request)
    {
        $provinceId = $request->input('province_id'); // Get province ID from request
        if (!$provinceId) {
            return response()->json(['error' => 'province_id is required'], 400);
        }

        $districts = District::where('province_id', $provinceId)->get(); // Get districts by province ID
        return response()->json($districts); // Return as JSON response
    }

    public function getWard(Request $request)
    {
        $districtId = $request->input('district_id'); // Get district ID from request
        if (!$districtId) {
            return response()->json(['error' => 'district_id is required'], 400);
        }

        $wards = Ward::where('district_id', $districtId)->get(); // Get wards by district ID
        return response()->json($wards); // Return as JSON response
    }
}
