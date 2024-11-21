<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Publisher;
use App\Http\Resources\PublisherResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publishers = Publisher::all();
        return new PublisherResource(true, 'Data retrieved successfully', $publishers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'      => 'required',
            'country'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $imagePath = $image->storeAs('publishers', $image->hashName(), 'public');

        $publisher = Publisher::create([
            'image' => basename($imagePath),
            'name'      => $request->name,
            'country'   => $request->country,
        ]);

        return new PublisherResource(true, 'The publisher has been created successfully!', $publisher);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $publisher = Publisher::findOrFail($id);
        return new PublisherResource(true, 'Detail Data Publisher!', $publisher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'country'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $publisher = Publisher::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/publishers', $image->hashName());

            Storage::delete('public/publishers/' . basename($publisher->image));

            $publisher->update([
                'image'     => $image->hashName(),
                'name'     => $request->name,
                'country'   => $request->country,
            ]);
        } else {
            $publisher->update([
                'name'     => $request->name,
                'country'   => $request->country,
            ]);
        }

        return new PublisherResource(true, 'Publisher data has been updated successfully!', $publisher);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $publisher = Publisher::findOrFail($id);
        if ($publisher->books()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this Publisher because it contains books!',
            ], 400);
        } else {
            if (Storage::exists('public/publishers/' . basename($publisher->image))) {
                Storage::delete('public/publishers/' . basename($publisher->image));
            }
        }

        $publisher->delete();

        return new PublisherResource(true, 'Publisher has been deleted successfully!', null);
    }
}
