<?php

namespace App\Http\Controllers;

use App\Models\CoverType;
use Illuminate\Http\Request;

class CoverTypeController extends Controller
{
    public function index()
    {
        $covertypes = CoverType::all();
        return response()->json([
            'success' => true,
            'data' => $covertypes
        ]);
    }
}
