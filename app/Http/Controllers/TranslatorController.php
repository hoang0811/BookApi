<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Translator;
use App\Http\Resources\TranslatorResource;
use Illuminate\Support\Facades\Validator;

class TranslatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $translators = Translator::all();
        return new TranslatorResource(true, 'Data retrieved successfully', $translators);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translator = Translator::create([
            'name' => $request->name,
        ]);

        return new TranslatorResource(true, 'The translator has been created successfully!', $translator);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $translator = Translator::findOrFail($id);
        return new TranslatorResource(true, 'Translator details retrieved successfully!', $translator);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translator = Translator::findOrFail($id);
        $translator->update([
            'name' => $request->name,
        ]);

        return new TranslatorResource(true, 'Translator has been updated successfully!', $translator);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $translator = Translator::findOrFail($id);
        $translator->delete();

        return new TranslatorResource(true, 'Translator has been deleted successfully!', null);
    }
}
